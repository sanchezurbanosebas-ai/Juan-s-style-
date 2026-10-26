<?php
session_start();
require_once 'conexion.php';

// Consultas a la base de datos
$sql_servicios = "SELECT * FROM servicios";
$resultado_servicios = $conn->query($sql_servicios);

$sql_productos = "SELECT * FROM productos";
$resultado_productos = $conn->query($sql_productos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JUAN STYLE | VIP Executive Barbering</title>
    
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Tipografía Luxury: Cormorant Garamond (Titulares VIP) + Montserrat (Lectura) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-luxury: #faf8f5;
            --surface-white: #ffffff;
            --gold-accent: #c5a059;
            --gold-dark: #a37f37;
            --gold-gradient: linear-gradient(135deg, #d4af37 0%, #b88d22 100%);
            --text-dark: #1c1917;
            --text-muted: #78716c;
            --border-subtle: rgba(197, 160, 89, 0.25);
        }

        body {
            background-color: var(--bg-luxury);
            color: var(--text-dark);
            font-family: 'Montserrat', sans-serif;
            overflow-x: hidden;
        }

        .font-serif {
            font-family: 'Cormorant Garamond', serif;
            letter-spacing: 1px;
        }

        /* Navbar Glassmorphism Claro */
        .navbar-custom {
            background: rgba(250, 248, 245, 0.9);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-subtle);
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-size: 2.2rem;
            color: var(--text-dark) !important;
            font-weight: 700;
        }

        .brand-gold {
            color: var(--gold-accent);
        }

        /* Hero Banner VIP */
        .hero-section {
            position: relative;
            min-height: 88vh;
            display: flex;
            align-items: center;
            background: linear-gradient(90deg, rgba(250, 248, 245, 0.95) 0%, rgba(250, 248, 245, 0.75) 50%, rgba(250, 248, 245, 0.3) 100%),
                        url('https://images.unsplash.com/photo-1621605815971-fbc98d665033?auto=format&fit=crop&w=1920&q=80') center right/cover no-repeat;
            padding-top: 100px;
        }

        .hero-title {
            font-size: 4.8rem;
            line-height: 1.05;
            color: var(--text-dark);
            font-weight: 700;
        }

        /* Botones VIP */
        .btn-gold {
            background: var(--gold-gradient);
            color: #ffffff;
            font-weight: 600;
            letter-spacing: 1px;
            padding: 14px 38px;
            border-radius: 4px;
            border: none;
            box-shadow: 0 10px 25px rgba(197, 160, 89, 0.3);
            transition: all 0.3s ease;
        }

        .btn-gold:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(197, 160, 89, 0.45);
            color: #ffffff;
        }

        .btn-outline-dark-vip {
            border: 2px solid var(--text-dark);
            color: var(--text-dark);
            font-weight: 600;
            padding: 12px 32px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .btn-outline-dark-vip:hover {
            background-color: var(--text-dark);
            color: #ffffff;
        }

        /* Tarjetas Estilo VIP Clean */
        .card-custom {
            background: var(--surface-white);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .card-custom:hover {
            transform: translateY(-8px);
            border-color: var(--gold-accent);
            box-shadow: 0 20px 40px rgba(197, 160, 89, 0.18);
        }

        .icon-box {
            width: 75px;
            height: 75px;
            background: rgba(197, 160, 89, 0.08);
            border: 1px solid var(--border-subtle);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: var(--gold-accent);
            font-size: 2rem;
        }

        .price-badge {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--gold-dark);
        }

        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-subtitle {
            color: var(--gold-accent);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 4px;
            font-size: 0.85rem;
        }

        .vip-badge {
            background-color: #1c1917;
            color: #d4af37;
            border-radius: 30px;
            padding: 6px 18px;
            font-size: 0.8rem;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand font-serif" href="index.php">
                JUAN STYLE <span class="brand-gold">VIP</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto ms-lg-4">
                    <li class="nav-item"><a class="nav-link text-dark fw-medium" href="index.php">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link text-dark fw-medium" href="#servicios">Servicios VIP</a></li>
                    <li class="nav-item"><a class="nav-link text-dark fw-medium" href="#productos">Boutique</a></li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <?php if ($_SESSION['rol'] === 'admin'): ?>
                            <a href="admin.php" class="btn btn-dark font-serif fs-5 py-1 px-4"><i class="bi bi-speedometer2 me-1"></i> PANEL ADMIN</a>
                        <?php else: ?>
                            <a href="agendar.php" class="btn btn-gold font-serif fs-5 py-1 px-4"><i class="bi bi-calendar-check me-1"></i> AGENDAR CITA</a>
                            <a href="mis_citas.php" class="btn btn-outline-dark-vip py-2 px-3 fs-6"><i class="bi bi-journal-check"></i> Mis Citas</a>
                        <?php endif; ?>
                        <a href="logout.php" class="btn btn-outline-danger py-2 px-3"><i class="bi bi-box-arrow-right"></i></a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-dark-vip py-2 px-4">Iniciar Sesión</a>
                        <a href="registro.php" class="btn btn-gold font-serif fs-5 py-1 px-4">Registrarse</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION VIP -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <span class="vip-badge fw-bold mb-3 d-inline-block">
                        <i class="bi bi-gem me-1"></i> EXECUTIVE GROOMING CLUB
                    </span>
                    <h1 class="hero-title font-serif">
                        El Arte del Buen Gusto <br>& <span class="brand-gold">Estilo Exclusivo</span>
                    </h1>
                    <p class="lead text-muted col-md-10 my-4 fs-5 fw-normal">
                        Bienvenido a <strong class="text-dark">JUAN STYLE VIP</strong>. Una experiencia de atención personalizada de alto nivel en un ambiente refinado.
                    </p>
                    
                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <?php if (isset($_SESSION['usuario_id']) && $_SESSION['rol'] === 'cliente'): ?>
                            <a href="agendar.php" class="btn btn-gold font-serif fs-4 px-5"><i class="bi bi-calendar-plus me-2"></i>RESERVAR MI CITA VIP</a>
                        <?php elseif (!isset($_SESSION['usuario_id'])): ?>
                            <a href="login.php" class="btn btn-gold font-serif fs-4 px-5"><i class="bi bi-shield-check me-2"></i>AGENDAR COMO CLIENTE</a>
                        <?php endif; ?>
                        <a href="#servicios" class="btn btn-outline-dark-vip font-serif fs-4 px-4">CONOCER SERVICIOS</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECCIÓN SERVICIOS VIP -->
    <section id="servicios" class="py-5" style="background-color: #f4efea;">
        <div class="container py-5">
            <div class="section-header">
                <span class="section-subtitle">EXPERIENCIA EXCLUSIVA</span>
                <h2 class="font-serif display-4 text-dark fw-bold">NUESTROS SERVICIOS VIP</h2>
            </div>

            <div class="row g-4">
                <?php if ($resultado_servicios && $resultado_servicios->num_rows > 0): ?>
                    <?php while ($servicio = $resultado_servicios->fetch_assoc()): ?>
                        <div class="col-md-4">
                            <div class="card card-custom h-100 p-4 text-center">
                                <div class="icon-box">
                                    <i class="bi bi-scissors"></i>
                                </div>
                                <h3 class="font-serif fs-2 text-dark mb-2 fw-bold"><?php echo htmlspecialchars($servicio['nombre']); ?></h3>
                                <p class="text-muted small mb-4"><?php echo htmlspecialchars($servicio['descripcion']); ?></p>
                                
                                <div class="mt-auto">
                                    <div class="price-badge font-serif mb-3">$<?php echo number_format($servicio['precio'], 0, ',', '.'); ?></div>
                                    <?php if (isset($_SESSION['usuario_id']) && $_SESSION['rol'] === 'cliente'): ?>
                                        <a href="agendar.php" class="btn btn-outline-dark-vip w-100">Reservar Cita</a>
                                    <?php else: ?>
                                        <a href="login.php" class="btn btn-outline-secondary w-100">Inicia sesión</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- SECCIÓN PRODUCTOS BOUTIQUE -->
    <section id="productos" class="py-5">
        <div class="container py-5">
            <div class="section-header">
                <span class="section-subtitle">CUIDADO Y MANTENIMIENTO</span>
                <h2 class="font-serif display-4 text-dark fw-bold">BOUTIQUE DE PRODUCTOS</h2>
            </div>

            <div class="row g-4">
                <?php if ($resultado_productos && $resultado_productos->num_rows > 0): ?>
                    <?php while ($producto = $resultado_productos->fetch_assoc()): ?>
                        <div class="col-lg-3 col-md-6">
                            <div class="card card-custom h-100 p-4 text-center">
                                <div class="bg-light rounded-3 p-4 mb-3 border">
                                    <i class="bi bi-box-seam fs-1 brand-gold"></i>
                                </div>
                                <h4 class="font-serif fs-3 text-dark mb-1 fw-bold"><?php echo htmlspecialchars($producto['nombre']); ?></h4>
                                <div class="badge bg-light text-dark border mb-3">Disponible: <?php echo $producto['stock']; ?> un.</div>
                                
                                <div class="mt-auto">
                                    <div class="price-badge font-serif fs-2 mb-3">$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></div>
                                    <button class="btn btn-gold font-serif fs-5 w-100 py-1"><i class="bi bi-cart-plus me-1"></i> COMPRAR</button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-muted">No hay productos disponibles.</div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- FOOTER VIP -->
    <footer class="py-5 border-top" style="background-color: #1c1917; color: #ffffff;">
        <div class="container text-center">
            <h2 class="font-serif brand-gold fs-1 mb-2">JUAN STYLE VIP</h2>
            <p class="text-secondary mb-4">El estándar supremo en cuidado masculino.</p>
            <div class="d-flex justify-content-center gap-4 fs-4 mb-4">
                <a href="#" class="text-warning"><i class="bi bi-instagram"></i></a>
                <a href="#" class="text-warning"><i class="bi bi-facebook"></i></a>
                <a href="#" class="text-warning"><i class="bi bi-whatsapp"></i></a>
            </div>
            <p class="text-secondary small mb-0">&copy; <?php echo date('Y'); ?> JUAN STYLE VIP. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>