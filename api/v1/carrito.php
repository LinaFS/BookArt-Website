<?php
require_once dirname(__DIR__, 2) . '/core/conexionBDD.php';
require_once dirname(__DIR__, 2) . '/core/services/CarritoService.php';
require_once dirname(__DIR__)    . '/middleware/auth.php';

requireUser();

$method  = $_SERVER['REQUEST_METHOD'];
$action  = trim($_GET['action'] ?? '');
$service = new CarritoService($pdo);           // ← $pdo viene de conexionBDD.php
$uid     = obtenerIdCuenta();

match (true) {
    $method === 'GET'  && $action === 'count'    => count($service, $uid),
    $method === 'GET'                            => items($service, $uid),
    $method === 'POST' && $action === 'checkout' => checkout($service, $uid),
    $method === 'POST' && isset($_POST['opcFinal']) => crearPersonalizada($pdo, $service, $uid),
    $method === 'POST'                           => agregar($service, $uid),
    $method === 'DELETE'                         => eliminar($service, $uid),
    default                                      => response(405, false, 'Método no permitido.')
};

// ── HANDLERS ─────────────────────────────────────────────────────────────────

function items(CarritoService $s, int $uid): void {
    $items = $s->obtenerItems($uid);
    response(200, true, 'OK', ['items' => $items]);
}

function count(CarritoService $s, int $uid): void {
    response(200, true, 'OK', ['count' => $s->contarItems($uid)]);
}

function agregar(CarritoService $s, int $uid): void {
    $tipo = trim($_POST['tipo'] ?? '');
    $id   = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);

    if (!$id || $id <= 0) { response(422, false, 'ID de producto inválido.'); return; }

    $resultado = $s->agregar($uid, $tipo, $id);
    response($resultado['success'] ? 201 : 409, $resultado['success'], $resultado['message']);
}

function eliminar(CarritoService $s, int $uid): void {
    $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
    if (!$id || $id <= 0) { response(422, false, 'ID inválido.'); return; }

    $resultado = $s->eliminar($uid, $id);
    response($resultado['success'] ? 200 : 404, $resultado['success'], $resultado['message']);
}

function checkout(CarritoService $s, int $uid): void {
    $resultado = $s->checkout($uid);
    response($resultado['success'] ? 200 : 422, $resultado['success'], $resultado['message']);
}

function crearPersonalizada(PDO $pdo, CarritoService $s, int $uid): void {
    // Validar campos obligatorios
    $campos = ['opcFinal', 'tamaño', 'tipoPapel', 'color', 'descripcion'];
    $data   = [];
    foreach ($campos as $campo) {
        $val = trim($_POST[$campo] ?? '');
        if (empty($val)) { response(422, false, "El campo '$campo' es obligatorio."); return; }
        $data[$campo] = $val;
    }

    // Procesar imagen si viene
    $portadaUrl = '';
    if (isset($_FILES['portada']) && $_FILES['portada']['error'] === UPLOAD_ERR_OK) {
        $resultado = procesarPortada($_FILES['portada']);
        if (!$resultado['success']) { response(422, false, $resultado['message']); return; }
        $portadaUrl = $resultado['url'];
    }

    // Insertar personalizada
    $stmt = $pdo->prepare(
        "INSERT INTO personalizada (color, descripcion, portada, tam, tipo_encuadernacion, tipo_papel)
         VALUES (:color, :desc, :portada, :tam, :enc, :papel)"
    );
    $stmt->execute([
        ':color'   => $data['color'],
        ':desc'    => $data['descripcion'],
        ':portada' => $portadaUrl,
        ':tam'     => $data['tamaño'],
        ':enc'     => $data['opcFinal'],
        ':papel'   => $data['tipoPapel'],
    ]);
    $idPersonalizada = (int) $pdo->lastInsertId();

    if (!$idPersonalizada) { response(500, false, 'Error al guardar el diseño.'); return; }

    // Agregar al carrito via service (reutiliza lógica de duplicados)
    $resultado = $s->agregar($uid, 'personalizada', $idPersonalizada);
    if (!$resultado['success']) {
        // Revertir si no se pudo agregar
        $pdo->prepare("DELETE FROM personalizada WHERE id_personalizada = ?")->execute([$idPersonalizada]);
        response(500, false, 'Error al agregar al carrito.'); return;
    }

    response(201, true, '¡Diseño creado y agregado al carrito!', ['redirect' => '/carrito']);
}

// ── HELPERS ──────────────────────────────────────────────────────────────────

function obtenerIdCuenta(): int {
    if (!empty($_SESSION['id_cuenta'])) return (int) $_SESSION['id_cuenta'];
    response(401, false, 'Sesión no válida.');
    exit;
}

function procesarPortada(array $file): array {
    if ($file['size'] > 5 * 1024 * 1024)
        return ['success' => false, 'message' => 'La portada no debe superar 5MB.'];

    $permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $permitidos))
        return ['success' => false, 'message' => 'Formato no válido (jpg, png, webp).'];

    $ext    = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nombre = uniqid('portada_') . '.' . $ext;
    $dir    = dirname(__DIR__, 2) . '/wwwroot/portadas/';

    if (!is_dir($dir)) mkdir($dir, 0755, true);
    if (!move_uploaded_file($file['tmp_name'], $dir . $nombre))
        return ['success' => false, 'message' => 'Error al guardar la portada.'];

    return ['success' => true, 'url' => '/wwwroot/portadas/' . $nombre];
}