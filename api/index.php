<?php
session_start();

require_once __DIR__ . '/middleware/cors.php';
require_once dirname(__DIR__) . '/core/config.php';

header('Content-Type: application/json; charset=utf-8');

// Leer ruta: /api/auth → "auth", /api/pedidos → "pedidos", etc.
// Soporta BASE_URL local (ej: /BookArt/api/auth → "auth")
$uri   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base  = rtrim(BASE_URL, '/');
$route = trim(preg_replace('#^' . preg_quote($base, '#') . '/?api/?#', '', $uri), '/');
$route = strtok($route, '?');

$routes = [
    'auth'     => __DIR__ . '/v1/auth.php',
    'catalogo' => __DIR__ . '/v1/catalogo.php',
    'carrito'  => __DIR__ . '/v1/carrito.php',
    'pedidos'  => __DIR__ . '/v1/pedidos.php',
    'contacto' => __DIR__ . '/v1/contacto.php',
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
