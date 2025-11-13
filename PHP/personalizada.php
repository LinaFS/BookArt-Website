<?php
session_start();

if (isset($_SESSION['usuario'])) {
    include("conexionBDD.php");

    $opcion = $_POST["opcFinal"];
    $tamanio = $_POST["tamaño"];
    $tipoPapel = $_POST["tipoPapel"];
    $color = $_POST["color"];
    $descripcion = $_POST["descripcion"];
    $imagen = '';

    // Procesar la imagen si existe
    if (isset($_FILES["portada"]) && $_FILES["portada"]["error"] === 0) {
        $file = $_FILES["portada"];
        $nombreImg = $file["name"];
        $tipoImg = $file["type"];
        $ruta_provisional = $file["tmp_name"];
        $carpeta = "../Portadas/";

        // Crear carpeta si no existe
        if (!file_exists($carpeta)) {
            mkdir($carpeta, 0777, true);
        }

        // Validar tipo
        $tiposPermitidos = ['image/jpg', 'image/jpeg', 'image/png', 'image/webp'];
        if (in_array($tipoImg, $tiposPermitidos)) {
            $extension = pathinfo($nombreImg, PATHINFO_EXTENSION);
            $nombreUnico = uniqid('portada_') . '.' . $extension;
            $src = $carpeta . $nombreUnico;

            if (move_uploaded_file($ruta_provisional, $src)) {
                $imagen = $src; // Ruta guardada en BD
            }
        }
    }

    // Insertar en tabla personalizada
    $consult = "INSERT INTO personalizada (color, descripcion, portada, tam, tipo_encuadernacion, tipo_papel)
                VALUES ('$color', '$descripcion', '$imagen', '$tamanio', '$opcion', '$tipoPapel')";
    $execute_perso = mysqli_query($conexion, $consult);

    if ($execute_perso) {
        $idpersonalizada = mysqli_insert_id($conexion);

        // Obtener ID del usuario
        $usuario = $_SESSION["usuario"];
        $queryId = "SELECT id_cuenta FROM cuenta WHERE correo = '$usuario' OR usuario ='$usuario'";
        $executeQuery = mysqli_query($conexion, $queryId);
        $usuarioIdArray = mysqli_fetch_assoc($executeQuery);
        $usuarioId = $usuarioIdArray['id_cuenta'];

        $fecha = date('Y-m-d');
        $hora = date('H:i:s');

        // Crear el pedido (tipo 2 = personalizada)
        $consultPedido = "INSERT INTO pedidos (fecha, hora, estatus, idPersonalizada, idTipoPedido, IdCuenta)
                          VALUES ('$fecha', '$hora', 'carrito', '$idpersonalizada', 2, '$usuarioId')";
        $execute_pedido = mysqli_query($conexion, $consultPedido);

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
