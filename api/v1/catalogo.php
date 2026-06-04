<?php
require_once dirname(__DIR__, 2) . '/core/conexionBDD.php';
require_once dirname(__DIR__) . '/middleware/auth.php';

$method = $_SERVER['REQUEST_METHOD'];

match ($method) {
    'GET'    => listar($conexion),
    'POST'   => agregar($conexion),
    'PUT'    => editar($conexion),
    'DELETE' => eliminar($conexion),
    default  => response(405, false, 'Método no permitido.')
};

// ── GET: listar o un producto ────────────────────────────────────────────────
function listar($conexion): void {
    $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;

    if ($id) {
        $stmt = mysqli_prepare($conexion, "SELECT * FROM catalogo WHERE id_producto = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);

        $producto = mysqli_fetch_assoc($result);
        if (!$producto) { response(404, false, 'Producto no encontrado.'); return; }
        response(200, true, 'OK', ['producto' => $producto]);
    } else {
        $result    = mysqli_query($conexion, "SELECT * FROM catalogo ORDER BY id_producto DESC");
        $productos = [];
        while ($row = mysqli_fetch_assoc($result)) $productos[] = $row;
        response(200, true, 'OK', ['productos' => $productos]);
    }
}

// ── POST: agregar producto (admin) ───────────────────────────────────────────
function agregar($conexion): void {
    requireAdmin();

    $nombre      = trim($_POST['nombre']      ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio      = trim($_POST['precio']      ?? '');

    if (empty($nombre) || empty($descripcion) || $precio === '') {
        response(422, false, 'Nombre, descripción y precio son obligatorios.'); return;
    }
    if (!is_numeric($precio) || $precio < 0) {
        response(422, false, 'El precio no es válido.'); return;
    }

    $stmt = mysqli_prepare($conexion, "SELECT 1 FROM catalogo WHERE nombre = ?");
    mysqli_stmt_bind_param($stmt, 's', $nombre);
    mysqli_stmt_execute($stmt);
    if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
        mysqli_stmt_close($stmt);
        response(409, false, 'Ya existe un producto con ese nombre.'); return;
    }
    mysqli_stmt_close($stmt);

    $imagen = '/wwwroot/catalogo/imgNoEncontrada.png';
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $resultado = procesarImagen($_FILES['imagen']);
        if (!$resultado['success']) { response(422, false, $resultado['message']); return; }
        $imagen = $resultado['ruta'];
    }

    $precioFloat = floatval($precio);
    $stmt = mysqli_prepare($conexion,
        "INSERT INTO catalogo (nombre, descripcion, precio, img) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'ssds', $nombre, $descripcion, $precioFloat, $imagen);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $ok ? response(201, true, 'Producto agregado exitosamente.')
        : response(500, false, 'Error al agregar el producto.');
}

// ── PUT: editar producto (admin) ─────────────────────────────────────────────
function editar($conexion): void {
    requireAdmin();

    // PHP no parsea PUT con FormData, hay que leerlo manualmente
    $_PUT = [];
    parse_str(file_get_contents('php://input'), $_PUT);

    $id          = isset($_PUT['id_producto']) ? intval($_PUT['id_producto']) : 0;
    $nombre      = trim($_PUT['nombre']      ?? '');
    $descripcion = trim($_PUT['descripcion'] ?? '');
    $precio      = trim($_PUT['precio']      ?? '');

    if ($id <= 0 || empty($nombre) || empty($descripcion) || $precio === '') {
        response(422, false, 'ID, nombre, descripción y precio son obligatorios.'); return;
    }
    if (!is_numeric($precio) || $precio < 0) {
        response(422, false, 'El precio no es válido.'); return;
    }

    $stmt = mysqli_prepare($conexion,
        "SELECT 1 FROM catalogo WHERE nombre = ? AND id_producto != ?");
    mysqli_stmt_bind_param($stmt, 'si', $nombre, $id);
    mysqli_stmt_execute($stmt);
    if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
        mysqli_stmt_close($stmt);
        response(409, false, 'Ya existe otro producto con ese nombre.'); return;
    }
    mysqli_stmt_close($stmt);

    $precioFloat = floatval($precio);
    $stmt = mysqli_prepare($conexion,
        "UPDATE catalogo SET nombre = ?, descripcion = ?, precio = ? WHERE id_producto = ?");
    mysqli_stmt_bind_param($stmt, 'ssdi', $nombre, $descripcion, $precioFloat, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $ok ? response(200, true, 'Producto actualizado exitosamente.')
        : response(500, false, 'Error al actualizar el producto.');
}

// ── DELETE: eliminar producto (admin) ────────────────────────────────────────
function eliminar($conexion): void {
    requireAdmin();

    parse_str(file_get_contents('php://input'), $_DELETE);
    $id = isset($_DELETE['id']) ? intval($_DELETE['id']) : 0;
    if ($id <= 0) { response(422, false, 'ID no válido.'); return; }

    $stmt = mysqli_prepare($conexion, "SELECT img FROM catalogo WHERE id_producto = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    $row = mysqli_fetch_assoc($result);
    if (!$row) { response(404, false, 'Producto no encontrado.'); return; }

    $stmt = mysqli_prepare($conexion, "DELETE FROM catalogo WHERE id_producto = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) {
        $img = $row['img'];
        if ($img !== '/wwwroot/catalogo/imgNoEncontrada.png') {
            $fs = dirname(__DIR__, 2) . $img;   // /wwwroot/catalogo/x.jpg → filesystem path
            if (file_exists($fs)) unlink($fs);
        }
        response(200, true, 'Producto eliminado exitosamente.');
    } else {
        response(500, false, 'Error al eliminar el producto.');
    }
}

// ── HELPERS ──────────────────────────────────────────────────────────────────
function procesarImagen(array $file): array {
    if ($file['size'] > 3 * 1024 * 1024)
        return ['success' => false, 'message' => 'La imagen no debe superar 3MB.'];

    $permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $permitidos))
        return ['success' => false, 'message' => 'Formato de imagen no válido.'];

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nombre   = uniqid('producto_') . '.' . $ext;
    $carpeta  = dirname(__DIR__, 2) . '/wwwroot/catalogo/';

    if (!is_dir($carpeta)) mkdir($carpeta, 0755, true);

    if (!move_uploaded_file($file['tmp_name'], $carpeta . $nombre))
        return ['success' => false, 'message' => 'Error al guardar la imagen.'];

    return ['success' => true, 'ruta' => '/wwwroot/catalogo/' . $nombre];
}

function requireAdmin(): void {
    if (!isset($_SESSION['permiso']) || $_SESSION['permiso'] != 1) {
        response(403, false, 'No tienes permisos de administrador.');
    }
}
