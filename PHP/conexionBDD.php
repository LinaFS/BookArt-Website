<?php
    $conexion = mysqli_connect("srv1563.hstgr.io","u165852803_user","MRU_7ZhBwCU!","u165852803_bookart");
    if($conexion){
        //echo 'Conectado exitosamente a la base de datos';
    }else{
        echo 'Conexión interrumpida';
    }
?>