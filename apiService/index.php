<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/middleware/cors.php';
require_once __DIR__ . '/core/config.php';

header('Content-Type: application/json; charset=utf-8');

// Leer ruta: /api/auth → "auth", /api/pedidos → "pedidos", etc.
// Soporta BASE_URL local (ej: /BookArt/api/auth → "auth")
$uri   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base  = rtrim(BASE_URL, '/');
$route = trim(preg_replace('#^' . preg_quote($base, '#') . '/?api/?#', '', $uri), '/');
$route = strtok($route, '?');

$routes = [
    'auth'     => dirname(__DIR__) . '/webService/actions/auth.php',
    'catalogo' => dirname(__DIR__) . '/webService/actions/catalogo.php',
    'carrito'  => dirname(__DIR__) . '/webService/actions/carrito.php',
    'pedidos'  => dirname(__DIR__) . '/webService/actions/pedidos.php',
    'contacto' => dirname(__DIR__) . '/webService/actions/contacto.php',
];

if (array_key_exists($route, $routes)) {
    require $routes[$route];
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => "Ruta '$route' no encontrada."]);
}

function response(int $code, bool $success, string $message, array $extra = []): void {
    http_response_code($code);
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}
