<?php
/**
 * COMECyT Control de Solicitudes
 * Cabecera HTML del panel de administracion
 *
 * Uso: require_once ROOT . '/includes/header_admin.php';
 * Variables esperadas (definir antes de incluir):
 *   $pageTitle   string  Titulo de la pagina (para <title> y breadcrumb)
 *   $activeMenu  string  Clave del menu activo: 'dashboard'|'solicitudes'|'pendientes'|'en_proceso'|'completadas'
 */

if (!isset($pageTitle))  $pageTitle  = 'Panel de Administracion';
if (!isset($activeMenu)) $activeMenu = '';

$adminNombre = getNombreAdmin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= esc($pageTitle) ?> — COMECyT Control de Solicitudes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
</head>
<body class="layout-admin">

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-logo">
            <img src="<?= BASE_URL ?>assets/MARCA.png" alt="Logo COMECyT">
        </div>
    </div>

    <nav class="sidebar-nav" aria-label="Menu principal">
        <div class="nav-group">
            <span class="nav-group-label">Principal</span>
            <a href="<?= BASE_URL ?>admin/dashboard.php"
               class="nav-link <?= $activeMenu === 'dashboard' ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-pie nav-icon"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <div class="nav-group">
            <span class="nav-group-label">Gestion</span>
            <a href="<?= BASE_URL ?>admin/solicitudes.php"
               class="nav-link <?= $activeMenu === 'solicitudes' ? 'active' : '' ?>">
                <i class="fa-solid fa-list-ul nav-icon"></i>
                <span>Todas las Solicitudes</span>
            </a>
            <a href="<?= BASE_URL ?>admin/solicitudes.php?estatus=pendiente"
               class="nav-link <?= $activeMenu === 'pendientes' ? 'active' : '' ?>">
                <i class="fa-solid fa-clock nav-icon"></i>
                <span>Pendientes</span>
            </a>
            <a href="<?= BASE_URL ?>admin/solicitudes.php?estatus=en_proceso"
               class="nav-link <?= $activeMenu === 'en_proceso' ? 'active' : '' ?>">
                <i class="fa-solid fa-bolt nav-icon"></i>
                <span>En Proceso</span>
            </a>
            <a href="<?= BASE_URL ?>admin/solicitudes.php?estatus=completada"
               class="nav-link <?= $activeMenu === 'completadas' ? 'active' : '' ?>">
                <i class="fa-solid fa-circle-check nav-icon"></i>
                <span>Completadas</span>
            </a>
            <a href="<?= BASE_URL ?>admin/administradores.php"
               class="nav-link <?= $activeMenu === 'administradores' ? 'active' : '' ?>">
                <i class="fa-solid fa-users-gear nav-icon"></i>
                <span>Administradores</span>
            </a>
        </div>

        <div class="nav-group">
            <span class="nav-group-label">Acceso Publico</span>
            <a href="<?= BASE_URL ?>public/index.php" target="_blank" class="nav-link">
                <i class="fa-solid fa-arrow-up-right-from-square nav-icon"></i>
                <span>Vista Ciudadana</span>
            </a>
        </div>
    </nav>

    <div class="sidebar-footer">
        <div class="admin-info">
            <div class="admin-avatar">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <div class="admin-details">
                <span class="admin-name"><?= esc($adminNombre) ?></span>
                <span class="admin-role">Administrador</span>
            </div>
        </div>
        <a href="<?= BASE_URL ?>admin/logout.php" class="btn-logout" title="Cerrar sesion">
            <i class="fa-solid fa-right-from-bracket"></i>
        </a>
    </div>
</aside>

<!-- Overlay para sidebar movil -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Contenido principal -->
<div class="main-wrapper">
    <!-- Topbar -->
    <header class="topbar">
        <button class="menu-toggle" id="menuToggle" aria-label="Abrir menu" aria-expanded="false">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="topbar-title">
            <h1><?= esc($pageTitle) ?></h1>
        </div>
        <div class="topbar-actions">
            <a href="<?= BASE_URL ?>admin/solicitudes.php?estatus=pendiente" class="topbar-btn" title="Solicitudes pendientes">
                <i class="fa-solid fa-bell"></i>
            </a>
            <a href="<?= BASE_URL ?>public/index.php" target="_blank" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus"></i>
                <span>Nueva Solicitud</span>
            </a>
        </div>
    </header>

    <!-- Contenido de la pagina -->
    <main class="page-content">
