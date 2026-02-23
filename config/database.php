<?php
/**
 * COMECyT Control de Solicitudes
 * Configuracion de Base de Datos
 *
 * Responsabilidad: Proporcionar la conexion PDO singleton y utilerias de BD.
 * Modificar las constantes segun el entorno de despliegue.
 */

// -------------------------------------------------------
// Constantes de conexion — ajustar segun entorno
// -------------------------------------------------------
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '8889');
define('DB_NAME', 'comecyt_solicitudes');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_CHARSET', 'utf8mb4');

// Prefijo de folio institucional
define('FOLIO_PREFIX', 'CMCT');

/**
 * Obtener la conexion PDO (patron Singleton).
 *
 * @return PDO Instancia de conexion a la base de datos.
 */
function getConnection(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_CHARSET
    );

    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $opciones);
    } catch (PDOException $e) {
        // En produccion, loguear el error en lugar de mostrarlo
        error_log('[COMECyT] Error de BD: ' . $e->getMessage());
        http_response_code(500);
        die('Error de conexion a la base de datos. Contacte al administrador.');
    }

    return $pdo;
}

/**
 * Generar un folio unico con formato CMCT-YYYY-NNNN.
 *
 * @param  PDO    $pdo
 * @return string Folio generado.
 */
function generarFolio(PDO $pdo): string
{
    $anio   = date('Y');
    $prefijo = FOLIO_PREFIX . '-' . $anio . '-';

    $stmt = $pdo->prepare(
        "SELECT folio FROM solicitudes
         WHERE folio LIKE :prefijo
         ORDER BY id DESC
         LIMIT 1"
    );
    $stmt->execute([':prefijo' => $prefijo . '%']);
    $ultimo = $stmt->fetchColumn();

    if ($ultimo) {
        $numero = (int) substr($ultimo, strlen($prefijo)) + 1;
    } else {
        $numero = 1;
    }

    return $prefijo . str_pad($numero, 4, '0', STR_PAD_LEFT);
}
