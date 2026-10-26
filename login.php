<?php
session_start();

// Si el usuario ya inició sesión, redirigir según su rol
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: cliente/mis_citas.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | JUAN STYLE</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Google Fonts Luxury -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&family=Playfair+Display:ital,wght@0,600;0,800&display=swap" rel="stylesheet">

    <style>
        :root {
            --color-negro: #0b0d0c;
            --color-verde-oscuro: #092219;
            --color-dorado: #D4AF37;
            --color-dorado-brillo: #F3E5AB;
            --color-blanco: #FFFFFF;
            --color-gris-oscuro: #1b1e1d;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(rgba(11, 13, 12, 0.85), rgba(9, 34, 25, 0.95)),
                        url('https://images.unsplash.com/photo-1503951914875-452162b0f3f1?q=80&w=1920') center/cover no-repeat;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-blanco);
            padding: 20px;
        }

        .card-auth {
            background-color: var(--color-gris-oscuro);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.7);
            max-width: 450px;
            width: 100%;
        }

        .font-luxury {
            font-family: 'Playfair Display', serif;
        }

        .text-gold {
            color: var(--color-dorado) !important;
        }

        .form-control-luxury {
            background-color: #121413;
            border: 1px solid rgba(212, 175, 55, 0.3);
            color: #fff;
            padding: 12px 15px;
            border-radius: 4px;
        }

        .form-control-luxury:focus {
            background-color: #161817;
            border-color: var(--color-dorado);
            color: #fff;
            box-shadow: 0 0 8px rgba(212, 175, 55, 0.4);
        }

        .btn-gold {
            background: linear-gradient(135deg, #D4AF37 0%, #AA7C11 100%);
            color: #000;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 12px;
            border: none;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-gold:hover {
            background: linear-gradient(135deg, #F3E5AB 0%, #D4AF37 100%);
            color: #000;
        }

        .gold-divider {
            width: 60px;
            height: 2px;
            background-color: var(--color-dorado);
            margin: 15px auto;
        }
    </style>
</head>
<body>

    <div class="card card-auth p-4 p-md-5">
        <div class="text-center mb-4">
            <a href="index.php" class="text-decoration-none">
                <h2 class="font-luxury text-white mb-0">
                    <i class="fa-solid fa-scissors text-gold me-2"></i>JUAN <span class="text-gold">STYLE</span>
                </h2>
            </a>
            <div class="gold-divider"></div>
            <p class="text-muted small text-uppercase letter-spacing-1">Ingreso a Plataforma VIP</p>
        </div>

        <!-- Muestra de alertas según parámetros GET -->
        <?php if (isset($_GET['exito']) && $_GET['exito'] === 'registrado'): ?>
            <div class="alert alert-success bg-dark border-success text-success text-center small" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>¡Registro exitoso! Ya puedes iniciar sesión.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger bg-dark border-danger text-danger text-center small" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i>
                <?php
                    if ($_GET['error'] === 'campos_vacios') echo 'Por favor completa todos los campos.';
                    elseif ($_GET['error'] === 'credenciales_incorrectas') echo 'Correo o contraseña incorrectos.';
                    else echo 'Ocurrió un error. Intenta de nuevo.';
                ?>
            </div>
        <?php endif; ?>

        <form action="procesar_login.php" method="POST">
            <div class="mb-3">
                <label for="correo" class="form-label text-light small fw-semibold">Correo Electrónico</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-gold"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" class="form-control form-control-luxury" id="correo" name="correo" placeholder="ejemplo@correo.com" required>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label text-light small fw-semibold">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-gold"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" class="form-control form-control-luxury" id="password" name="password" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-gold mb-3">
                <i class="fa-solid fa-right-to-bracket me-2"></i>Iniciar Sesión
            </button>
        </form>

        <div class="text-center mt-3">
            <p class="small text-muted mb-1">¿Aún no tienes una cuenta?</p>
            <a href="registro.php" class="text-gold text-decoration-none fw-semibold small">Regístrate como Cliente AQUÍ</a>
            <div class="mt-3">
                <a href="index.php" class="text-secondary text-decoration-none small"><i class="fa-solid fa-arrow-left me-1"></i> Volver al Inicio</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>