<?php
session_start();
require("conexionBDD.php");

header('Content-Type: application/json');

if (!isset($_SESSION["usuario"])) {
    echo json_encode(['success' => false, 'message' => 'Sesión expirada']);
    exit;
}

// Obtener ID del usuario con SENTENCIAS PREPARADAS
$usuario = $_SESSION["usuario"];
$usuarioId = null;

$queryId = "SELECT id_cuenta FROM cuenta WHERE correo = ? OR usuario = ?";
if ($stmt = mysqli_prepare($conexion, $queryId)) {
    mysqli_stmt_bind_param($stmt, "ss", $usuario, $usuario);
    mysqli_stmt_execute($stmt);
    $executeQuery = mysqli_stmt_get_result($stmt);
    $usuarioIdArray = mysqli_fetch_assoc($executeQuery);
    
    if ($usuarioIdArray) {
        $usuarioId = $usuarioIdArray['id_cuenta'];
    }
    mysqli_stmt_close($stmt);
}

if (!$usuarioId) {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

switch($action) {
    case 'cancel':
        cancelarPedido($conexion, $usuarioId);
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}

function cancelarPedido($conexion, $usuarioId) {
    $id = mysqli_real_escape_string($conexion, $_POST['id']);
    
    // Verificar que el pedido pertenezca al usuario y esté en estado modificable
    $queryVerify = "SELECT estatus, idPersonalizada, idTipoPedido 
                    FROM pedidos 
                    WHERE idPedido = '$id' AND IdCuenta = '$usuarioId'";
    $resultVerify = mysqli_query($conexion, $queryVerify);
    
    if (!$resultVerify || mysqli_num_rows($resultVerify) == 0) {
        echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
        return;
    }
    
    $pedido = mysqli_fetch_assoc($resultVerify);
    $estatus = strtolower($pedido['estatus']);
    
    // Solo permitir cancelar si está en estados iniciales
    if (!in_array($estatus, ['pendiente', 'visto', 'carrito'])) {
        echo json_encode(['success' => false, 'message' => 'Este pedido ya no puede ser cancelado']);
        return;
    }
    
    // Si es personalizada, obtener la ruta de la imagen
    $idPersonalizada = $pedido['idPersonalizada'];
    $rutaImagen = null;
    
    if ($idPersonalizada && $pedido['idTipoPedido'] == 2) {
        $queryRuta = "SELECT portada FROM personalizada WHERE id_personalizada = '$idPersonalizada'";
        $resultRuta = mysqli_query($conexion, $queryRuta);
        if ($resultRuta && mysqli_num_rows($resultRuta) > 0) {
            $rowRuta = mysqli_fetch_assoc($resultRuta);
            $rutaImagen = $rowRuta['portada'];
        }
    }
    
    // Eliminar el pedido
    $queryDelete = "DELETE FROM pedidos WHERE idPedido = '$id' AND IdCuenta = '$usuarioId'";
    $resultDelete = mysqli_query($conexion, $queryDelete);
    
    if ($resultDelete) {
        // Si era personalizada, eliminar registro y archivo
        if ($idPersonalizada && $pedido['idTipoPedido'] == 2) {
            $perDelete = "DELETE FROM personalizada WHERE id_personalizada = '$idPersonalizada'";
            mysqli_query($conexion, $perDelete);
            
            // Eliminar archivo físico si existe
            if (!empty($rutaImagen) && file_exists($rutaImagen)) {
                unlink($rutaImagen);
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Pedido cancelado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al cancelar el pedido']);
    }
}
?>