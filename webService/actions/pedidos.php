<?php
require_once dirname(__DIR__, 2) . '/apiService/core/conexionBDD.php';
require_once dirname(__DIR__, 2) . '/apiService/middleware/auth.php';

requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$action = trim($_GET['action'] ?? '');

$esAdmin = in_array($action, ['admin-listar','admin-detalle','admin-estatus','admin-precio','estadisticas']);
if ($esAdmin) requireAdmin(); else requireUser();

match (true) {
    // Admin
    $action === 'admin-listar'  => adminListar($conexion),
    $action === 'admin-detalle' => adminDetalle($conexion),
    $action === 'admin-estatus' => adminEstatus($conexion, $method),
    $action === 'admin-precio'  => adminPrecio($conexion, $method),
    $action === 'estadisticas'  => estadisticas($conexion),
    // Usuario
    $action === 'editar'        => editarPedido($conexion, $method),
    $action === 'cancel'        => cancelarPedido($conexion),
    $method === 'GET'           => misPedidos($conexion),
    $method === 'POST'          => checkout($conexion),
    $method === 'DELETE'        => cancelarPedido($conexion),
    default                     => response(405, false, 'Acción no válida.')
};

// ── USUARIO: mis pedidos ─────────────────────────────────────────────────────
function misPedidos($conexion): void {
    $usuarioId = obtenerIdCuenta($conexion);

    $sql = "SELECT p.idPedido, p.fecha, p.hora, p.estatus, p.mensaje,
                tp.descripcion AS tipo,
                CASE WHEN tp.id_tipoPedido = 1 THEN c.nombre  ELSE 'Libreta Personalizada' END AS producto,
                CASE WHEN tp.id_tipoPedido = 1 THEN c.precio  ELSE COALESCE(per.precio, 0) END AS precio,
                CASE WHEN tp.id_tipoPedido = 1 THEN c.img     ELSE per.portada              END AS imagen
            FROM pedidos p
            INNER JOIN tipoPedido tp ON p.idTipoPedido = tp.id_tipoPedido
            LEFT JOIN catalogo      c   ON p.idCatalogo    = c.id_producto
            LEFT JOIN personalizada per ON p.idPersonalizada = per.id_personalizada
            WHERE p.IdCuenta = ? AND p.estatus != 'carrito'
            ORDER BY p.fecha DESC, p.hora DESC";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $usuarioId);
    mysqli_stmt_execute($stmt);
    $result  = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    $pedidos = [];
    while ($row = mysqli_fetch_assoc($result)) $pedidos[] = $row;
    response(200, true, 'OK', ['pedidos' => $pedidos]);
}

