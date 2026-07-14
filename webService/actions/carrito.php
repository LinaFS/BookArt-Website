<?php
require_once dirname(__DIR__, 2) . '/apiService/core/conexionBDD.php';
require_once dirname(__DIR__, 2) . '/apiService/middleware/auth.php';

requireUser();

$method   = $_SERVER['REQUEST_METHOD'];
$usuarioId = obtenerIdCuenta($conexion);

$action = trim($_GET['action'] ?? '');

match (true) {
    $method === 'GET'                                   => verCarrito($conexion, $usuarioId),
    $method === 'POST' && $action === 'checkout'        => checkout($conexion, $usuarioId),
    $method === 'POST' && isset($_POST['opcFinal'])     => crearPersonalizada($conexion, $usuarioId),
    $method === 'POST'                                  => agregarAlCarrito($conexion, $usuarioId),
    $method === 'DELETE'                                => eliminarDelCarrito($conexion, $usuarioId),
    default                                             => response(405, false, 'Método no permitido.')
};

// ── GET: ver carrito + count ─────────────────────────────────────────────────
function verCarrito($conexion, $usuarioId): void {
    $action = trim($_GET['action'] ?? '');

    if ($action === 'count') {
        $stmt = mysqli_prepare($conexion,
            "SELECT COUNT(*) AS total FROM pedidos WHERE IdCuenta = ? AND estatus = 'carrito'");
        mysqli_stmt_bind_param($stmt, 'i', $usuarioId);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        response(200, true, 'OK', ['count' => (int)$row['total']]);
        return;
    }

    $sql = "SELECT p.idPedido, p.idTipoPedido,
                CASE WHEN p.idTipoPedido = 1 THEN c.nombre  ELSE 'Libreta Personalizada' END AS nombre,
                CASE WHEN p.idTipoPedido = 1 THEN c.precio  ELSE COALESCE(per.precio, 0) END AS precio,
                CASE WHEN p.idTipoPedido = 1 THEN c.img     ELSE per.portada              END AS imagen
            FROM pedidos p
            LEFT JOIN catalogo     c   ON p.idCatalogo    = c.id_producto
            LEFT JOIN personalizada per ON p.idPersonalizada = per.id_personalizada
            WHERE p.IdCuenta = ? AND p.estatus = 'carrito'";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $usuarioId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    $items = [];
    while ($row = mysqli_fetch_assoc($result)) $items[] = $row;
    response(200, true, 'OK', ['items' => $items]);
}

// ── POST: crear libreta personalizada y agregar al carrito ──────────────────
function crearPersonalizada($conexion, $usuarioId): void {
    $opcion      = trim($_POST['opcFinal']     ?? '');
    $tamanio     = trim($_POST['tamaño']       ?? '');
    $tipoPapel   = trim($_POST['tipoPapel']    ?? '');
    $color       = trim($_POST['color']        ?? '');
    $descripcion = trim($_POST['descripcion']  ?? '');

    if (empty($opcion) || empty($tamanio) || empty($tipoPapel) || empty($color) || empty($descripcion)) {
        response(422, false, 'Todos los campos de la libreta son obligatorios.'); return;
    }

    $portadaUrl = '';
    if (isset($_FILES['portada']) && $_FILES['portada']['error'] === UPLOAD_ERR_OK) {
        $resultado = procesarPortada($_FILES['portada']);
        if (!$resultado['success']) { response(422, false, $resultado['message']); return; }
        $portadaUrl = $resultado['url'];
    }

    // Insertar en personalizada
    $stmt = mysqli_prepare($conexion,
        "INSERT INTO personalizada (color, descripcion, portada, tam, tipo_encuadernacion, tipo_papel)
         VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'ssssss',
        $color, $descripcion, $portadaUrl, $tamanio, $opcion, $tipoPapel);
    mysqli_stmt_execute($stmt);
    $idPersonalizada = mysqli_insert_id($conexion);
    $ok = $idPersonalizada > 0;
    mysqli_stmt_close($stmt);

    if (!$ok) { response(500, false, 'Error al guardar el diseño.'); return; }

    // Insertar en pedidos con estatus carrito
    $fecha = date('Y-m-d');
    $hora  = date('H:i:s');
    $stmt  = mysqli_prepare($conexion,
        "INSERT INTO pedidos (fecha, hora, estatus, idPersonalizada, idTipoPedido, IdCuenta)
         VALUES (?, ?, 'carrito', ?, 2, ?)");
    mysqli_stmt_bind_param($stmt, 'ssii', $fecha, $hora, $idPersonalizada, $usuarioId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$ok) {
        // Revertir personalizada si falla el pedido
        $stmt = mysqli_prepare($conexion, "DELETE FROM personalizada WHERE id_personalizada = ?");
        mysqli_stmt_bind_param($stmt, 'i', $idPersonalizada);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        response(500, false, 'Error al agregar al carrito.'); return;
    }

    response(201, true, '¡Diseño creado y agregado al carrito!', ['redirect' => '/carrito']);
}

function procesarPortada(array $file): array {
    if ($file['size'] > 5 * 1024 * 1024)
        return ['success' => false, 'message' => 'La portada no debe superar 5MB.'];

    $permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $permitidos))
        return ['success' => false, 'message' => 'Formato de imagen no válido (jpg, png, webp).'];

    $ext     = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nombre  = uniqid('portada_') . '.' . $ext;
    $carpeta = dirname(__DIR__, 2) . '/wwwroot/portadas/';

    if (!is_dir($carpeta)) mkdir($carpeta, 0755, true);

    if (!move_uploaded_file($file['tmp_name'], $carpeta . $nombre))
        return ['success' => false, 'message' => 'Error al guardar la portada.'];

    return ['success' => true, 'url' => '/wwwroot/portadas/' . $nombre];
}

