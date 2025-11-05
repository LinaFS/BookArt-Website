<?php
// Guardar como: PHP/ajax_catalogo.php
session_start();
include("conexionBDD.php");

// Verificar sesión
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

// Listar todos los productos
function listarProductos($conexion) {
    $query = "SELECT * FROM catalogo ORDER BY id_producto DESC";
    $result = mysqli_query($conexion, $query);
    
    if($result) {
        $productos = [];
        while($row = mysqli_fetch_assoc($result)) {
            $productos[] = $row;
        }
        echo json_encode(['success' => true, 'productos' => $productos]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al consultar productos']);
    }
}

// Obtener un producto específico
function obtenerProducto($conexion) {
    $id = mysqli_real_escape_string($conexion, $_GET['id']);
    $query = "SELECT * FROM catalogo WHERE id_producto = '$id'";
    $result = mysqli_query($conexion, $query);
    
    if($result && mysqli_num_rows($result) > 0) {
        $producto = mysqli_fetch_assoc($result);
        echo json_encode(['success' => true, 'producto' => $producto]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
    }
}

// Agregar producto
function agregarProducto($conexion) {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $precio = mysqli_real_escape_string($conexion, $_POST['precio']);
    
    // Validar campos
    if(empty($nombre) || empty($descripcion) || empty($precio)) {
        echo json_encode(['success' => false, 'message' => 'Completa todos los campos']);
        return;
    }
    
    // Verificar si ya existe
    $check = "SELECT id_producto FROM catalogo WHERE nombre = '$nombre'";
    $result_check = mysqli_query($conexion, $check);
    
    if(mysqli_num_rows($result_check) > 0) {
        echo json_encode(['success' => false, 'message' => 'Ya existe un producto con ese nombre']);
        return;
    }
    
    // Procesar imagen
    $rutaImagen = '../Catalogo/imgNoEncontrada.png';
    
    if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $resultado = procesarImagen($_FILES['imagen']);
        if($resultado['success']) {
            $rutaImagen = $resultado['ruta'];
        } else {
            echo json_encode(['success' => false, 'message' => $resultado['message']]);
            return;
        }
    }
    
    // Insertar producto
    $query = "INSERT INTO catalogo (nombre, descripcion, precio, img) VALUES ('$nombre', '$descripcion', '$precio', '$rutaImagen')";
    $result = mysqli_query($conexion, $query);
    
    if($result) {
        echo json_encode(['success' => true, 'message' => 'Producto agregado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al agregar producto']);
    }
}

// Editar producto
function editarProducto($conexion) {
    $id = mysqli_real_escape_string($conexion, $_POST['id_producto']);
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $precio = mysqli_real_escape_string($conexion, $_POST['precio']);
    
    // Validar campos
    if(empty($id) || empty($nombre) || empty($descripcion) || empty($precio)) {
        echo json_encode(['success' => false, 'message' => 'Completa todos los campos']);
        return;
    }
    
    // Verificar si existe otro producto con el mismo nombre
    $check = "SELECT id_producto FROM catalogo WHERE nombre = '$nombre' AND id_producto != '$id'";
    $result_check = mysqli_query($conexion, $check);
    
    if(mysqli_num_rows($result_check) > 0) {
        echo json_encode(['success' => false, 'message' => 'Ya existe otro producto con ese nombre']);
        return;
    }
    
    // Si hay nueva imagen
    if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        // Obtener imagen anterior
        $query_old = "SELECT img FROM catalogo WHERE id_producto = '$id'";
        $result_old = mysqli_query($conexion, $query_old);
        $old_img = mysqli_fetch_assoc($result_old)['img'];
        
        // Procesar nueva imagen
        $resultado = procesarImagen($_FILES['imagen']);
        if($resultado['success']) {
            // Eliminar imagen anterior si no es la predeterminada
            if($old_img != '../Catalogo/imgNoEncontrada.png' && file_exists($old_img)) {
                unlink($old_img);
            }
            
            $query = "UPDATE catalogo SET nombre='$nombre', descripcion='$descripcion', 
                     precio='$precio', img='{$resultado['ruta']}' WHERE id_producto='$id'";
        } else {
            echo json_encode(['success' => false, 'message' => $resultado['message']]);
            return;
        }
    } else {
        // Actualizar sin cambiar imagen
        $query = "UPDATE catalogo SET nombre='$nombre', descripcion='$descripcion', 
                 precio='$precio' WHERE id_producto='$id'";
    }
    
    $result = mysqli_query($conexion, $query);
    
    if($result) {
        echo json_encode(['success' => true, 'message' => 'Producto actualizado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar producto']);
    }
}

// Eliminar producto
function eliminarProducto($conexion) {
    $id = mysqli_real_escape_string($conexion, $_POST['id']);
    
    // Obtener ruta de imagen
    $query_img = "SELECT img FROM catalogo WHERE id_producto = '$id'";
    $result_img = mysqli_query($conexion, $query_img);
    
    if(mysqli_num_rows($result_img) > 0) {
        $img = mysqli_fetch_assoc($result_img)['img'];
        
        // Eliminar producto
        $query_delete = "DELETE FROM catalogo WHERE id_producto = '$id'";
        $result_delete = mysqli_query($conexion, $query_delete);
        
        if($result_delete) {
            // Eliminar imagen si no es la predeterminada
            if($img != '../Catalogo/imgNoEncontrada.png' && file_exists($img)) {
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

// Función auxiliar para procesar imágenes
function procesarImagen($file) {
    $carpeta = "../Catalogo/";
    
    // Validar tamaño (3MB máximo)
    if($file['size'] > 3 * 1024 * 1024) {
        return ['success' => false, 'message' => 'La imagen no debe superar 3MB'];
    }
    
    // Validar tipo
    $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if(!in_array($file['type'], $tiposPermitidos)) {
        return ['success' => false, 'message' => 'Formato de imagen no válido'];
    }
    
    // Generar nombre único
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nombreArchivo = uniqid('producto_') . '.' . $extension;
    $rutaDestino = $carpeta . $nombreArchivo;
    
    // Mover archivo
    if(move_uploaded_file($file['tmp_name'], $rutaDestino)) {
        return ['success' => true, 'ruta' => $rutaDestino];
    } else {
        return ['success' => false, 'message' => 'Error al guardar la imagen'];
    }
}
?>