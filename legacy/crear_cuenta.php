<?php
    require_once __DIR__ . '/conexionBDD.php';
    if (!isset($conexion) || !$conexion) {
        exit('Error de conexión a la base de datos.');
    }

    $nombre = $_POST['nombre'];
    $paterno = $_POST['paterno'];
    $materno = $_POST['materno'];
    $tel = $_POST['tel'];
    $dia = $_POST['Dia'];
    $mes = $_POST['Mes'];
    $anio = $_POST['anio'];
    $usuario = $_POST['usuario'];
    $correo = $_POST['correo'];
    $contra = $_POST['contrasena'];
    $permiso = 2;

    $contra = hash("sha512", $contra);

    $query_usuario = "INSERT INTO usuario(nombre, paterno, materno, tel, dia, mes, anio)
            VALUES(?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = mysqli_prepare($conexion, $query_usuario)) {
        mysqli_stmt_bind_param($stmt, "sssssss", $nombre, $paterno, $materno, $tel, $dia, $mes, $anio);
        $execute_usuario = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        exit('Error interno al crear usuario.');
    }

    $idusuario = mysqli_insert_id($conexion);

    if ($execute_usuario) {
        $verify_email = "SELECT 1 FROM cuenta WHERE correo = ?";
        if ($stmt = mysqli_prepare($conexion, $verify_email)) {
            mysqli_stmt_bind_param($stmt, "s", $correo);
            mysqli_stmt_execute($stmt);
            $result_email = mysqli_stmt_get_result($stmt);
            mysqli_stmt_close($stmt);
        }

        if ($result_email && mysqli_num_rows($result_email) > 0) {
            $drop = "DELETE FROM usuario WHERE id_usuario = ?";
            if ($stmt = mysqli_prepare($conexion, $drop)) {
                mysqli_stmt_bind_param($stmt, "i", $idusuario);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <link rel="stylesheet" href="../CSS/styleSesion.css">
            </head>
            <body>
                <dialog id="warning" open>
                    <p id="mensaje" class="error">❌ Este correo ya está registrado</p>
                    <div class="btnModal">
                        <button id="btnAcept" onclick="window.location='../Inicio_sesion.php'">Aceptar</button>
                    </div>
                </dialog>
            </body>
            </html>
            <?php
            exit;
        }

        $verify_user = "SELECT 1 FROM cuenta WHERE usuario = ?";
        if ($stmt = mysqli_prepare($conexion, $verify_user)) {
            mysqli_stmt_bind_param($stmt, "s", $usuario);
            mysqli_stmt_execute($stmt);
            $result_user = mysqli_stmt_get_result($stmt);
            mysqli_stmt_close($stmt);
        }

        if ($result_user && mysqli_num_rows($result_user) > 0) {
            $drop = "DELETE FROM usuario WHERE id_usuario = ?";
            if ($stmt = mysqli_prepare($conexion, $drop)) {
                mysqli_stmt_bind_param($stmt, "i", $idusuario);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <link rel="stylesheet" href="../CSS/styleSesion.css">
            </head>
            <body>
                <dialog id="warning" open>
                    <p id="mensaje" class="error">❌ Este nombre de usuario ya está registrado</p>
                    <div class="btnModal">
                        <button id="btnAcept" onclick="window.location='../Inicio_sesion.php'">Aceptar</button>
                    </div>
                </dialog>
            </body>
            </html>
            <?php
            exit;
        }

        $query_cuenta = "INSERT INTO cuenta(usuario, correo, contrasenia, permiso_id, usuario_id)
            VALUES(?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($conexion, $query_cuenta)) {
            mysqli_stmt_bind_param($stmt, "sssii", $usuario, $correo, $contra, $permiso, $idusuario);
            $execute_cuenta = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            exit('Error interno al crear cuenta.');
        }

        if ($execute_cuenta) {
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <link rel="stylesheet" href="../CSS/styleSesion.css">
            </head>
            <body>
                <dialog id="warning" open>
                    <p id="mensaje" class="success">✅ ¡Cuenta creada exitosamente!</p>
                    <div class="btnModal">
                        <button id="btnAcept" onclick="window.location='../Inicio_sesion.php'">Aceptar</button>
                    </div>
                </dialog>
            </body>
            </html>
            <?php
            exit;
        } else {
            echo "Error en crear cuenta: " . mysqli_error($conexion);
        }
    } else {
        echo "Error en la inserción: " . mysqli_error($conexion);
    }

    mysqli_close($conexion);
?>