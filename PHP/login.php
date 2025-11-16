<?php
    session_start();
    include("conexionBDD.php");
    $user=$_POST["sesionUsuario"];
    $password=$_POST["sesionContra"];
    $password=hash('sha512', $password);

    $consult="SELECT * FROM cuenta WHERE (correo='$user' or usuario='$user') and contrasenia ='$password'";
    $validate_login = mysqli_query($conexion,$consult);
    if(mysqli_num_rows($validate_login)> 0){
        $row = mysqli_fetch_assoc($validate_login);
        $permiso_id = $row['permiso_id'];
        if($permiso_id=="1"){
            $_SESSION["usuario"] = $user;
            echo"
                <script>
                    window.location='../Administrador.php';
                </script>
            ";
        }else if($permiso_id=="2"){
            $_SESSION["usuario"] = $user;
            echo"
                <script>
                    window.location='../Catalogo.php';
                </script>
            ";
        }
        
    }else{
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