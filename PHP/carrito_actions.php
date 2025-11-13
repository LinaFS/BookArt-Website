<?php
session_start();
require("conexionBDD.php");

header('Content-Type: application/json');

if (!isset($_SESSION["usuario"])) {
    echo json_encode(['success' => false, 'message' => 'Sesión expirada']);
    exit;
}

// Obtener ID del usuario
$usuario = $_SESSION["usuario"];
$queryId = "SELECT id_cuenta FROM cuenta WHERE correo = '$usuario' OR usuario ='$usuario'";
$executeQuery = mysqli_query($conexion, $queryId);
$usuarioIdArray = mysqli_fetch_assoc($executeQuery);
$usuarioId = $usuarioIdArray['id_cuenta'];

$action = isset($_POST['action']) ? $_POST['action'] : '';

switch($action) {
    case 'add':
        agregarAlCarrito($conexion, $usuarioId);
        break;
    
    case 'remove':
        eliminarDelCarrito($conexion);
        break;
    
    case 'checkout':
        realizarCheckout($conexion, $usuarioId);
        break;
    case 'count':
        contarItemsCarrito($conexion, $usuarioId);
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}

// Agregar producto al carrito
function agregarAlCarrito($conexion, $usuarioId) {
    $tipo = mysqli_real_escape_string($conexion, $_POST['tipo']);
    $id = mysqli_real_escape_string($conexion, $_POST['id']);
    
    $fecha = date('Y-m-d');
    $hora = date('H:i:s');
    
    if ($tipo == 'catalogo') {
        // idTipoPedido = 1 para Catálogo
        $idTipoPedido = 1;
        
        // Verificar si ya existe en el carrito
        $check = "SELECT idPedido FROM pedidos 
                  WHERE id_cuenta = '$usuarioId' 
                  AND idCatalogo = '$id' 
                  AND idTipoPedido = '$idTipoPedido'
                  AND estatus = 'carrito'";
        $resultCheck = mysqli_query($conexion, $check);
        
        if (mysqli_num_rows($resultCheck) > 0) {
            echo json_encode(['success' => false, 'message' => 'Este producto ya está en tu carrito']);
            return;
        }
        
        $query = "INSERT INTO pedidos (fecha, hora, estatus, idCatalogo, idTipoPedido, id_cuenta) 
                  VALUES ('$fecha', '$hora', 'carrito', '$id', '$idTipoPedido', '$usuarioId')";
        
    } else if ($tipo == 'personalizada') {
        // idTipoPedido = 2 para Personalizada
        $idTipoPedido = 2;
        
        // Verificar si ya existe en el carrito
        $check = "SELECT idPedido FROM pedidos 
                  WHERE id_cuenta = '$usuarioId' 
                  AND idPersonalizada = '$id' 
                  AND idTipoPedido = '$idTipoPedido'
                  AND estatus = 'carrito'";
        $resultCheck = mysqli_query($conexion, $check);
        
        if (mysqli_num_rows($resultCheck) > 0) {
            echo json_encode(['success' => false, 'message' => 'Este diseño ya está en tu carrito']);
            return;
        }
        
        // Insertar pedido personalizado en la tabla pedidos
        $query = "INSERT INTO pedidos (fecha, hora, estatus, idPersonalizada, idTipoPedido, id_cuenta) 
                  VALUES ('$fecha', '$hora', 'carrito', '$id', '$idTipoPedido', '$usuarioId')";
    }
    
    $result = mysqli_query($conexion, $query);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Producto agregado al carrito']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al agregar al carrito']);
    }
}

// Eliminar producto del carrito
function eliminarDelCarrito($conexion) {
    $id = mysqli_real_escape_string($conexion, $_POST['id']);

    // Obtener el idPersonalizada del pedido
    $idper = "SELECT IdPersonalizada FROM pedidos WHERE idPedido = '$id' AND estatus = 'carrito'";
    $resultIdPer = mysqli_query($conexion, $idper);
    $row = mysqli_fetch_assoc($resultIdPer);
    $idPersonalizada = $row['IdPersonalizada'];

    if ($idPersonalizada) {
        // Obtener la ruta de la imagen antes de borrar la fila
        $queryRuta = "SELECT portada FROM personalizada WHERE id_personalizada = '$idPersonalizada'";
        $resultRuta = mysqli_query($conexion, $queryRuta);
        $rowRuta = mysqli_fetch_assoc($resultRuta);
        $rutaImagen = $rowRuta['portada'];

        // Eliminar el pedido
        $query = "DELETE FROM pedidos WHERE idPedido = '$id' AND estatus = 'carrito'";
        $result = mysqli_query($conexion, $query);

        if ($result) {
            // Eliminar registro de personalizada
            $perDelete = "DELETE FROM personalizada WHERE id_personalizada = '$idPersonalizada'";
            $deleteResult = mysqli_query($conexion, $perDelete);

            // Eliminar archivo físico si existe
            if (!empty($rutaImagen) && file_exists($rutaImagen)) {
                unlink($rutaImagen);
            }

            echo json_encode(['success' => true, 'message' => 'Producto eliminado del carrito y archivo borrado']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar del carrito']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró el diseño personalizado asociado.']);
    }
}


// Realizar checkout (cambiar estatus de carrito a pendiente)
function realizarCheckout($conexion, $usuarioId) {
    // Actualizar todos los pedidos del usuario que estén en carrito
    $query = "UPDATE pedidos 
               SET estatus = 'pendiente', fecha = CURDATE(), hora = CURTIME()
               WHERE id_cuenta = '$usuarioId' AND estatus = 'carrito'";
    
    $result = mysqli_query($conexion, $query);
    
    if ($result && mysqli_affected_rows($conexion) > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Pedido realizado con éxito'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Error al procesar el pedido o no hay productos en el carrito'
        ]);
    }
}

function contarItemsCarrito($conexion, $usuarioId) {
    $query = "SELECT COUNT(*) as total 
              FROM pedidos 
              WHERE IdCuenta = '$usuarioId' AND estatus = 'carrito'";
    
    $result = mysqli_query($conexion, $query);
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo json_encode([
            'success' => true, 
            'count' => (int)$row['total']
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'count' => 0
        ]);
    }
}
?>