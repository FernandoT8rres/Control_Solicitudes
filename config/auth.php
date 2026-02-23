<?php
/**
 * COMECyT Control de Solicitudes
 * Gestion de Autenticacion de Administradores
 *
 * Responsabilidad: Iniciar y validar sesiones del panel de administracion.
 * Todas las paginas del directorio admin/ deben llamar a verificarSesionAdmin()
 * al inicio del script para asegurar acceso controlado.
 */

require_once __DIR__ . '/database.php';

// Duracion maxima de sesion inactiva (segundos)
define('SESSION_TIMEOUT', 7200); // 2 horas

/**
 * Iniciar la sesion PHP de forma segura si aun no esta activa.
 */
function inicializarSesion(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_httponly' => true,
            'cookie_secure'   => false, // Cambiar a true en produccion con HTTPS
            'use_strict_mode' => true,
        ]);
    }
}

/**
 * Verificar que el administrador tenga sesion activa y no expirada.
 * Si no, redirige al login y detiene la ejecucion.
 */
function verificarSesionAdmin(): void
{
    inicializarSesion();

    // Sin sesion registrada
    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . BASE_URL . 'admin/login.php');
        exit;
    }

    // Comprobar tiempo de inactividad
    if (!empty($_SESSION['ultimo_acceso'])) {
        $inactivo = time() - (int) $_SESSION['ultimo_acceso'];
        if ($inactivo > SESSION_TIMEOUT) {
            cerrarSesion();
            header('Location: ' . BASE_URL . 'admin/login.php?motivo=timeout');
            exit;
        }
    }

    // Renovar marca de tiempo de actividad
    $_SESSION['ultimo_acceso'] = time();
}

/**
 * Intentar iniciar sesion con las credenciales proporcionadas.
 *
 * @param  string $email
 * @param  string $password
 * @return bool   true si las credenciales son correctas y el admin esta activo.
 */
function iniciarSesion(string $email, string $password): bool
{
    inicializarSesion();

    $pdo  = getConnection();
    $stmt = $pdo->prepare(
        "SELECT id, nombre, email, password_hash
         FROM administradores
         WHERE email = :email AND activo = 1
         LIMIT 1"
    );
    $stmt->execute([':email' => trim($email)]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        return false;
    }

    // Registrar el ultimo acceso en BD
    $upd = $pdo->prepare(
        "UPDATE administradores SET ultimo_login = NOW() WHERE id = :id"
    );
    $upd->execute([':id' => $admin['id']]);

    // Regenerar ID de sesion para prevenir session fixation
    session_regenerate_id(true);

    $_SESSION['admin_id']      = $admin['id'];
    $_SESSION['admin_nombre']  = $admin['nombre'];
    $_SESSION['admin_email']   = $admin['email'];
    $_SESSION['ultimo_acceso'] = time();

    return true;
}

/**
 * Cerrar la sesion del administrador y limpiar la cookie.
 */
function cerrarSesion(): void
{
    inicializarSesion();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

/**
 * Obtener el nombre del administrador en sesion.
 *
 * @return string
 */
function getNombreAdmin(): string
{
    inicializarSesion();
    return $_SESSION['admin_nombre'] ?? 'Administrador';
}
