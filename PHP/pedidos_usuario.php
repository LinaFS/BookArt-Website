<?php
session_start();
require_once __DIR__ . '/conexionBDD.php';

if (!isset($conexion) || !$conexion) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'No se pudo conectar a la base de datos']);
    exit;
}

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
    $id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : false;

    if ($id === false || $id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de pedido inválido']);
        return;
    }

    // Verificar que el pedido pertenezca al usuario y esté en estado modificable
    $queryVerify = "SELECT estatus, idPersonalizada, idTipoPedido 
                    FROM pedidos 
                    WHERE idPedido = ? AND IdCuenta = ?";
    if ($stmtVerify = mysqli_prepare($conexion, $queryVerify)) {
        mysqli_stmt_bind_param($stmtVerify, "ii", $id, $usuarioId);
        mysqli_stmt_execute($stmtVerify);
        $resultVerify = mysqli_stmt_get_result($stmtVerify);
        $pedido = mysqli_fetch_assoc($resultVerify);
        mysqli_stmt_close($stmtVerify);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al verificar el pedido']);
        return;
    }

    if (!$pedido) {
        echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
        return;
    }

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
        $queryRuta = "SELECT portada FROM personalizada WHERE id_personalizada = ?";
        if ($stmtRuta = mysqli_prepare($conexion, $queryRuta)) {
            mysqli_stmt_bind_param($stmtRuta, "i", $idPersonalizada);
            mysqli_stmt_execute($stmtRuta);
            $resultRuta = mysqli_stmt_get_result($stmtRuta);
            $rowRuta = mysqli_fetch_assoc($resultRuta);
            mysqli_stmt_close($stmtRuta);

            if ($rowRuta) {
                $rutaImagen = $rowRuta['portada'];
            }
        }
    }

    // Eliminar el pedido
    $queryDelete = "DELETE FROM pedidos WHERE idPedido = ? AND IdCuenta = ?";
    if ($stmtDelete = mysqli_prepare($conexion, $queryDelete)) {
        mysqli_stmt_bind_param($stmtDelete, "ii", $id, $usuarioId);
        mysqli_stmt_execute($stmtDelete);
        $resultDelete = mysqli_stmt_affected_rows($stmtDelete) >= 0;
        mysqli_stmt_close($stmtDelete);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el pedido']);
        return;
    }

    if ($resultDelete) {
        // Si era personalizada, eliminar registro y archivo
        if ($idPersonalizada && $pedido['idTipoPedido'] == 2) {
            $perDelete = "DELETE FROM personalizada WHERE id_personalizada = ?";
            if ($stmtPerDelete = mysqli_prepare($conexion, $perDelete)) {
                mysqli_stmt_bind_param($stmtPerDelete, "i", $idPersonalizada);
                mysqli_stmt_execute($stmtPerDelete);
                mysqli_stmt_close($stmtPerDelete);
            }

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