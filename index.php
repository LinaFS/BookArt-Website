<?php
session_start();
require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/checkLicense.php';
checkLicense();

$uri   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Quitar el prefijo de BASE_URL para que las rutas funcionen en local y producción
$base  = rtrim(BASE_URL, '');
if ($base !== '' && strpos($uri, $base) === 0) {
    $uri = substr($uri, strlen($base));
}

$route = trim($uri, '/');
$route = strtok($route, '?');
if ($route === false) $route = '';

$routes = [
    ''                   => __DIR__ . '/views/home.php',
    'index.php'          => __DIR__ . '/views/home.php',
    'contacto'           => __DIR__ . '/views/user/Contacto.php',

    // Usuario
    'catalogo'           => __DIR__ . '/views/user/Catalogo.php',
    'productos'          => __DIR__ . '/views/user/Productos.php',
    'carrito'            => __DIR__ . '/views/user/Carrito.php',
    'inicio-sesion'      => __DIR__ . '/views/user/Inicio_sesion.php',
    'mis-pedidos'        => __DIR__ . '/views/user/MisPedidos.php',
    'personalizada'      => __DIR__ . '/views/user/Personalizada.php',
    'extension-catalogo' => __DIR__ . '/views/user/Extension_Catalogo.php',
    'editar-pedido'      => __DIR__ . '/views/user/EditarPedido.php',

    // Admin
    'administrador'      => __DIR__ . '/views/admin/Administrador.php',
];

// Admin bloqueado en su panel hasta cerrar sesión
if (isset($_SESSION['permiso']) && (int)$_SESSION['permiso'] === 1) {
    if ($route !== 'administrador') {
        header('Location: ' . BASE_URL . '/administrador');
        exit;
    }
}

if (array_key_exists($route, $routes)) {
    require $routes[$route];
} else {
    http_response_code(404);
    require __DIR__ . '/views/404.php';
}
