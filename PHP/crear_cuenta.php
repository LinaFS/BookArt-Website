<?php
    include 'conexionBDD.php';
    //creando al usuario
    $nombre= $_POST['nombre'];
    $paterno= $_POST['paterno'];
    $materno= $_POST['materno'];
    $tel= $_POST['tel'];
    //para fecha de nacimiento:
    $dia= $_POST['Dia'];
    $mes= $_POST['Mes'];
    $anio= $_POST['anio'];
    //completando para la creación de la cuenta
    $usuario= $_POST['usuario'];
    $correo= $_POST['correo'];
    $contra= $_POST['contrasena'];
    $permiso=2;
    //encriptar contraseña
    $contra=hash("sha512", $contra);

    // Insertar datos del usuario en la tabla 'usuario'
    $query_usuario = "INSERT INTO usuario(nombre, paterno, materno, tel, dia, mes, anio)
            VALUES('$nombre','$paterno','$materno','$tel','$dia','$mes','$anio')";

    $execute_usuario = mysqli_query($conexion, $query_usuario);
    $idusuario = mysqli_insert_id($conexion);

    if ($execute_usuario) {
        echo "Creando cuenta... Último ID insertado: " . $idusuario;
        $query_cuenta = "INSERT INTO cuenta(usuario, correo, contrasenia, permiso_id, usuario_id)
            VALUES('$usuario','$correo','$contra','$permiso','$idusuario')";

        // Verificar si el correo electrónico ya está registrado
        $verify_email = mysqli_query($conexion, "SELECT * FROM cuenta WHERE correo='$correo'");
        if(mysqli_num_rows($verify_email) > 0){
            $drop = "DELETE FROM usuario WHERE id_usuario='$idusuario'";
            mysqli_query($conexion, $drop);
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

        // Verificar si el nombre de usuario ya está registrado
        $verify_user = mysqli_query($conexion, "SELECT * FROM cuenta WHERE usuario= '$usuario'");
        if(mysqli_num_rows($verify_user) > 0){
            $drop = "DELETE FROM usuario WHERE id_usuario='$idusuario'";
            mysqli_query($conexion, $drop);
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

        // Insertar datos de la cuenta en la tabla 'cuenta'
        $execute_cuenta = mysqli_query($conexion, $query_cuenta);
        if($execute_cuenta){
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