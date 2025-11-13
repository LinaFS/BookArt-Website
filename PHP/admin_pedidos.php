<?php
session_start();
include("conexionBDD.php");

// Verificar que sea administrador
if (!isset($_SESSION["usuario"])) {
    echo json_encode(['success' => false, 'message' => 'Sesión expirada']);
    exit;
}

// Verificar permiso de administrador
$usuario = $_SESSION["usuario"];
$queryPermiso = "SELECT permiso_id FROM cuenta WHERE correo = '$usuario' OR usuario = '$usuario'";
$resultPermiso = mysqli_query($conexion, $queryPermiso);
$permisoData = mysqli_fetch_assoc($resultPermiso);

if ($permisoData['permiso_id'] != 1) {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos de administrador']);
    exit;
}

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch($action) {
    case 'listar':
        listarPedidos($conexion);
        break;
    
    case 'detalle':
        obtenerDetallePedido($conexion);
        break;
    
    case 'cambiar_estatus':
        cambiarEstatus($conexion);
        break;

    case 'estadisticas':
        obtenerEstadisticas($conexion);
        break;
    
    case 'asignar_precio':
        asignarPrecioPersonalizada($conexion);
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}

function listarPedidos($conexion) {
    // Obtener todos los pedidos usando tipoPedido
    $query = "
        SELECT 
            p.idPedido AS id,
            p.fecha,
            p.hora,
            p.estatus,
            tp.descripcion AS tipo,
            tp.id_tipoPedido,
            CASE 
                WHEN tp.id_tipoPedido = 1 THEN c.nombre
                WHEN tp.id_tipoPedido = 2 THEN 'Libreta Personalizada'
            END AS producto_nombre,
            CASE 
                WHEN tp.id_tipoPedido = 1 THEN c.precio
                WHEN tp.id_tipoPedido = 2 THEN COALESCE(per.precio, 0)
            END AS producto_precio,
            u.nombre AS cliente_nombre,
            u.paterno,
            u.materno,
            cu.correo AS cliente_correo,
            cu.usuario AS cliente_usuario
        FROM pedidos p
        INNER JOIN tipoPedido tp ON p.idTipoPedido = tp.id_tipoPedido
        LEFT JOIN catalogo c ON p.idCatalogo = c.id_producto AND tp.id_tipoPedido = 1
        LEFT JOIN personalizada per ON p.idPersonalizada = per.id_personalizada AND tp.id_tipoPedido = 2
        
        -- UNIONES DEL CLIENTE CORREGIDAS
        INNER JOIN cuenta cu ON p.idCuenta = cu.id_cuenta 
        INNER JOIN usuario u ON cu.usuario_id = u.id_usuario 
        
        WHERE p.estatus != 'carrito'
        ORDER BY p.fecha DESC, p.hora DESC
    ";
    
    $result = mysqli_query($conexion, $query);
    
    if($result) {
        $pedidos = [];
        while($row = mysqli_fetch_assoc($result)) {
            $pedidos[] = $row;
        }
        echo json_encode(['success' => true, 'pedidos' => $pedidos]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al consultar pedidos: ' . mysqli_error($conexion)]);
    }
}

function obtenerDetallePedido($conexion) {
    $id = mysqli_real_escape_string($conexion, $_GET['id']);
    
    $query = "
        SELECT 
            p.*,
            tp.descripcion as tipo_descripcion,
            tp.id_tipoPedido,
            CASE 
                WHEN tp.id_tipoPedido = 1 THEN c.nombre
                WHEN tp.id_tipoPedido = 2 THEN 'Libreta Personalizada'
            END as producto_nombre,
            CASE 
                WHEN tp.id_tipoPedido = 1 THEN c.precio
                WHEN tp.id_tipoPedido = 2 THEN COALESCE(per.precio, 0)
            END as producto_precio,
            CASE 
                WHEN tp.id_tipoPedido = 1 THEN c.descripcion
                WHEN tp.id_tipoPedido = 2 THEN per.descripcion
            END as producto_descripcion,
            CASE 
                WHEN tp.id_tipoPedido = 1 THEN c.img
                WHEN tp.id_tipoPedido = 2 THEN per.portada
            END as producto_img,
            per.color,
            per.tam,
            per.tipo_encuadernacion,
            per.tipo_papel,
            u.nombre as cliente_nombre,
            u.paterno as cliente_paterno,
            u.materno as cliente_materno,
            u.tel as cliente_tel,
            cu.correo as cliente_correo,
            cu.usuario as cliente_usuario
        FROM pedidos p
        INNER JOIN tipoPedido tp ON p.idTipoPedido = tp.id_tipoPedido
        LEFT JOIN catalogo c ON p.idCatalogo = c.id_producto AND tp.id_tipoPedido = 1
        LEFT JOIN personalizada per ON p.idPersonalizada = per.id_personalizada AND tp.id_tipoPedido = 2
        INNER JOIN cuenta cu ON p.idCuenta = cu.id_cuenta
        INNER JOIN usuario u ON cu.usuario_id = u.id_usuario
        WHERE p.idPedido = '$id'
    ";
    
    $result = mysqli_query($conexion, $query);
    
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $isCatalogo = ($row['id_tipoPedido'] == 1);
        
        $pedido = [
            'id' => $row['idPedido'],
            'fecha' => $row['fecha'],
            'hora' => $row['hora'],
            'estatus' => $row['estatus'],
            'tipo' => $isCatalogo ? 'catalogo' : 'personalizada',
            'cliente' => [
                'nombre' => $row['cliente_nombre'],
                'paterno' => $row['cliente_paterno'],
                'materno' => $row['cliente_materno'],
                'tel' => $row['cliente_tel'],
                'correo' => $row['cliente_correo'],
                'usuario' => $row['cliente_usuario']
            ],
            'producto' => [
                'nombre' => $row['producto_nombre'],
                'precio' => $row['producto_precio'],
                'descripcion' => $row['producto_descripcion'],
                'img' => $row['producto_img']
            ]
        ];
        
        // Agregar datos adicionales si es personalizada
        if (!$isCatalogo) {
            $pedido['producto']['color'] = $row['color'];
            $pedido['producto']['tam'] = $row['tam'];
            $pedido['producto']['tipo_encuadernacion'] = $row['tipo_encuadernacion'];
            $pedido['producto']['tipo_papel'] = $row['tipo_papel'];
            $pedido['producto']['portada'] = $row['producto_img'];
        }
        
        echo json_encode(['success' => true, 'pedido' => $pedido]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
    }
}

function cambiarEstatus($conexion) {
    $id = mysqli_real_escape_string($conexion, $_POST['pedidoId']);
    $nuevoEstatus = mysqli_real_escape_string($conexion, $_POST['estatus']);
    $mensaje = isset($_POST['mensaje']) ? mysqli_real_escape_string($conexion, $_POST['mensaje']) : '';
    
    // Validar estatus
    $estatusValidos = ['pendiente', 'visto', 'aprobado', 'declinado', 'proceso', 'terminado', 'entregado'];
    if(!in_array($nuevoEstatus, $estatusValidos)) {
        echo json_encode(['success' => false, 'message' => 'Estatus no válido']);
        return;
    }
    
    // Actualizar en la tabla pedidos
    $query = "UPDATE pedidos SET estatus = '$nuevoEstatus' WHERE idPedido = '$id'";
    
    $result = mysqli_query($conexion, $query);
    
    if($result) {
        $estatusTexto = '';
        switch($nuevoEstatus) {
            case 'pendiente': $estatusTexto = 'Pendiente'; break;
            case 'visto': $estatusTexto = 'Visto'; break;
            case 'aprobado': $estatusTexto = 'Aprobado'; break;
            case 'declinado': $estatusTexto = 'Declinado'; break;
            case 'proceso': $estatusTexto = 'En Proceso'; break;
            case 'terminado': $estatusTexto = 'Terminado'; break;
            case 'entregado': $estatusTexto = 'Entregado'; break;
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Estatus actualizado a: ' . $estatusTexto
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar estatus: ' . mysqli_error($conexion)]);
    }
}

function obtenerEstadisticas($conexion) {
    // 1. Contar pedidos activos (excluyendo carrito, entregados y declinados)
    $queryPedidos = "SELECT COUNT(*) as total FROM pedidos 
                     WHERE estatus NOT IN ('carrito', 'entregado', 'declinado')";
    $resultPedidos = mysqli_query($conexion, $queryPedidos);
    $pedidosActivos = mysqli_fetch_assoc($resultPedidos)['total'] ?? 0;
    
    // 2. Contar total de clientes únicos
    $queryClientes = "SELECT COUNT(DISTINCT id_cuenta) as total FROM cuenta WHERE permiso_id = 2";
    $resultClientes = mysqli_query($conexion, $queryClientes);
    $totalClientes = mysqli_fetch_assoc($resultClientes)['total'] ?? 0;
    
    // 3. Calcular ventas del mes actual (pedidos del catálogo entregados + personalizadas con precio asignado)
    $queryVentas = "SELECT 
                        (SELECT COALESCE(SUM(c.precio), 0)
                         FROM pedidos p
                         INNER JOIN catalogo c ON p.idCatalogo = c.id_producto
                         WHERE p.idTipoPedido = 1 
                         AND p.estatus = 'entregado'
                         AND MONTH(p.fecha) = MONTH(CURDATE())
                         AND YEAR(p.fecha) = YEAR(CURDATE()))
                        +
                        (SELECT COALESCE(SUM(per.precio), 0)
                         FROM pedidos p
                         INNER JOIN personalizada per ON p.idPersonalizada = per.id_personalizada
                         WHERE p.idTipoPedido = 2 
                         AND p.estatus = 'entregado'
                         AND per.precio > 0
                         AND MONTH(p.fecha) = MONTH(CURDATE())
                         AND YEAR(p.fecha) = YEAR(CURDATE()))
                        as total";
    $resultVentas = mysqli_query($conexion, $queryVentas);
    $ventasMes = mysqli_fetch_assoc($resultVentas)['total'] ?? 0;
    
    echo json_encode([
        'success' => true,
        'pedidos_activos' => (int)$pedidosActivos,
        'total_clientes' => (int)$totalClientes,
        'ventas_mes' => (float)$ventasMes
    ]);
}

function asignarPrecioPersonalizada($conexion) {
    $idPedido = mysqli_real_escape_string($conexion, $_POST['pedidoId']);
    $precio = mysqli_real_escape_string($conexion, $_POST['precio']);
    
    // Validar precio
    if(!is_numeric($precio) || $precio < 0) {
        echo json_encode(['success' => false, 'message' => 'Precio no válido']);
        return;
    }
    
    // Obtener el idPersonalizada del pedido
    $queryCheck = "SELECT idPersonalizada, idTipoPedido 
                   FROM pedidos 
                   WHERE idPedido = '$idPedido'";
    $resultCheck = mysqli_query($conexion, $queryCheck);
    
    if(!$resultCheck || mysqli_num_rows($resultCheck) == 0) {
        echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
        return;
    }
    
    $pedido = mysqli_fetch_assoc($resultCheck);
    
    // Verificar que sea personalizada
    if($pedido['idTipoPedido'] != 2) {
        echo json_encode(['success' => false, 'message' => 'Solo se puede asignar precio a libretas personalizadas']);
        return;
    }
    
    $idPersonalizada = $pedido['idPersonalizada'];
    
    // Actualizar precio en la tabla personalizada
    $query = "UPDATE personalizada 
              SET precio = '$precio' 
              WHERE id_personalizada = '$idPersonalizada'";
    
    $result = mysqli_query($conexion, $query);
    
    if($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'Precio asignado correctamente: $' . number_format($precio, 2)
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al asignar precio: ' . mysqli_error($conexion)]);
    }
}
?>