<?php
session_start();
require("conexionBDD.php");

header('Content-Type: application/json');

if (!isset($_SESSION["usuario"])) {
    echo json_encode(['success' => false, 'message' => 'Sesión expirada']);
    exit;
}

// Obtener ID del usuario CON SENTENCIAS PREPARADAS
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

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch($action) {
    case 'add':
        agregarAlCarrito($conexion, $usuarioId);
        break;
    
    case 'remove':
        eliminarDelCarrito($conexion, $usuarioId);
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
                  WHERE IdCuenta = ? 
                  AND idCatalogo = ? 
                  AND idTipoPedido = ?
                  AND estatus = 'carrito'";
        
        if ($stmtCheck = mysqli_prepare($conexion, $check)) {
            mysqli_stmt_bind_param($stmtCheck, "iii", $usuarioId, $id, $idTipoPedido);
            mysqli_stmt_execute($stmtCheck);
            $resultCheck = mysqli_stmt_get_result($stmtCheck);
            
            if (mysqli_num_rows($resultCheck) > 0) {
                mysqli_stmt_close($stmtCheck);
                echo json_encode(['success' => false, 'message' => 'Este producto ya está en tu carrito']);
                return;
            }
            mysqli_stmt_close($stmtCheck);
        }
        
        // Insertar en el carrito
        $query = "INSERT INTO pedidos (fecha, hora, estatus, idCatalogo, idTipoPedido, IdCuenta) 
                  VALUES (?, ?, 'carrito', ?, ?, ?)";
        
        if ($stmt = mysqli_prepare($conexion, $query)) {
            mysqli_stmt_bind_param($stmt, "ssiii", $fecha, $hora, $id, $idTipoPedido, $usuarioId);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Producto agregado al carrito']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al agregar al carrito: ' . mysqli_error($conexion)]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al preparar consulta: ' . mysqli_error($conexion)]);
        }
        
    } else if ($tipo == 'personalizada') {
        // idTipoPedido = 2 para Personalizada
        $idTipoPedido = 2;
        
        // Verificar si ya existe en el carrito
        $check = "SELECT idPedido FROM pedidos 
                  WHERE IdCuenta = ? 
                  AND idPersonalizada = ? 
                  AND idTipoPedido = ?
                  AND estatus = 'carrito'";
        
        if ($stmtCheck = mysqli_prepare($conexion, $check)) {
            mysqli_stmt_bind_param($stmtCheck, "iii", $usuarioId, $id, $idTipoPedido);
            mysqli_stmt_execute($stmtCheck);
            $resultCheck = mysqli_stmt_get_result($stmtCheck);
            
            if (mysqli_num_rows($resultCheck) > 0) {
                mysqli_stmt_close($stmtCheck);
                echo json_encode(['success' => false, 'message' => 'Este diseño ya está en tu carrito']);
                return;
            }
            mysqli_stmt_close($stmtCheck);
        }
        
        // Insertar pedido personalizado en la tabla pedidos
        $query = "INSERT INTO pedidos (fecha, hora, estatus, idPersonalizada, idTipoPedido, IdCuenta) 
                  VALUES (?, ?, 'carrito', ?, ?, ?)";
        
        if ($stmt = mysqli_prepare($conexion, $query)) {
            mysqli_stmt_bind_param($stmt, "ssiii", $fecha, $hora, $id, $idTipoPedido, $usuarioId);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Diseño agregado al carrito']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al agregar al carrito: ' . mysqli_error($conexion)]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al preparar consulta: ' . mysqli_error($conexion)]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Tipo de producto no válido']);
    }
}

