<?php
require_once __DIR__ . '/../../core/Auth.php';
$usuarioActual = Auth::user();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Juan’Style | Sistema de Gestión y Reservas</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Estilos Personalizados Blanco & Dorado -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <span class="fs-3 fw-bold text-dark">JUAN’<span class="text-gold">STYLE</span></span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navJuanStyle">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navJuanStyle">
            <ul class="navbar-nav ms-auto me-3 mb-2 mb-lg-0 align-items-lg-center fw-medium">
                <li class="nav-item"><a class="nav-link text-dark" href="index.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="index.php#servicios">Servicios</a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="index.php#nosotros">Juan González</a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="index.php#galeria">Galería</a></li>
                <li class="nav-item"><a class="nav-link text-dark" href="index.php#contacto">Contacto</a></li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <a href="https://wa.me/573177128368" target="_blank" class="btn btn-outline-success rounded-pill btn-sm px-3">
                    <i class="bi bi-whatsapp"></i> WhatsApp
                </a>

                <?php if ($usuarioActual): ?>
                    <?php if ($usuarioActual['rol'] === 'admin'): ?>
                        <a href="index.php?page=admin_dashboard" class="btn btn-gold rounded-pill btn-sm px-3">
                            <i class="bi bi-speedometer2"></i> Panel Admin
                        </a>
                    <?php else: ?>
                        <a href="index.php?page=mis_reservas" class="btn btn-gold rounded-pill btn-sm px-3">
                            <i class="bi bi-calendar-check"></i> Mis Citas
                        </a>
                    <?php endif; ?>
                    <a href="index.php?page=logout" class="btn btn-light rounded-pill btn-sm px-3 border">
                        Salir
                    </a>
                <?php else: ?>
                    <a href="index.php?page=login" class="btn btn-outline-dark rounded-pill btn-sm px-3">
                        Iniciar Sesión
                    </a>
                    <a href="index.php?page=registro" class="btn btn-gold rounded-pill btn-sm px-3">
                        Registrarse
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>