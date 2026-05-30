<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../Inicio_sesion.php");
    exit;
}

require_once __DIR__ . '/conexionBDD.php';
if (!isset($conexion) || !$conexion) {
    exit('Error de conexión a la base de datos.');
}

$idPedido = $_POST['idPedido'];
$idPersonalizada = $_POST['idPersonalizada'];
$opcion = $_POST['opcFinal'];
$tamanio = $_POST['tamaño'];
$tipoPapel = $_POST['tipoPapel'];
$color = $_POST['color'];
$descripcion = $_POST['descripcion'];

$usuario = $_SESSION["usuario"];
$queryId = "SELECT id_cuenta FROM cuenta WHERE correo = ? OR usuario = ?";
if ($stmt = mysqli_prepare($conexion, $queryId)) {
    mysqli_stmt_bind_param($stmt, "ss", $usuario, $usuario);
    mysqli_stmt_execute($stmt);
    $executeQuery = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
}
$usuarioIdArray = mysqli_fetch_assoc($executeQuery);
$usuarioId = $usuarioIdArray['id_cuenta'];

$queryVerify = "SELECT estatus FROM pedidos WHERE idPedido = ? AND IdCuenta = ?";
if ($stmt = mysqli_prepare($conexion, $queryVerify)) {
    mysqli_stmt_bind_param($stmt, "ii", $idPedido, $usuarioId);
    mysqli_stmt_execute($stmt);
    $resultVerify = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
} else {
    $resultVerify = false;
}

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
            $queryOldImg = "SELECT portada FROM personalizada WHERE id_personalizada = ?";
            if ($stmt = mysqli_prepare($conexion, $queryOldImg)) {
                mysqli_stmt_bind_param($stmt, "i", $idPersonalizada);
                mysqli_stmt_execute($stmt);
                $resultOldImg = mysqli_stmt_get_result($stmt);
                mysqli_stmt_close($stmt);

                if ($resultOldImg) {
                    $oldImg = mysqli_fetch_assoc($resultOldImg)['portada'];
                    if (!empty($oldImg) && file_exists($oldImg)) {
                        unlink($oldImg);
                    }
                }
            }
            $rutaImagenNueva = $src;
        }
    }
}

if ($rutaImagenNueva) {
    $queryUpdate = "UPDATE personalizada SET color = ?, descripcion = ?, portada = ?, tam = ?, tipo_encuadernacion = ?, tipo_papel = ? WHERE id_personalizada = ?";
    if ($stmt = mysqli_prepare($conexion, $queryUpdate)) {
        mysqli_stmt_bind_param($stmt, "ssssssi", $color, $descripcion, $rutaImagenNueva, $tamanio, $opcion, $tipoPapel, $idPersonalizada);
        $resultUpdate = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $resultUpdate = false;
    }
} else {
    $queryUpdate = "UPDATE personalizada SET color = ?, descripcion = ?, tam = ?, tipo_encuadernacion = ?, tipo_papel = ? WHERE id_personalizada = ?";
    if ($stmt = mysqli_prepare($conexion, $queryUpdate)) {
        mysqli_stmt_bind_param($stmt, "sssssi", $color, $descripcion, $tamanio, $opcion, $tipoPapel, $idPersonalizada);
        $resultUpdate = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $resultUpdate = false;
    }
}

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