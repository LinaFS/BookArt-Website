<?php
session_start();

if (isset($_SESSION['usuario'])) {
    require_once __DIR__ . '/conexionBDD.php';
    if (!isset($conexion) || !$conexion) {
        exit('Error de conexión a la base de datos.');
    }

    $opcion = $_POST["opcFinal"];
    $tamanio = $_POST["tamaño"];
    $tipoPapel = $_POST["tipoPapel"];
    $color = $_POST["color"];
    $descripcion = $_POST["descripcion"];
    $imagen = '';

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
                $imagen = $src;
            }
        }
    }

    $consult = "INSERT INTO personalizada (color, descripcion, portada, tam, tipo_encuadernacion, tipo_papel)
                VALUES (?, ?, ?, ?, ?, ?)";
    if ($stmt = mysqli_prepare($conexion, $consult)) {
        mysqli_stmt_bind_param($stmt, "ssssss", $color, $descripcion, $imagen, $tamanio, $opcion, $tipoPapel);
        $execute_perso = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $execute_perso = false;
    }

    if ($execute_perso) {
        $idpersonalizada = mysqli_insert_id($conexion);

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

        $fecha = date('Y-m-d');
        $hora = date('H:i:s');

        $consultPedido = "INSERT INTO pedidos (fecha, hora, estatus, idPersonalizada, idTipoPedido, IdCuenta)
                          VALUES (?, ?, 'carrito', ?, 2, ?)";
        if ($stmt = mysqli_prepare($conexion, $consultPedido)) {
            mysqli_stmt_bind_param($stmt, "ssii", $fecha, $hora, $idpersonalizada, $usuarioId);
            $execute_pedido = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            $execute_pedido = false;
        }

        if ($execute_pedido) {
            mysqli_close($conexion);
            $mensaje = urlencode("✨ ¡Diseño creado exitosamente! Ha sido agregado a tu carrito.");
            header("Location: ../Carrito.php?mensaje=$mensaje&modal=true");
            exit;
        } else {
            mysqli_close($conexion);
            $mensaje = urlencode("Error al crear el pedido: " . mysqli_error($conexion));
            header("Location: ../Personalizada.php?mensaje=$mensaje&modal=true");
            exit;
        }
    } else {
        mysqli_close($conexion);
        $mensaje = urlencode("Error al crear el diseño: " . mysqli_error($conexion));
        header("Location: ../Personalizada.php?mensaje=$mensaje&modal=true");
        exit;
    }
} else {
    $mensaje = urlencode("Debes iniciar sesión para crear un diseño personalizado");
    header("Location: ../Inicio_sesion.php?origen=Personalizada&mensaje=$mensaje");
    exit;
}
?>
