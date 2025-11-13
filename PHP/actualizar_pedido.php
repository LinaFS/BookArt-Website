<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../Inicio_sesion.php");
    exit;
}

include("conexionBDD.php");

$idPedido = mysqli_real_escape_string($conexion, $_POST['idPedido']);
$idPersonalizada = mysqli_real_escape_string($conexion, $_POST['idPersonalizada']);
$opcion = mysqli_real_escape_string($conexion, $_POST['opcFinal']);
$tamanio = mysqli_real_escape_string($conexion, $_POST['tamaño']);
$tipoPapel = mysqli_real_escape_string($conexion, $_POST['tipoPapel']);
$color = mysqli_real_escape_string($conexion, $_POST['color']);
$descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);

// Verificar que el pedido pertenezca al usuario
$usuario = $_SESSION["usuario"];
$queryId = "SELECT id_cuenta FROM cuenta WHERE correo = '$usuario' OR usuario = '$usuario'";
$executeQuery = mysqli_query($conexion, $queryId);
$usuarioIdArray = mysqli_fetch_assoc($executeQuery);
$usuarioId = $usuarioIdArray['id_cuenta'];

$queryVerify = "SELECT estatus FROM pedidos WHERE idPedido = '$idPedido' AND IdCuenta = '$usuarioId'";
$resultVerify = mysqli_query($conexion, $queryVerify);

if (!$resultVerify || mysqli_num_rows($resultVerify) == 0) {
    $mensaje = urlencode("Pedido no encontrado");
    header("Location: ../MisPedidos.php?mensaje=$mensaje&modal=true");
    exit;
}

$pedido = mysqli_fetch_assoc($resultVerify);
if (!in_array(strtolower($pedido['estatus']), ['pendiente', 'visto'])) {
    $mensaje = urlencode("Este pedido ya no puede ser editado");
    header("Location: ../MisPedidos.php?mensaje=$mensaje&modal=true");
    exit;
}

// Procesar nueva imagen si se subió
$rutaImagenNueva = null;

if (isset($_FILES["portada"]) && $_FILES["portada"]["error"] === 0) {
    $file = $_FILES["portada"];
    $nombreImg = $file["name"];
    $tipoImg = $file["type"];
    $ruta_provisional = $file["tmp_name"];
    $carpeta = "../Portadas/";

    if (!file_exists($carpeta)) {
        mkdir($carpeta, 0777, true);
    }

    $tiposPermitidos = ['image/jpg', 'image/jpeg', 'image/png', 'image/webp'];
    if (in_array($tipoImg, $tiposPermitidos)) {
        $extension = pathinfo($nombreImg, PATHINFO_EXTENSION);
        $nombreUnico = uniqid('portada_') . '.' . $extension;
        $src = $carpeta . $nombreUnico;

        if (move_uploaded_file($ruta_provisional, $src)) {
            // Eliminar imagen anterior
            $queryOldImg = "SELECT portada FROM personalizada WHERE id_personalizada = '$idPersonalizada'";
            $resultOldImg = mysqli_query($conexion, $queryOldImg);
            if ($resultOldImg) {
                $oldImg = mysqli_fetch_assoc($resultOldImg)['portada'];
                if (!empty($oldImg) && file_exists($oldImg)) {
                    unlink($oldImg);
                }
            }
            
            $rutaImagenNueva = $src;
        }
    }
}

// Actualizar personalizada
if ($rutaImagenNueva) {
    $queryUpdate = "UPDATE personalizada 
                    SET color = '$color', 
                        descripcion = '$descripcion', 
                        portada = '$rutaImagenNueva', 
                        tam = '$tamanio', 
                        tipo_encuadernacion = '$opcion', 
                        tipo_papel = '$tipoPapel'
                    WHERE id_personalizada = '$idPersonalizada'";
} else {
    $queryUpdate = "UPDATE personalizada 
                    SET color = '$color', 
                        descripcion = '$descripcion', 
                        tam = '$tamanio', 
                        tipo_encuadernacion = '$opcion', 
                        tipo_papel = '$tipoPapel'
                    WHERE id_personalizada = '$idPersonalizada'";
}

$resultUpdate = mysqli_query($conexion, $queryUpdate);

if ($resultUpdate) {
    mysqli_close($conexion);
    $mensaje = urlencode("✅ Pedido actualizado exitosamente");
    header("Location: ../MisPedidos.php?mensaje=$mensaje&modal=true");
    exit;
} else {
    mysqli_close($conexion);
    $mensaje = urlencode("❌ Error al actualizar el pedido: " . mysqli_error($conexion));
    header("Location: ../EditarPedido.php?id=$idPedido&mensaje=$mensaje&modal=true");
    exit;
}
?>