// ── USUARIO: checkout ────────────────────────────────────────────────────────
function checkout($conexion): void {
    $usuarioId = obtenerIdCuenta($conexion);

    $stmt = mysqli_prepare($conexion,
        "SELECT COUNT(*) AS total FROM pedidos WHERE IdCuenta = ? AND estatus = 'carrito'");
    mysqli_stmt_bind_param($stmt, 'i', $usuarioId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($row['total'] == 0) { response(422, false, 'No hay productos en el carrito.'); return; }

    $stmt = mysqli_prepare($conexion,
        "UPDATE pedidos SET estatus = 'pendiente', fecha = CURDATE(), hora = CURTIME()
         WHERE IdCuenta = ? AND estatus = 'carrito'");
    mysqli_stmt_bind_param($stmt, 'i', $usuarioId);
    $ok   = mysqli_stmt_execute($stmt);
    $filas = mysqli_affected_rows($conexion);
    mysqli_stmt_close($stmt);

    $ok && $filas > 0
        ? response(200, true, 'Pedido realizado con éxito.', ['pedidos_procesados' => $filas])
        : response(500, false, 'No se pudo procesar el pedido.');
}

// ── USUARIO: cancelar ────────────────────────────────────────────────────────
function cancelar($conexion): void {
    $usuarioId = obtenerIdCuenta($conexion);
    parse_str(file_get_contents('php://input'), $_DELETE);
    $id = isset($_DELETE['id']) ? filter_var($_DELETE['id'], FILTER_VALIDATE_INT) : false;
    if (!$id || $id <= 0) { response(422, false, 'ID de pedido inválido.'); return; }

    $stmt = mysqli_prepare($conexion,
        "SELECT estatus, idPersonalizada, idTipoPedido FROM pedidos
         WHERE idPedido = ? AND IdCuenta = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $id, $usuarioId);
    mysqli_stmt_execute($stmt);
    $pedido = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$pedido) { response(404, false, 'Pedido no encontrado.'); return; }
    if (!in_array(strtolower($pedido['estatus']), ['pendiente', 'visto', 'carrito'])) {
        response(409, false, 'Este pedido ya no puede cancelarse.'); return;
    }

    $stmt = mysqli_prepare($conexion,
        "DELETE FROM pedidos WHERE idPedido = ? AND IdCuenta = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $id, $usuarioId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Limpiar personalizada si aplica
    if ($pedido['idTipoPedido'] == 2 && $pedido['idPersonalizada']) {
        $idPer = $pedido['idPersonalizada'];
        $stmt  = mysqli_prepare($conexion,
            "SELECT portada FROM personalizada WHERE id_personalizada = ?");
        mysqli_stmt_bind_param($stmt, 'i', $idPer);
        mysqli_stmt_execute($stmt);
        $per = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($conexion,
            "DELETE FROM personalizada WHERE id_personalizada = ?");
        mysqli_stmt_bind_param($stmt, 'i', $idPer);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($per && !empty($per['portada'])) {
            $fs = dirname(__DIR__, 2) . $per['portada'];
            if (file_exists($fs)) unlink($fs);
        }
    }

    response(200, true, 'Pedido cancelado exitosamente.');
}

// ── USUARIO: editar pedido personalizada ─────────────────────────────────────
function editarPedido($conexion, $method): void {
    if ($method !== 'POST') { response(405, false, 'Método no permitido.'); return; }

    $usuarioId      = obtenerIdCuenta($conexion);
    $idPedido       = isset($_POST['idPedido'])       ? intval($_POST['idPedido'])       : 0;
    $idPersonalizada= isset($_POST['idPersonalizada']) ? intval($_POST['idPersonalizada']): 0;
    $opcion         = trim($_POST['opcFinal']    ?? '');
    $tamanio        = trim($_POST['tamaño']      ?? '');
    $tipoPapel      = trim($_POST['tipoPapel']   ?? '');
    $color          = trim($_POST['color']        ?? '');
    $descripcion    = trim($_POST['descripcion'] ?? '');

    if ($idPedido <= 0 || $idPersonalizada <= 0) {
        response(422, false, 'IDs de pedido no válidos.'); return;
    }
    if (empty($opcion) || empty($tamanio) || empty($tipoPapel) || empty($color) || empty($descripcion)) {
        response(422, false, 'Todos los campos son obligatorios.'); return;
    }

    // Verificar que el pedido pertenece al usuario y puede editarse
    $stmt = mysqli_prepare($conexion,
        "SELECT estatus FROM pedidos WHERE idPedido = ? AND IdCuenta = ? AND idTipoPedido = 2");
    mysqli_stmt_bind_param($stmt, 'ii', $idPedido, $usuarioId);
    mysqli_stmt_execute($stmt);
    $pedido = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$pedido) { response(404, false, 'Pedido no encontrado.'); return; }
    if (!in_array(strtolower($pedido['estatus']), ['pendiente', 'visto'])) {
        response(409, false, 'Este pedido ya no puede editarse.'); return;
    }

    // Procesar nueva portada si se subió
    $portadaUrl = null;
    if (isset($_FILES['portada']) && $_FILES['portada']['error'] === UPLOAD_ERR_OK) {
        $resultado = procesarPortada($_FILES['portada']);
        if (!$resultado['success']) { response(422, false, $resultado['message']); return; }
        $portadaUrl = $resultado['url'];
    }

    // Actualizar personalizada
    if ($portadaUrl !== null) {
        // Borrar portada anterior
        $stmt = mysqli_prepare($conexion,
            "SELECT portada FROM personalizada WHERE id_personalizada = ?");
        mysqli_stmt_bind_param($stmt, 'i', $idPersonalizada);
        mysqli_stmt_execute($stmt);
        $old = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        if ($old && !empty($old['portada'])) {
            $fs = dirname(__DIR__, 2) . $old['portada'];
            if (file_exists($fs)) unlink($fs);
        }

        $stmt = mysqli_prepare($conexion,
            "UPDATE personalizada SET color=?, descripcion=?, portada=?, tam=?, tipo_encuadernacion=?, tipo_papel=?
             WHERE id_personalizada=?");
        mysqli_stmt_bind_param($stmt, 'ssssssi',
            $color, $descripcion, $portadaUrl, $tamanio, $opcion, $tipoPapel, $idPersonalizada);
    } else {
        $stmt = mysqli_prepare($conexion,
            "UPDATE personalizada SET color=?, descripcion=?, tam=?, tipo_encuadernacion=?, tipo_papel=?
             WHERE id_personalizada=?");
        mysqli_stmt_bind_param($stmt, 'sssssi',
            $color, $descripcion, $tamanio, $opcion, $tipoPapel, $idPersonalizada);
    }

    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $ok ? response(200, true, 'Pedido actualizado correctamente.', ['redirect' => '/mis-pedidos'])
        : response(500, false, 'Error al actualizar el pedido.');
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

// ── ADMIN: listar todos los pedidos ─────────────────────────────────────────
function adminListar($conexion): void {
    requireAdmin();

    $sql = "SELECT p.idPedido AS id, p.fecha, p.hora, p.estatus,
                tp.id_tipoPedido,
                tp.descripcion AS tipo,
                CASE WHEN tp.id_tipoPedido = 1 THEN c.nombre  ELSE 'Libreta Personalizada' END AS producto_nombre,
                CASE WHEN tp.id_tipoPedido = 1 THEN c.precio  ELSE COALESCE(per.precio, 0) END AS producto_precio,
                u.nombre AS cliente_nombre, u.paterno, u.materno,
                cu.correo AS cliente_correo, cu.usuario AS cliente_usuario
            FROM pedidos p
            INNER JOIN tipoPedido tp ON p.idTipoPedido = tp.id_tipoPedido
            LEFT JOIN catalogo      c   ON p.idCatalogo    = c.id_producto
            LEFT JOIN personalizada per ON p.idPersonalizada = per.id_personalizada
            INNER JOIN cuenta cu  ON p.idCuenta   = cu.id_cuenta
            INNER JOIN usuario u  ON cu.usuario_id = u.id_usuario
            WHERE p.estatus != 'carrito'
            ORDER BY p.fecha DESC, p.hora DESC";

    $result  = mysqli_query($conexion, $sql);
    $pedidos = [];
    while ($row = mysqli_fetch_assoc($result)) $pedidos[] = $row;
    response(200, true, 'OK', ['pedidos' => $pedidos]);
}

// ── ADMIN: detalle de un pedido ───────────────────────────────────────────────
function adminDetalle($conexion): void {
    requireAdmin();
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id <= 0) { response(422, false, 'ID no válido.'); return; }

    $sql = "SELECT p.*, tp.descripcion AS tipo_descripcion, tp.id_tipoPedido,
                CASE WHEN tp.id_tipoPedido = 1 THEN c.nombre       ELSE 'Libreta Personalizada' END AS producto_nombre,
                CASE WHEN tp.id_tipoPedido = 1 THEN c.precio       ELSE COALESCE(per.precio, 0) END AS producto_precio,
                CASE WHEN tp.id_tipoPedido = 1 THEN c.descripcion  ELSE per.descripcion         END AS producto_descripcion,
                CASE WHEN tp.id_tipoPedido = 1 THEN c.img          ELSE per.portada             END AS producto_img,
                per.color, per.tam, per.tipo_encuadernacion, per.tipo_papel,
                u.nombre AS cliente_nombre, u.paterno, u.materno, u.tel AS cliente_tel,
                cu.correo AS cliente_correo, cu.usuario AS cliente_usuario
            FROM pedidos p
            INNER JOIN tipoPedido tp ON p.idTipoPedido = tp.id_tipoPedido
            LEFT JOIN catalogo      c   ON p.idCatalogo    = c.id_producto
            LEFT JOIN personalizada per ON p.idPersonalizada = per.id_personalizada
            INNER JOIN cuenta cu  ON p.idCuenta   = cu.id_cuenta
            INNER JOIN usuario u  ON cu.usuario_id = u.id_usuario
            WHERE p.idPedido = ?";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$row) { response(404, false, 'Pedido no encontrado.'); return; }
    response(200, true, 'OK', ['pedido' => $row]);
}

// ── ADMIN: cambiar estatus ────────────────────────────────────────────────────
function adminEstatus($conexion, $method): void {
    requireAdmin();
    if ($method !== 'PUT') { response(405, false, 'Método no permitido.'); return; }

    parse_str(file_get_contents('php://input'), $input);
    $id      = isset($input['pedidoId']) ? intval($input['pedidoId']) : 0;
    $estatus = trim($input['estatus'] ?? '');
    $mensaje = trim($input['mensaje'] ?? '');

    $validos = ['pendiente','visto','aprobado','declinado','proceso','terminado','entregado'];
    if (!in_array($estatus, $validos)) {
        response(422, false, 'Estatus no válido.'); return;
    }

    $stmt = mysqli_prepare($conexion,
        "UPDATE pedidos SET estatus = ?, mensaje = ? WHERE idPedido = ?");
    mysqli_stmt_bind_param($stmt, 'ssi', $estatus, $mensaje, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $ok ? response(200, true, "Estatus actualizado a: $estatus.")
        : response(500, false, 'Error al actualizar estatus.');
}

// ── ADMIN: asignar precio personalizada ──────────────────────────────────────
function adminPrecio($conexion, $method): void {
    requireAdmin();
    if ($method !== 'PUT') { response(405, false, 'Método no permitido.'); return; }

    parse_str(file_get_contents('php://input'), $input);
    $idPedido = isset($input['pedidoId']) ? intval($input['pedidoId']) : 0;
    $precio   = $input['precio'] ?? '';

    if (!is_numeric($precio) || $precio < 0) {
        response(422, false, 'Precio no válido.'); return;
    }

    $stmt = mysqli_prepare($conexion,
        "SELECT idPersonalizada, idTipoPedido FROM pedidos WHERE idPedido = ?");
    mysqli_stmt_bind_param($stmt, 'i', $idPedido);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$row)              { response(404, false, 'Pedido no encontrado.'); return; }
    if ($row['idTipoPedido'] != 2) {
        response(422, false, 'Solo se puede asignar precio a libretas personalizadas.'); return;
    }

    $idPer       = $row['idPersonalizada'];
    $precioFloat = floatval($precio);
    $stmt        = mysqli_prepare($conexion,
        "UPDATE personalizada SET precio = ? WHERE id_personalizada = ?");
    mysqli_stmt_bind_param($stmt, 'di', $precioFloat, $idPer);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $ok ? response(200, true, 'Precio asignado: $' . number_format($precioFloat, 2))
        : response(500, false, 'Error al asignar precio.');
}

// ── ADMIN: estadísticas dashboard ────────────────────────────────────────────
function estadisticas($conexion): void {
    requireAdmin();

    $activos = mysqli_fetch_assoc(mysqli_query($conexion,
        "SELECT COUNT(*) AS total FROM pedidos
         WHERE estatus NOT IN ('carrito','entregado','declinado')"))['total'] ?? 0;

    $clientes = mysqli_fetch_assoc(mysqli_query($conexion,
        "SELECT COUNT(DISTINCT id_cuenta) AS total FROM cuenta WHERE permiso_id = 2"))['total'] ?? 0;

    $ventas = mysqli_fetch_assoc(mysqli_query($conexion,
        "SELECT (
            SELECT COALESCE(SUM(c.precio),0) FROM pedidos p
            INNER JOIN catalogo c ON p.idCatalogo = c.id_producto
            WHERE p.idTipoPedido = 1 AND p.estatus = 'entregado'
            AND MONTH(p.fecha) = MONTH(CURDATE()) AND YEAR(p.fecha) = YEAR(CURDATE())
         ) + (
            SELECT COALESCE(SUM(per.precio),0) FROM pedidos p
            INNER JOIN personalizada per ON p.idPersonalizada = per.id_personalizada
            WHERE p.idTipoPedido = 2 AND p.estatus = 'entregado' AND per.precio > 0
            AND MONTH(p.fecha) = MONTH(CURDATE()) AND YEAR(p.fecha) = YEAR(CURDATE())
         ) AS total"))['total'] ?? 0;

    response(200, true, 'OK', [
        'pedidos_activos' => (int)$activos,
        'total_clientes'  => (int)$clientes,
        'ventas_mes'      => (float)$ventas,
    ]);
}

// ── CANCELAR PEDIDO (usuario) ─────────────────────────────────────────────────
function cancelarPedido($conexion): void {
    $usuarioId = obtenerIdCuenta($conexion);
    $id        = filter_var($_POST['id'] ?? $_GET['id'] ?? 0, FILTER_VALIDATE_INT);

    if (!$id || $id <= 0) {
        response(422, false, 'ID de pedido inválido.'); return;
    }

    // Verificar que el pedido pertenece al usuario y está en estado cancelable
    $stmt = mysqli_prepare($conexion,
        "SELECT idPedido, estatus, idPersonalizada FROM pedidos
         WHERE idPedido = ? AND idCuenta = ? AND estatus IN ('pendiente','visto')");
    mysqli_stmt_bind_param($stmt, 'ii', $id, $usuarioId);
    mysqli_stmt_execute($stmt);
    $pedido = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$pedido) {
        response(404, false, 'Pedido no encontrado o no se puede cancelar.'); return;
    }

    $stmt = mysqli_prepare($conexion,
        "UPDATE pedidos SET estatus = 'cancelado' WHERE idPedido = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $ok ? response(200, true, 'Pedido cancelado correctamente.')
        : response(500, false, 'Error al cancelar el pedido.');
}

// ── HELPERS ──────────────────────────────────────────────────────────────────
function obtenerIdCuenta($conexion): int {
    if (!empty($_SESSION['id_cuenta'])) {
        return (int)$_SESSION['id_cuenta'];
    }
    response(401, false, 'Sesión no válida. Inicia sesión nuevamente.');
}

