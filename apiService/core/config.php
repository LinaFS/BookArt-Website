<?php
require_once __DIR__ . '/env.php';

define('KEY_TOKEN', $_ENV['KEY_TOKEN']);

// Base URL: define en .env solo si la app está en subcarpeta
// Ejemplo local subcarpeta: BASE_URL=/BookArt
// En producción (raíz del dominio): dejar vacío o no definir
define('BASE_URL', $_ENV['BASE_URL'] ?? '');