function eliminarDelCarrito($conexion, $usuarioId) {
    $id = mysqli_real_escape_string($conexion, $_POST['id']);

    // Obtener información del pedido
    $queryInfo = "SELECT idPersonalizada, idTipoPedido FROM pedidos WHERE idPedido = ? AND estatus = 'carrito' AND IdCuenta = ?";
    
    if ($stmtInfo = mysqli_prepare($conexion, $queryInfo)) {
        mysqli_stmt_bind_param($stmtInfo, "ii", $id, $usuarioId);
        mysqli_stmt_execute($stmtInfo);
        $resultInfo = mysqli_stmt_get_result($stmtInfo);
        $row = mysqli_fetch_assoc($resultInfo);
        mysqli_stmt_close($stmtInfo);
        
        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
            return;
        }
        
        $idPersonalizada = $row['idPersonalizada'];
        $idTipoPedido = $row['idTipoPedido'];
        
        // Eliminar el pedido
        $queryDelete = "DELETE FROM pedidos WHERE idPedido = ? AND estatus = 'carrito' AND IdCuenta = ?";
        
        if ($stmtDelete = mysqli_prepare($conexion, $queryDelete)) {
            mysqli_stmt_bind_param($stmtDelete, "ii", $id, $usuarioId);
            $result = mysqli_stmt_execute($stmtDelete);
            mysqli_stmt_close($stmtDelete);
            
            if ($result) {
                // Si era personalizada, eliminar registro y archivo
                if ($idTipoPedido == 2 && $idPersonalizada) {
                    // Obtener ruta de la imagen
                    $queryRuta = "SELECT portada FROM personalizada WHERE id_personalizada = ?";
                    if ($stmtRuta = mysqli_prepare($conexion, $queryRuta)) {
                        mysqli_stmt_bind_param($stmtRuta, "i", $idPersonalizada);
                        mysqli_stmt_execute($stmtRuta);
                        $resultRuta = mysqli_stmt_get_result($stmtRuta);
                        $rowRuta = mysqli_fetch_assoc($resultRuta);
                        mysqli_stmt_close($stmtRuta);
                        
                        if ($rowRuta) {
                            $rutaImagen = $rowRuta['portada'];
                            
                            // Eliminar registro de personalizada
                            $perDelete = "DELETE FROM personalizada WHERE id_personalizada = ?";
                            if ($stmtPerDelete = mysqli_prepare($conexion, $perDelete)) {
                                mysqli_stmt_bind_param($stmtPerDelete, "i", $idPersonalizada);
                                mysqli_stmt_execute($stmtPerDelete);
                                mysqli_stmt_close($stmtPerDelete);
                            }
                            
                            // Eliminar archivo físico si existe
                            if (!empty($rutaImagen) && file_exists($rutaImagen)) {
                                unlink($rutaImagen);
                            }
                        }
                    }
                }
                
                echo json_encode(['success' => true, 'message' => 'Producto eliminado del carrito']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar del carrito']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al preparar consulta de eliminación']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al preparar consulta de información']);
    }
}

function realizarCheckout($conexion, $usuarioId) {
    // Verificar que haya productos en el carrito
    $checkQuery = "SELECT COUNT(*) as total FROM pedidos 
                   WHERE IdCuenta = ? AND estatus = 'carrito'";
    
    if ($stmtCheck = mysqli_prepare($conexion, $checkQuery)) {
        mysqli_stmt_bind_param($stmtCheck, "i", $usuarioId);
        mysqli_stmt_execute($stmtCheck);
        $checkResult = mysqli_stmt_get_result($stmtCheck);
        $row = mysqli_fetch_assoc($checkResult);
        mysqli_stmt_close($stmtCheck);
        
        if ($row['total'] == 0) {
            echo json_encode(['success' => false, 'message' => 'No hay productos en el carrito']);
            return;
        }
        
        // Actualizar todos los pedidos del usuario que estén en carrito
        $query = "UPDATE pedidos 
                  SET estatus = 'pendiente', fecha = CURDATE(), hora = CURTIME()
                  WHERE IdCuenta = ? AND estatus = 'carrito'";
        
        if ($stmt = mysqli_prepare($conexion, $query)) {
            mysqli_stmt_bind_param($stmt, "i", $usuarioId);
            $result = mysqli_stmt_execute($stmt);
            $affectedRows = mysqli_affected_rows($conexion);
            mysqli_stmt_close($stmt);
            
            if ($result && $affectedRows > 0) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Pedido realizado con éxito',
                    'pedidos_procesados' => $affectedRows
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'No se pudo procesar el pedido. Intenta nuevamente.'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Error al preparar consulta: ' . mysqli_error($conexion)
            ]);
        }
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Error al verificar el carrito: ' . mysqli_error($conexion)
        ]);
    }
}

function contarItemsCarrito($conexion, $usuarioId) {
    $query = "SELECT COUNT(*) as total 
              FROM pedidos 
              WHERE IdCuenta = ? AND estatus = 'carrito'";
    
    if ($stmt = mysqli_prepare($conexion, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $usuarioId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        echo json_encode([
            'success' => true, 
            'count' => (int)$row['total']
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'count' => 0,
            'message' => 'Error al contar items: ' . mysqli_error($conexion)
        ]);
    }
}
?>