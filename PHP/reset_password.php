<?php
    require_once __DIR__ . '/conexionBDD.php';
    if (!isset($conexion) || !$conexion) {
        exit('Error de conexión a la base de datos.');
    }

    $email = $_POST["correoRecupera"];
    $contrasena = hash("sha512", $_POST["recuperaContra"]);

    $validate_sql = "SELECT id_cuenta FROM cuenta WHERE correo = ?";
    if ($stmt = mysqli_prepare($conexion, $validate_sql)) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $validate_email = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
    } else {
        exit('Error interno al verificar correo.');
    }

    if ($validate_email && mysqli_num_rows($validate_email) > 0) {
        $q = "UPDATE cuenta SET contrasenia = ? WHERE correo = ?";
        if ($stmt = mysqli_prepare($conexion, $q)) {
            mysqli_stmt_bind_param($stmt, "ss", $contrasena, $email);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            $result = false;
        }

        if ($result) {
            echo "
            <script>
                alert('Contraseña restablecida correctamente.');
                window.location.href = '../Inicio_sesion.php';
            </script>
            ";
            exit;
        } else {
            echo "
            <script>
                alert('Error al intentar restablecer la contraseña.');
                window.location.href = '../Inicio_sesion.php';
            </script>
            ";
            exit;
        }
    } else {
        echo "
        <script>
            window.location.href = '../Inicio_sesion.php';
        </script>
        ";
        exit;
    }
?>
