<?php
/**
 * COMECyT Control de Solicitudes
 * Vista Publica — Nueva Solicitud
 *
 * Permite a cualquier usuario (sin login) registrar una solicitud.
 * Al enviarse correctamente, muestra el folio asignado.
 * No requiere autenticacion.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$pdo = getConnection();

// Estado del formulario
$exito      = false;
$folioNuevo = '';
$errores    = [];
$datos      = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger y sanitizar entradas
    $datos = [
        'tipo'              => postParam('tipo'),
        'solicitante'       => postParam('solicitante'),
        'email_solicitante' => postParam('email_solicitante'),
        'area'              => postParam('area'),
        'prioridad'         => postParam('prioridad'),
        'descripcion'       => postParam('descripcion'),
    ];

    // Validaciones
    $tiposValidos     = array_keys(ETIQUETAS_TIPO);
    $prioridadesValidas = array_keys(ETIQUETAS_PRIORIDAD);

    if (!in_array($datos['tipo'], $tiposValidos, true)) {
        $errores[] = 'Seleccione un tipo de solicitud valido.';
    }
    if (mb_strlen($datos['solicitante']) < 3) {
        $errores[] = 'El nombre del solicitante debe tener al menos 3 caracteres.';
    }
    if (!empty($datos['email_solicitante']) && !filter_var($datos['email_solicitante'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electronico no es valido.';
    }
    if (mb_strlen($datos['area']) < 2) {
        $errores[] = 'Indique el area o departamento de origen.';
    }
    if (mb_strlen($datos['descripcion']) < 20) {
        $errores[] = 'La descripcion debe tener al menos 20 caracteres.';
    }
    if (!in_array($datos['prioridad'], $prioridadesValidas, true)) {
        $datos['prioridad'] = 'media';
    }

    if (empty($errores)) {
        // Generar folio y persistir
        $folio = generarFolio($pdo);

        $ins = $pdo->prepare(
            "INSERT INTO solicitudes (folio, tipo, solicitante, email_solicitante, area, prioridad, descripcion)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $ins->execute([
            $folio,
            $datos['tipo'],
            $datos['solicitante'],
            $datos['email_solicitante'] ?: '',
            $datos['area'],
            $datos['prioridad'],
            $datos['descripcion'],
        ]);

        $idNuevo = (int) $pdo->lastInsertId();

        // Primer registro en historial
        $pdo->prepare(
            "INSERT INTO historial_solicitudes (solicitud_id, estatus_anterior, estatus_nuevo, comentario)
             VALUES (?, NULL, 'pendiente', 'Solicitud registrada.')"
        )->execute([$idNuevo]);

        $folioNuevo = $folio;
        $exito      = true;
        $datos      = []; // Limpiar formulario
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Registre una solicitud interna en el sistema COMECyT. Atencion, soporte, mantenimiento y administracion.">
    <title>Nueva Solicitud — COMECyT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
</head>
<body class="layout-public">

<!-- Encabezado publico -->
<header class="public-header">
    <div class="public-brand">
        <div class="public-brand-text">
            <span class="brand-name">COMECyT</span>
            <span class="brand-sub">Control de Solicitudes</span>
        </div>
    </div>
    <nav class="public-nav">
        <a href="<?= BASE_URL ?>public/consulta.php" class="btn btn-outline btn-sm">
            <i class="fa-solid fa-magnifying-glass"></i>
            Consultar folio
        </a>
        <a href="<?= BASE_URL ?>admin/login.php" class="btn btn-outline btn-sm">
            <i class="fa-solid fa-lock"></i>
            Administracion
        </a>
    </nav>
</header>

<div class="public-main">

    <?php if ($exito): ?>
    <!-- Pantalla de exito -->
    <div class="card success-screen visible" style="text-align: center; padding: 48px 32px; max-width: 520px; margin: 0 auto;">
        <div style="width: 72px; height: 72px; border-radius: 50%; background: rgba(22,163,74,0.15); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 2rem; color: #4ADE80;">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <h2 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 8px;">Solicitud registrada</h2>
        <p class="text-muted" style="margin-bottom: 24px; font-size: 0.9rem;">
            Su solicitud fue enviada exitosamente. Conserve su folio para dar seguimiento.
        </p>
        <div class="folio-display">
            <div class="folio-label">Numero de Folio</div>
            <div class="folio-value"><?= esc($folioNuevo) ?></div>
        </div>
        <div style="margin-top: 28px; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
            <a href="<?= BASE_URL ?>public/consulta.php?folio=<?= urlencode($folioNuevo) ?>"
               class="btn btn-outline">
                <i class="fa-solid fa-magnifying-glass"></i>
                Rastrear solicitud
            </a>
            <a href="<?= BASE_URL ?>public/index.php" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i>
                Nueva solicitud
            </a>
        </div>
    </div>

    <?php else: ?>
    <!-- Formulario -->
    <div class="public-hero" style="text-align: center; padding: 40px 20px 20px;">
        <img src="<?= BASE_URL ?>assets/MARCA.png" alt="Logo COMECyT" style="max-width: 450px; width: 100%; height: auto; margin: 0 auto 24px; display: block; filter: drop-shadow(0 0 30px rgba(177, 154, 109, 0.15));">
        <h2>Registrar Solicitud</h2>
        <p>Complete el formulario y recibira un folio de seguimiento de inmediato.</p>
    </div>

    <?php if (!empty($errores)): ?>
    <div class="alert alert-error" style="max-width: 820px; margin: 0 auto 20px;">
        <div>
            <i class="fa-solid fa-triangle-exclamation"></i>
            <strong>Corrija los siguientes errores:</strong>
            <ul style="margin-top: 8px; padding-left: 20px;">
                <?php foreach ($errores as $err): ?>
                <li><?= esc($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <form method="POST" action="" novalidate>
        <!-- 1. Tipo de solicitud -->
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-tag"></i>
                    Tipo de Solicitud <span style="color: #F87171; margin-left: 3px;">*</span>
                </h3>
            </div>
            <div class="type-grid">
                <?php
                $tiposConfig = [
                    'mantenimiento' => ['icono' => 'fa-wrench', 'desc' => 'Infraestructura fisica y equipo'],
                    'atencion'      => ['icono' => 'fa-headset', 'desc' => 'Atencion a usuarios'],
                    'soporte'       => ['icono' => 'fa-laptop-code', 'desc' => 'Soporte tecnico o informatico'],
                    'administracion'=> ['icono' => 'fa-folder-open', 'desc' => 'Tramites y procesos administrativos'],
                ];
                foreach ($tiposConfig as $valor => $cfg):
                    $seleccionado = (postParam('tipo') === $valor || ($datos['tipo'] ?? '') === $valor) ? 'selected' : '';
                ?>
                <div class="type-option <?= $seleccionado ?>"
                     data-tipo="<?= $valor ?>"
                     onclick="seleccionarTipo('<?= $valor ?>')">
                    <div class="type-icon">
                        <i class="fa-solid <?= $cfg['icono'] ?>"></i>
                    </div>
                    <span class="type-label"><?= esc(getEtiqueta('tipo', $valor)) ?></span>
                    <span class="type-desc"><?= esc($cfg['desc']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <input type="hidden" name="tipo" id="campoTipo" value="<?= esc($datos['tipo'] ?? '') ?>">
        </div>

        <!-- 2. Datos del solicitante -->
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-user"></i>
                    Datos del Solicitante
                </h3>
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="solicitante">
                        Nombre completo <span class="required">*</span>
                    </label>
                    <input type="text" id="solicitante" name="solicitante" class="form-control"
                           value="<?= esc($datos['solicitante'] ?? '') ?>"
                           placeholder="Nombre del responsable o solicitante"
                           required maxlength="150">
                </div>
                <div class="form-group">
                    <label class="form-label" for="email_solicitante">Correo electronico</label>
                    <input type="email" id="email_solicitante" name="email_solicitante" class="form-control"
                           value="<?= esc($datos['email_solicitante'] ?? '') ?>"
                           placeholder="usuario@comecyt.gob.mx"
                           maxlength="200">
                </div>
                <div class="form-group">
                    <label class="form-label" for="area">
                        Area o departamento <span class="required">*</span>
                    </label>
                    <input type="text" id="area" name="area" class="form-control"
                           value="<?= esc($datos['area'] ?? '') ?>"
                           placeholder="Area de origen de la solicitud"
                           required maxlength="150">
                </div>
                <div class="form-group">
                    <label class="form-label" for="prioridad">Prioridad</label>
                    <select name="prioridad" id="prioridad" class="form-control">
                        <?php foreach (ETIQUETAS_PRIORIDAD as $val => $lbl): ?>
                        <option value="<?= $val ?>" <?= ($datos['prioridad'] ?? 'media') === $val ? 'selected' : '' ?>>
                            <?= esc($lbl) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- 3. Descripcion -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-align-left"></i>
                    Descripcion de la Solicitud
                </h3>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" for="descripcion">
                    Detalle el motivo y lo que se requiere <span class="required">*</span>
                </label>
                <textarea id="descripcion" name="descripcion" class="form-control" rows="5"
                          placeholder="Describa con el mayor detalle posible lo que necesita, el contexto y cualquier informacion relevante para atender su solicitud."
                          required minlength="20"><?= esc($datos['descripcion'] ?? '') ?></textarea>
                <p class="form-hint">Minimo 20 caracteres.</p>
            </div>
        </div>

        <div style="text-align: center;">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fa-solid fa-paper-plane"></i>
                Enviar Solicitud
            </button>
        </div>
    </form>
    <?php endif; ?>

</div><!-- /.public-main -->

<script src="<?= BASE_URL ?>assets/js/app.js"></script>
<script>
/**
 * Selecciona visualmente un tipo de solicitud y actualiza el campo oculto.
 * @param {string} valor
 */
function seleccionarTipo(valor) {
    document.querySelectorAll('.type-option').forEach(el => el.classList.remove('selected'));
    const opcion = document.querySelector(`.type-option[data-tipo="${valor}"]`);
    if (opcion) opcion.classList.add('selected');
    document.getElementById('campoTipo').value = valor;
}
</script>
</body>
</html>