// ── POST: checkout — confirmar pedido ────────────────────────────────────────
function checkout($conexion, $usuarioId): void {
    // Verificar que hay items en el carrito
    $stmt = mysqli_prepare($conexion,
        "SELECT COUNT(*) AS total FROM pedidos WHERE IdCuenta = ? AND estatus = 'carrito'");
    mysqli_stmt_bind_param($stmt, 'i', $usuarioId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ((int)$row['total'] === 0) {
        response(422, false, 'Tu carrito está vacío.'); return;
    }

    // Cambiar estatus de 'carrito' a 'pendiente'
    $stmt = mysqli_prepare($conexion,
        "UPDATE pedidos SET estatus = 'pendiente' WHERE IdCuenta = ? AND estatus = 'carrito'");
    mysqli_stmt_bind_param($stmt, 'i', $usuarioId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) {
        $_SESSION['flash'] = [
            'type'    => 'success',
            'message' => '¡Pedido realizado! Nos pondremos en contacto contigo pronto.'
        ];
        response(200, true, '¡Pedido realizado exitosamente!');
    } else {
        response(500, false, 'Error al procesar el pedido.');
    }
}

// ── POST: agregar al carrito ─────────────────────────────────────────────────
function agregarAlCarrito($conexion, $usuarioId): void {
    $tipo = trim($_POST['tipo'] ?? '');
    $id   = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);

    if (!$id || $id <= 0) { response(422, false, 'ID de producto inválido.'); return; }
    if (!in_array($tipo, ['catalogo', 'personalizada'])) {
        response(422, false, 'Tipo de producto no válido.'); return;
    }

    $idTipo  = $tipo === 'catalogo' ? 1 : 2;
    $columna = $tipo === 'catalogo' ? 'idCatalogo' : 'idPersonalizada';

    // Verificar duplicado en carrito
    $check = "SELECT 1 FROM pedidos
              WHERE IdCuenta = ? AND $columna = ? AND idTipoPedido = ? AND estatus = 'carrito'";
    $stmt  = mysqli_prepare($conexion, $check);
    mysqli_stmt_bind_param($stmt, 'iii', $usuarioId, $id, $idTipo);
    mysqli_stmt_execute($stmt);
    if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
        mysqli_stmt_close($stmt);
        response(409, false, 'Este producto ya está en tu carrito.'); return;
    }
    mysqli_stmt_close($stmt);

    $fecha = date('Y-m-d');
    $hora  = date('H:i:s');
    $sql   = "INSERT INTO pedidos (fecha, hora, estatus, $columna, idTipoPedido, IdCuenta)
              VALUES (?, ?, 'carrito', ?, ?, ?)";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, 'ssiii', $fecha, $hora, $id, $idTipo, $usuarioId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $ok ? response(201, true, 'Producto agregado al carrito.')
        : response(500, false, 'Error al agregar al carrito.');
}

// ── DELETE: quitar del carrito ───────────────────────────────────────────────
function eliminarDelCarrito($conexion, $usuarioId): void {
    $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : false;
    if (!$id || $id <= 0) { response(422, false, 'ID de pedido inválido.'); return; }

    // Verificar que el pedido pertenece al usuario
    $stmt = mysqli_prepare($conexion,
        "SELECT idPersonalizada, idTipoPedido FROM pedidos
         WHERE idPedido = ? AND IdCuenta = ? AND estatus = 'carrito'");
    mysqli_stmt_bind_param($stmt, 'ii', $id, $usuarioId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$row) { response(404, false, 'Pedido no encontrado.'); return; }

    $stmt = mysqli_prepare($conexion,
        "DELETE FROM pedidos WHERE idPedido = ? AND IdCuenta = ? AND estatus = 'carrito'");
    mysqli_stmt_bind_param($stmt, 'ii', $id, $usuarioId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Si era personalizada, eliminar registro e imagen
    if ($row['idTipoPedido'] == 2 && $row['idPersonalizada']) {
        $idPer = $row['idPersonalizada'];
        $stmt  = mysqli_prepare($conexion,
            "SELECT portada FROM personalizada WHERE id_personalizada = ?");
        mysqli_stmt_bind_param($stmt, 'i', $idPer);
        mysqli_stmt_execute($stmt);
        $per = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if ($per) {
            $stmt = mysqli_prepare($conexion,
                "DELETE FROM personalizada WHERE id_personalizada = ?");
            mysqli_stmt_bind_param($stmt, 'i', $idPer);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            if (!empty($per['portada'])) {
                $fs = dirname(__DIR__, 2) . $per['portada'];
                if (file_exists($fs)) unlink($fs);
            }
        }
    }

    response(200, true, 'Producto eliminado del carrito.');
}

// ── HELPERS ──────────────────────────────────────────────────────────────────
function obtenerIdCuenta($conexion): int {
    if (!empty($_SESSION['id_cuenta'])) {
        return (int)$_SESSION['id_cuenta'];
    }
    response(401, false, 'Sesión no válida. Inicia sesión nuevamente.');
}
