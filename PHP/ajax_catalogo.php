<?php
session_start();
require_once __DIR__ . '/conexionBDD.php';

if (!isset($conexion) || !$conexion) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

if (!isset($_SESSION["usuario"])) {
    echo json_encode(['success' => false, 'message' => 'Sesión expirada']);
    exit;
}

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch($action) {
    case 'listar':
        listarProductos($conexion);
        break;
    case 'obtener':
        obtenerProducto($conexion);
        break;
    case 'agregar':
        agregarProducto($conexion);
        break;
    case 'editar':
        editarProducto($conexion);
        break;
    case 'eliminar':
        eliminarProducto($conexion);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}

function listarProductos($conexion) {
    $query = "SELECT * FROM catalogo ORDER BY id_producto DESC";
    $result = mysqli_query($conexion, $query);

    if ($result) {
        $productos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $productos[] = $row;
        }
        echo json_encode(['success' => true, 'productos' => $productos]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al consultar productos']);
    }
}

function obtenerProducto($conexion) {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $query = "SELECT * FROM catalogo WHERE id_producto = ?";

    if ($stmt = mysqli_prepare($conexion, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error interno']);
        return;
    }

    if ($result && mysqli_num_rows($result) > 0) {
        $producto = mysqli_fetch_assoc($result);
        echo json_encode(['success' => true, 'producto' => $producto]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
    }
}

function agregarProducto($conexion) {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = trim($_POST['precio'] ?? '');
    $precioFloat = floatval($precio);

    if (empty($nombre) || empty($descripcion) || $precio === '') {
        echo json_encode(['success' => false, 'message' => 'Completa todos los campos']);
        return;
    }

    $check = "SELECT id_producto FROM catalogo WHERE nombre = ?";
    if ($stmt = mysqli_prepare($conexion, $check)) {
        mysqli_stmt_bind_param($stmt, "s", $nombre);
        mysqli_stmt_execute($stmt);
        $result_check = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error interno']);
        return;
    }

    if (mysqli_num_rows($result_check) > 0) {
        echo json_encode(['success' => false, 'message' => 'Ya existe un producto con ese nombre']);
        return;
    }

    $rutaImagen = '../Catalogo/imgNoEncontrada.png';
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $resultado = procesarImagen($_FILES['imagen']);
        if ($resultado['success']) {
            $rutaImagen = $resultado['ruta'];
        } else {
            echo json_encode(['success' => false, 'message' => $resultado['message']]);
            return;
        }
    }

    $query = "INSERT INTO catalogo (nombre, descripcion, precio, img) VALUES (?, ?, ?, ?)";
    if ($stmt = mysqli_prepare($conexion, $query)) {
        mysqli_stmt_bind_param($stmt, "ssds", $nombre, $descripcion, $precioFloat, $rutaImagen);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error interno']);
        return;
    }

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Producto agregado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al agregar producto']);
    }
}

function editarProducto($conexion) {
    $id = isset($_POST['id_producto']) ? intval($_POST['id_producto']) : 0;
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = trim($_POST['precio'] ?? '');
    $precioFloat = floatval($precio);

    if ($id <= 0 || empty($nombre) || empty($descripcion) || $precio === '') {
        echo json_encode(['success' => false, 'message' => 'Completa todos los campos']);
        return;
    }

    $check = "SELECT id_producto FROM catalogo WHERE nombre = ? AND id_producto != ?";
    if ($stmt = mysqli_prepare($conexion, $check)) {
        mysqli_stmt_bind_param($stmt, "si", $nombre, $id);
        mysqli_stmt_execute($stmt);
        $result_check = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error interno']);
        return;
    }

    if (mysqli_num_rows($result_check) > 0) {
        echo json_encode(['success' => false, 'message' => 'Ya existe otro producto con ese nombre']);
        return;
    }

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $query_old = "SELECT img FROM catalogo WHERE id_producto = ?";
        if ($stmt = mysqli_prepare($conexion, $query_old)) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $result_old = mysqli_stmt_get_result($stmt);
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error interno']);
            return;
        }

        $old_img = mysqli_fetch_assoc($result_old)['img'] ?? '';
        $resultado = procesarImagen($_FILES['imagen']);
        if ($resultado['success']) {
            if ($old_img != '../Catalogo/imgNoEncontrada.png' && file_exists($old_img)) {
                unlink($old_img);
            }

            $query = "UPDATE catalogo SET nombre = ?, descripcion = ?, precio = ?, img = ? WHERE id_producto = ?";
            if ($stmt = mysqli_prepare($conexion, $query)) {
                mysqli_stmt_bind_param($stmt, "ssdsi", $nombre, $descripcion, $precioFloat, $resultado['ruta'], $id);
                $result = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error interno']);
                return;
            }
        } else {
            echo json_encode(['success' => false, 'message' => $resultado['message']]);
            return;
        }
    } else {
        $query = "UPDATE catalogo SET nombre = ?, descripcion = ?, precio = ? WHERE id_producto = ?";
        if ($stmt = mysqli_prepare($conexion, $query)) {
            mysqli_stmt_bind_param($stmt, "ssdi", $nombre, $descripcion, $precioFloat, $id);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error interno']);
            return;
        }
    }

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Producto actualizado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar producto']);
    }
}

function eliminarProducto($conexion) {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    $query_img = "SELECT img FROM catalogo WHERE id_producto = ?";
    if ($stmt = mysqli_prepare($conexion, $query_img)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result_img = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error interno']);
        return;
    }

    if (mysqli_num_rows($result_img) > 0) {
        $img = mysqli_fetch_assoc($result_img)['img'];
        $query_delete = "DELETE FROM catalogo WHERE id_producto = ?";
        if ($stmt = mysqli_prepare($conexion, $query_delete)) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            $result_delete = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error interno']);
            return;
        }

        if ($result_delete) {
            if ($img != '../Catalogo/imgNoEncontrada.png' && file_exists($img)) {
                unlink($img);
            }
            echo json_encode(['success' => true, 'message' => 'Producto eliminado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar producto']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
    }
}

function procesarImagen($file) {
    $carpeta = "../Catalogo/";
    if ($file['size'] > 3 * 1024 * 1024) {
        return ['success' => false, 'message' => 'La imagen no debe superar 3MB'];
    }

    $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $tiposPermitidos)) {
        return ['success' => false, 'message' => 'Formato de imagen no válido'];
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nombreArchivo = uniqid('producto_') . '.' . $extension;
    $rutaDestino = $carpeta . $nombreArchivo;

    if (move_uploaded_file($file['tmp_name'], $rutaDestino)) {
        return ['success' => true, 'ruta' => $rutaDestino];
    } else {
        return ['success' => false, 'message' => 'Error al guardar la imagen'];
    }
}
?>