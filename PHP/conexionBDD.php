<?php
    $conexion = mysqli_connect("localhost","root","MRU_7ZhBwCU!","bookart");
    if($conexion){
        //echo 'Conectado exitosamente a la base de datos';
    }else{
        echo 'Conexión interrumpida';
    }
?>