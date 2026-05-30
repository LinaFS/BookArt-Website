<?php
    define('DB_HOST', 'localhost');
    define('DB_USER', 'u165852803_user');
    define('DB_PASSWORD', 'MRU_7ZhBwCU!');
    define('DB_NAME', 'bu165852803_bookart');

    /*define('DB_HOST', 'srv1563.hstgr.io');
    define('DB_USER', 'u165852803_user');
    define('DB_PASSWORD', 'MRU_7ZhBwCU!');
    define('DB_NAME', 'u165852803_bookart');*/

    $conexion = mysqli_init();
    mysqli_options($conexion, MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);

    if (!mysqli_real_connect($conexion, DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)) {
        error_log('MySQL connection error: ' . mysqli_connect_error());
        http_response_code(500);
        exit('Error de conexión a la base de datos.');
    }

    if (!mysqli_set_charset($conexion, 'utf8mb4')) {
        error_log('Error al establecer el conjunto de caracteres: ' . mysqli_error($conexion));
        http_response_code(500);
        exit('Error de configuración de la base de datos.');
    }
?>