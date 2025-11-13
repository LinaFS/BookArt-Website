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
        echo"
            <script>
                alert('Error al iniciar sesión, verifique sus datos');
                window.location='../Inicio_sesion.php';
            </script>
        ";
    }
?>