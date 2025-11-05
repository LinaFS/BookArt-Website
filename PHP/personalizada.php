<?php
    session_start();
    if (isset($_SESSION['usuario'])) {
        //error_reporting(E_ALL);
        //ini_set('display_errors', 1);
        include("conexionBDD.php");
        $opcion = $_POST["opcFinal"];
        $tamanio = $_POST["tamaño"]; 
        $tipoPapel = $_POST["tipoPapel"]; 
        $color = $_POST["color"]; 
        $descripcion = $_POST["descripcion"];
        $imagen = '';
        if(isset($_FILES["portada"])) {
            $file = $_FILES["portada"];
            $nombreImg = $file["name"];
            $tipoImg = $file["type"];
            $ruta_provisional = $file["tmp_name"];
            $carpeta = "../Portadas/";
            if($tipoImg == 'image/jpg' || $tipoImg == 'image/JPG' || $tipoImg == 'image/jpeg' || $tipoImg == 'image/png') {
                $src = $carpeta.$nombreImg;
                move_uploaded_file($ruta_provisional, $src);
                $imagen = $nombreImg;
            }
        }
        $consult = "INSERT INTO personalizada (color, descripcion, portada, tam, tipo_encuadernacion, tipo_papel) VALUES ('$color', '$descripcion', '$imagen', '$tamanio', '$opcion', '$tipoPapel')";
        $execute_perso = mysqli_query($conexion, $consult);
        $idpersonalizada = mysqli_insert_id($conexion);
        $usu  = $_SESSION['usuario'];

        $usuario = $_SESSION["usuario"];
        
        $queryId = "SELECT usuario_id FROM cuenta WHERE correo = '$usuario' OR usuario ='$usuario'";
        $executeQuery = mysqli_query($conexion, $queryId);
        if ($executeQuery) {
            $usuarioIdArray = mysqli_fetch_assoc($executeQuery);
            $usuarioId = $usuarioIdArray['usuario_id'];
        }

        $fecha = date('Y-m-d');
        $hora = date('H:i:s');
        $consult = "INSERT INTO pedidosper (fecha, hora, usuario_id, personalizada_id) VALUES ( '$fecha', '$hora', '$usuarioId', '$idpersonalizada')";
        $execute_perso = mysqli_query($conexion, $consult);
        $idpedidoPer = mysqli_insert_id($conexion);
        
        mysqli_close($conexion);
        $mensaje = urlencode("¡Se ha realizado tu pedido!");
        header("Location: ../Personalizada.php?mensaje=$mensaje&modal=true");
        exit;
    } else {
        $mensaje = urlencode("¡No se ha completado tu pedido, revisa tu información!");
        header("Location: ../Personalizada.php?mensaje=$mensaje&modal=true");
        exit;
    }
?>
