<?php
    include("conexionBDD.php");

    $email = $_POST["correoRecupera"];
    $contrasena = $_POST["recuperaContra"];
    $contrasena = hash("sha512", $contrasena);

    $validate_email = mysqli_query($conexion, "SELECT * FROM cuenta WHERE correo='$email'");

    if (mysqli_num_rows($validate_email) > 0) {
        $q = "UPDATE cuenta SET contrasenia='$contrasena' WHERE correo='$email'";
        $result = mysqli_query($conexion, $q);

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
                alert('Error al intentar restablecer la contraseña. Error en la consulta: " . mysqli_error($conexion) . "');
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
