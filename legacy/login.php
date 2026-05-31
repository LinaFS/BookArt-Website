<?php
    session_start();
    require_once __DIR__ . '/conexionBDD.php';
    if (!isset($conexion) || !$conexion) {
        exit('Error de conexión a la base de datos.');
    }

    $user = $_POST["sesionUsuario"];
    $password = hash('sha512', $_POST["sesionContra"]);

    $consult = "SELECT * FROM cuenta WHERE (correo = ? OR usuario = ?) AND contrasenia = ?";
    if ($stmt = mysqli_prepare($conexion, $consult)) {
        mysqli_stmt_bind_param($stmt, "sss", $user, $user, $password);
        mysqli_stmt_execute($stmt);
        $validate_login = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
    } else {
        // No exponer detalles de error en producción
        exit('Error interno de autenticación.');
    }

    if ($validate_login && mysqli_num_rows($validate_login) > 0) {
        $row = mysqli_fetch_assoc($validate_login);
        $permiso_id = $row['permiso_id'];

        if ($permiso_id == "1") {
            $_SESSION["usuario"] = $user;
            echo "<script>window.location='../Administrador.php';</script>";
        } else if ($permiso_id == "2") {
            $_SESSION["usuario"] = $user;
            echo "<script>window.location='../Catalogo.php';</script>";
        }
    } else {
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="../CSS/reset.css">
            <link rel="stylesheet" href="../CSS/styleSesion.css">
        </head>
        <body>
            <dialog id="warning" open>
                <p id="mensaje" class="error">❌ Error al iniciar sesión. Verifica tu usuario y contraseña</p>
                <div class="btnModal">
                    <button id="btnAcept" onclick="window.location='../Inicio_sesion.php'">Aceptar</button>
                </div>
            </dialog>
        </body>
        </html>
        <?php
        exit;
    }
?>