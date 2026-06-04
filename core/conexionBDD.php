<?php
require_once __DIR__ . '/env.php';

/**
 * Singleton PDO para BookArt.
 * Reemplaza mysqli_* — misma variable $pdo disponible globalmente.
 *
 * Uso en cualquier archivo:
 *   require_once __DIR__ . '/../core/conexionBDD.php';
 *   // $pdo ya está disponible
 */

define('DB_HOST',     $_ENV['DB_HOST']);
define('DB_USER',     $_ENV['DB_USER']);
define('DB_PASSWORD', $_ENV['DB_PASSWORD']);
define('DB_NAME',     $_ENV['DB_NAME']);

class ConexionBDD {
    private static ?PDO $instancia = null;

    public static function obtener(): PDO {
        if (self::$instancia === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            try {
                self::$instancia = new PDO($dsn, DB_USER, DB_PASSWORD, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                error_log('BookArt DB Error: ' . $e->getMessage());
                http_response_code(500);
                exit('Error de conexión a la base de datos.');
            }
        }
        return self::$instancia;
    }
}

// Alias global — todos los archivos usan $pdo directamente
$pdo = ConexionBDD::obtener();