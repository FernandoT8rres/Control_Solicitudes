<?php
/**
 * COMECyT Control de Solicitudes
 * Panel de Administracion — Detalle de Solicitud
 *
 * Muestra toda la informacion de una solicitud individual junto
 * con su historial de cambios de estatus en formato timeline.
 * Permite cambiar el estatus directamente desde esta vista.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/auth.php';

verificarSesionAdmin();

$pdo = getConnection();

$id = (int) getParam('id');
if ($id <= 0) {
    redirigir('admin/solicitudes.php');
}

// -------------------------------------------------------
// Procesar cambio de estatus (POST -> Redirect -> GET)
// -------------------------------------------------------
$mensajeFlash = '';
$tipoFlash    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && postParam('_accion') === 'cambiar_estatus') {
    $estatusNuevo = postParam('estatus_nuevo');
    $comentario   = postParam('comentario');
    $estatusValidos = ['pendiente', 'en_proceso', 'completada', 'cancelada'];

    if (in_array($estatusNuevo, $estatusValidos, true)) {
        $stmt = $pdo->prepare("SELECT estatus FROM solicitudes WHERE id = ?");
        $stmt->execute([$id]);
        $actual = $stmt->fetchColumn();

        if ($actual !== false) {
            $nombreAdmin = getNombreAdmin();

            if ($estatusNuevo === 'completada' && $actual !== 'completada') {
                $upd = $pdo->prepare("UPDATE solicitudes SET estatus = ?, resuelto_por = ? WHERE id = ?");
                $upd->execute([$estatusNuevo, $nombreAdmin, $id]);
            } else {
                $upd = $pdo->prepare("UPDATE solicitudes SET estatus = ? WHERE id = ?");
                $upd->execute([$estatusNuevo, $id]);
            }

            $ins = $pdo->prepare(
                "INSERT INTO historial_solicitudes (solicitud_id, estatus_anterior, estatus_nuevo, comentario, usuario_nombre)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $ins->execute([$id, $actual, $estatusNuevo, $comentario ?: null, $nombreAdmin]);

            header('Location: ' . BASE_URL . 'admin/detalle.php?id=' . $id . '&flash=ok');
            exit;
        }
    }

    $mensajeFlash = 'No se pudo actualizar el estatus.';
    $tipoFlash    = 'error';
}

if (getParam('flash') === 'ok') {
    $mensajeFlash = 'Estatus actualizado correctamente.';
    $tipoFlash    = 'success';
}

// -------------------------------------------------------
// Cargar solicitud
// -------------------------------------------------------
$stmt = $pdo->prepare("SELECT * FROM solicitudes WHERE id = ?");
$stmt->execute([$id]);
$sol = $stmt->fetch();

if (!$sol) {
    redirigir('admin/solicitudes.php');
}

// Cargar historial cronologico
$stmtH = $pdo->prepare(
    "SELECT * FROM historial_solicitudes
     WHERE solicitud_id = ?
     ORDER BY fecha_cambio ASC"
);
$stmtH->execute([$id]);
$historial = $stmtH->fetchAll();

// Transiciones validas
$transiciones = [
    'pendiente'  => ['en_proceso', 'cancelada'],
    'en_proceso' => ['completada', 'cancelada'],
    'completada' => [],
    'cancelada'  => [],
];
$opcionesEstatus = $transiciones[$sol['estatus']] ?? [];

// -------------------------------------------------------
// Vista
// -------------------------------------------------------
$pageTitle  = 'Detalle — ' . $sol['folio'];
$activeMenu = 'solicitudes';

require_once __DIR__ . '/../includes/header_admin.php';
?>

<a href="<?= BASE_URL ?>admin/solicitudes.php" class="back-link">
    <i class="fa-solid fa-arrow-left"></i>
    Volver al listado
</a>

<?php if ($mensajeFlash): ?>
<div class="alert alert-<?= esc($tipoFlash) ?>" data-auto-close="4000">
    <i class="fa-solid <?= $tipoFlash === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>"></i>
    <?= esc($mensajeFlash) ?>
</div>
<?php endif; ?>

<!-- Encabezado de la solicitud -->
<div class="card mb-16" style="margin-bottom: 20px;">
    <div class="d-flex align-center justify-between gap-16" style="flex-wrap: wrap; gap: 12px;">
        <div>
            <div class="folio-link" style="font-size: 1.2rem; margin-bottom: 8px;">
                <?= esc($sol['folio']) ?>
            </div>
            <div class="d-flex align-center gap-8" style="flex-wrap: wrap; gap: 8px;">
                <span class="badge <?= getBadgeClase('tipo', $sol['tipo']) ?>">
                    <i class="<?= getIconoTipo($sol['tipo']) ?>"></i>
                    <?= esc(getEtiqueta('tipo', $sol['tipo'])) ?>
                </span>
                <span class="badge <?= getBadgeClase('estatus', $sol['estatus']) ?>">
                    <i class="<?= getIconoEstatus($sol['estatus']) ?>"></i>
                    <?= esc(getEtiqueta('estatus', $sol['estatus'])) ?>
                </span>
                <span class="badge <?= getBadgeClase('prioridad', $sol['prioridad']) ?>">
                    <?= esc(getEtiqueta('prioridad', $sol['prioridad'])) ?>
                </span>
            </div>
        </div>
        <?php if (!empty($opcionesEstatus)): ?>
        <button type="button" class="btn btn-primary" onclick="abrirModal('modalEstatus')">
            <i class="fa-solid fa-pen-to-square"></i>
            Cambiar Estatus
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Layout dos columnas -->
<div class="detail-layout">

    <!-- Columna izquierda: Datos -->
    <div>
        <div class="card mb-16" style="margin-bottom: 20px;">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fa-solid fa-circle-info"></i>
                    Informacion General
                </h2>
            </div>
            <div class="detail-grid">
                <div class="detail-field">
                    <div class="detail-field-label">Solicitante</div>
                    <div class="detail-field-value"><?= esc($sol['solicitante']) ?></div>
                </div>
                <div class="detail-field">
                    <div class="detail-field-label">Area</div>
                    <div class="detail-field-value"><?= esc($sol['area']) ?></div>
                </div>
                <div class="detail-field">
                    <div class="detail-field-label">Correo electronico</div>
                    <div class="detail-field-value">
                        <?= $sol['email_solicitante']
                            ? '<a href="mailto:' . esc($sol['email_solicitante']) . '">' . esc($sol['email_solicitante']) . '</a>'
                            : '<span class="text-muted">—</span>' ?>
                    </div>
                </div>
                <div class="detail-field">
                    <div class="detail-field-label">Fecha de registro</div>
                    <div class="detail-field-value"><?= formatearFecha($sol['fecha_creacion']) ?></div>
                </div>
                <div class="detail-field">
                    <div class="detail-field-label">Ultima actualizacion</div>
                    <div class="detail-field-value"><?= formatearFecha($sol['fecha_actualizacion']) ?></div>
                </div>
                <?php if ($sol['estatus'] === 'completada' && $sol['resuelto_por']): ?>
                <div class="detail-field" style="grid-column: 1 / -1; background: rgba(22, 163, 74, 0.05); padding: 12px; border-radius: var(--radius-sm); border: 1px solid rgba(22, 163, 74, 0.2); margin-top: 8px;">
                    <div class="detail-field-label" style="color: var(--color-completada);">
                        <i class="fa-solid fa-check-double"></i> Atendido y cerrado por
                    </div>
                    <div class="detail-field-value" style="font-weight: 600;">
                        <?= esc($sol['resuelto_por']) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="card-header" style="margin-top: 16px;">
                <h3 class="card-title">
                    <i class="fa-solid fa-align-left"></i>
                    Descripcion
                </h3>
            </div>
            <div class="detail-description">
                <?= nl2br(esc($sol['descripcion'])) ?>
            </div>
        </div>
    </div>

    <!-- Columna derecha: Historial -->
    <div>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fa-solid fa-timeline"></i>
                    Historial de Cambios
                </h2>
                <span class="text-muted fs-sm"><?= count($historial) ?> evento<?= count($historial) !== 1 ? 's' : '' ?></span>
            </div>

            <?php if (!empty($historial)): ?>
            <div class="timeline">
                <?php foreach ($historial as $evento): ?>
                <?php
                    $claseEst = str_replace('_', '-', $evento['estatus_nuevo']);
                ?>
                <div class="timeline-item">
                    <div class="timeline-dot dot-<?= esc($claseEst) ?>">
                        <i class="<?= getIconoEstatus($evento['estatus_nuevo']) ?>"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <div class="d-flex align-center gap-8">
                                <?php if ($evento['estatus_anterior']): ?>
                                <span class="badge <?= getBadgeClase('estatus', $evento['estatus_anterior']) ?>" style="font-size: 0.65rem; padding: 1px 7px;">
                                    <?= esc(getEtiqueta('estatus', $evento['estatus_anterior'])) ?>
                                </span>
                                <i class="fa-solid fa-arrow-right text-muted" style="font-size:0.7rem;"></i>
                                <?php endif; ?>
                                <span class="badge <?= getBadgeClase('estatus', $evento['estatus_nuevo']) ?>" style="font-size: 0.65rem; padding: 1px 7px;">
                                    <?= esc(getEtiqueta('estatus', $evento['estatus_nuevo'])) ?>
                                </span>
                            </div>
                            <span class="timeline-date"><?= formatearFecha($evento['fecha_cambio']) ?></span>
                        </div>
                        <?php if ($evento['usuario_nombre']): ?>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 4px;">
                            <i class="fa-solid fa-user"></i> Por: <strong><?= esc($evento['usuario_nombre']) ?></strong>
                        </div>
                        <?php endif; ?>
                        <?php if ($evento['comentario']): ?>
                        <p class="timeline-comment"><?= nl2br(esc($evento['comentario'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state" style="padding: 30px 20px;">
                <i class="fa-solid fa-clock"></i>
                <p>Sin historial registrado.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal: Cambiar Estatus -->
<?php if (!empty($opcionesEstatus)): ?>
<div class="modal-backdrop" id="modalEstatus">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fa-solid fa-pen-to-square"></i>
                Cambiar Estatus
            </h3>
            <button type="button" class="modal-close" onclick="cerrarModal('modalEstatus')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="_accion" value="cambiar_estatus">
            <div class="modal-body">
                <p class="text-muted fs-sm mb-16">
                    Solicitud: <strong class="text-accent"><?= esc($sol['folio']) ?></strong>
                </p>
                <div class="form-group">
                    <label class="form-label" for="estatus_nuevo">
                        Nuevo estatus <span class="required">*</span>
                    </label>
                    <select name="estatus_nuevo" id="estatus_nuevo" class="form-control" required>
                        <option value="">Seleccionar...</option>
                        <?php foreach ($opcionesEstatus as $opcion): ?>
                        <option value="<?= esc($opcion) ?>">
                            <?= esc(getEtiqueta('estatus', $opcion)) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="comentario">Comentario</label>
                    <textarea name="comentario" id="comentario" class="form-control" rows="3"
                              placeholder="Descripcion de la accion realizada..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="cerrarModal('modalEstatus')">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Guardar cambio
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
