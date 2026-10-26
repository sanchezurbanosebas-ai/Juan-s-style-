<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- Google Fonts para Tipografía de Marca -->
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
    .brand-font {
        font-family: 'Bebas Neue', cursive;
        letter-spacing: 2px;
        font-size: 2rem;
        color: #d4af37 !important;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top border-bottom border-warning">
    <div class="container">
        <a class="navbar-brand brand-font" href="index.php">
            <i class="bi bi-scissors me-2"></i>JUAN STYLE
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link text-light" href="index.php">Inicio</a>
                </li>
                <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'cliente'): ?>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="agendar.php">Agendar Cita</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="mis_citas.php">Mis Citas</a>
                    </li>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-center">
                <?php if (isset($_SESSION['nombre'])): ?>
                    <span class="text-white me-3">👤 <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light me-2">Iniciar Sesión</a>
                    <a href="registro.php" class="btn btn-warning fw-bold">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>