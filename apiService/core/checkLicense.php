<?php
/**
 * Valida la licencia de BookArt leyendo el caché local.
 * El caché lo actualiza el cron job (cron/validateLicense.php).
 *
 * Si no existe el caché (primera instalación) → permite acceso
 * y registra un warning en log para que el admin ejecute el cron.
 */

define('LICENSE_CACHE_FILE', __DIR__ . '/../../storage/license_cache.json');

function checkLicense(): void {
    if (!file_exists(LICENSE_CACHE_FILE)) {
        // Sin caché: primera instalación o cron aún no corrió.
        // Permite acceso pero deja log de aviso.
        log_license_warning('Caché de licencia no encontrado. Ejecuta el cron job.');
        return;
    }

    $cache = json_decode(file_get_contents(LICENSE_CACHE_FILE), true);

    if (!$cache || !isset($cache['valid'])) {
        log_license_warning('Caché de licencia corrupto.');
        return; // Permite acceso ante duda
    }

    if (!$cache['valid']) {
        showMaintenance($cache['message'] ?? 'Licencia inválida o expirada.');
    }
}

function showMaintenance(string $motivo): void {
    http_response_code(503);
    require __DIR__ . '/../../webService/views/mantenimiento.php';
    exit;
}

function log_license_warning(string $msg): void {
    $dir = __DIR__ . '/../../storage/logs';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $line = '[' . date('Y-m-d H:i:s') . '] WARNING: ' . $msg . PHP_EOL;
    @file_put_contents($dir . '/license.log', $line, FILE_APPEND);
}
