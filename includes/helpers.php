<?php
/**
 * COMECyT Control de Solicitudes
 * Funciones utilitarias compartidas
 *
 * Responsabilidad: Proporcionar helpers de presentacion reutilizables
 * en todas las vistas PHP del sistema.
 */

// -----------------------------------------------------------------------
// URL base del sistema — ajustar si se instala en subdirectorio
// Ejemplo: define('BASE_URL', 'http://localhost/COMECyT_ControlSolicitudes/');
// -----------------------------------------------------------------------
if (!defined('BASE_URL')) {
    $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host      = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script    = $_SERVER['SCRIPT_NAME'] ?? '';
    // Detectar directorio raiz del proyecto
    $raiz      = rtrim(dirname(dirname($script)), '/') . '/';
    define('BASE_URL', $protocolo . '://' . $host . $raiz);
}

// -----------------------------------------------------------------------
// Etiquetas legibles en espanol
// -----------------------------------------------------------------------

/** @var array<string, string> Etiquetas para tipos de solicitud */
const ETIQUETAS_TIPO = [
    'mantenimiento' => 'Mantenimiento',
    'atencion'      => 'Atencion',
    'soporte'       => 'Soporte',
    'administracion'=> 'Administracion',
];

/** @var array<string, string> Etiquetas para estatus */
const ETIQUETAS_ESTATUS = [
    'pendiente'  => 'Pendiente',
    'en_proceso' => 'En Proceso',
    'completada' => 'Completada',
    'cancelada'  => 'Cancelada',
];

/** @var array<string, string> Etiquetas para prioridad */
const ETIQUETAS_PRIORIDAD = [
    'baja'    => 'Baja',
    'media'   => 'Media',
    'alta'    => 'Alta',
    'urgente' => 'Urgente',
];

// -----------------------------------------------------------------------
// Clases CSS para badges (definidas en main.css)
// -----------------------------------------------------------------------

/**
 * Obtener la clase CSS del badge segun tipo de campo y su valor.
 *
 * @param  string $campo  'tipo' | 'estatus' | 'prioridad'
 * @param  string $valor
 * @return string Clase CSS.
 */
function getBadgeClase(string $campo, string $valor): string
{
    $mapa = [
        'tipo' => [
            'mantenimiento' => 'badge-mantenimiento',
            'atencion'      => 'badge-atencion',
            'soporte'       => 'badge-soporte',
            'administracion'=> 'badge-administracion',
        ],
        'estatus' => [
            'pendiente'  => 'badge-pendiente',
            'en_proceso' => 'badge-en-proceso',
            'completada' => 'badge-completada',
            'cancelada'  => 'badge-cancelada',
        ],
        'prioridad' => [
            'baja'    => 'badge-prioridad-baja',
            'media'   => 'badge-prioridad-media',
            'alta'    => 'badge-prioridad-alta',
            'urgente' => 'badge-prioridad-urgente',
        ],
    ];

    return $mapa[$campo][$valor] ?? 'badge-default';
}

/**
 * Obtener la etiqueta legible de un campo.
 *
 * @param  string $campo  'tipo' | 'estatus' | 'prioridad'
 * @param  string $valor
 * @return string Etiqueta en espanol.
 */
function getEtiqueta(string $campo, string $valor): string
{
    $mapas = [
        'tipo'      => ETIQUETAS_TIPO,
        'estatus'   => ETIQUETAS_ESTATUS,
        'prioridad' => ETIQUETAS_PRIORIDAD,
    ];

    return $mapas[$campo][$valor] ?? ucfirst($valor);
}

/**
 * Obtener la clase de icono Font Awesome segun el tipo de solicitud.
 *
 * @param  string $tipo
 * @return string Clase FA.
 */
function getIconoTipo(string $tipo): string
{
    $iconos = [
        'mantenimiento' => 'fa-wrench',
        'atencion'      => 'fa-headset',
        'soporte'       => 'fa-laptop-code',
        'administracion'=> 'fa-folder-open',
    ];

    return 'fa-solid ' . ($iconos[$tipo] ?? 'fa-file');
}

/**
 * Obtener la clase de icono Font Awesome segun el estatus.
 *
 * @param  string $estatus
 * @return string Clase FA.
 */
function getIconoEstatus(string $estatus): string
{
    $iconos = [
        'pendiente'  => 'fa-solid fa-clock',
        'en_proceso' => 'fa-solid fa-bolt',
        'completada' => 'fa-solid fa-circle-check',
        'cancelada'  => 'fa-solid fa-ban',
    ];

    return $iconos[$estatus] ?? 'fa-solid fa-circle';
}

/**
 * Formatear una fecha de MySQL al formato legible d/m/Y H:i.
 *
 * @param  string|null $fecha Fecha en formato MySQL (Y-m-d H:i:s).
 * @return string Fecha formateada o guion si es nula.
 */
function formatearFecha(?string $fecha): string
{
    if (empty($fecha)) {
        return '—';
    }

    try {
        $dt = new DateTime($fecha);
        return $dt->format('d/m/Y H:i');
    } catch (Exception $e) {
        return $fecha;
    }
}

/**
 * Sanitizar y escapar cadena para salida segura en HTML.
 *
 * @param  mixed $valor
 * @return string
 */
function esc(mixed $valor): string
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Obtener el valor de $_GET de forma segura.
 *
 * @param  string $clave
 * @param  string $defecto Valor por defecto.
 * @return string
 */
function getParam(string $clave, string $defecto = ''): string
{
    return trim($_GET[$clave] ?? $defecto);
}

/**
 * Obtener el valor de $_POST de forma segura.
 *
 * @param  string $clave
 * @param  string $defecto Valor por defecto.
 * @return string
 */
function postParam(string $clave, string $defecto = ''): string
{
    return trim($_POST[$clave] ?? $defecto);
}

/**
 * Redirigir a una URL relativa a BASE_URL y detener la ejecucion.
 *
 * @param string $rutaRelativa Por ejemplo: 'admin/dashboard.php'
 */
function redirigir(string $rutaRelativa): never
{
    header('Location: ' . BASE_URL . ltrim($rutaRelativa, '/'));
    exit;
}
