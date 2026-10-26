<?php
session_start();
require_once '../conexion.php';

// Validar que el usuario haya iniciado sesión y sea cliente
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: ../login.php");
    exit();
}

// Cargar barberos
$stmt_barberos = $pdo->query("SELECT * FROM barberos ORDER BY nombre ASC");
$barberos = $stmt_barberos->fetchAll(PDO::FETCH_ASSOC);

// Cargar servicios
$stmt_servicios = $pdo->query("SELECT * FROM servicios ORDER BY nombre_servicio ASC");
$servicios = $stmt_servicios->fetchAll(PDO::FETCH_ASSOC);

// Si viene un servicio preseleccionado desde el index
$servicio_seleccionado = isset($_GET['servicio']) ? intval($_GET['servicio']) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Cita | JUAN STYLE</title>

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
            background-color: var(--color-negro);
            color: var(--color-blanco);
            min-height: 100vh;
        }

        .font-luxury {
            font-family: 'Playfair Display', serif;
        }

        .text-gold {
            color: var(--color-dorado) !important;
        }

        .navbar-luxury {
            background-color: rgba(9, 34, 25, 0.95);
            border-bottom: 1px solid rgba(212, 175, 55, 0.25);
        }

        .card-luxury {
            background-color: var(--color-gris-oscuro);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
        }

        .form-control-luxury, .form-select-luxury {
            background-color: #121413;
            border: 1px solid rgba(212, 175, 55, 0.3);
            color: #fff;
            padding: 10px 14px;
            border-radius: 4px;
        }

        .form-control-luxury:focus, .form-select-luxury:focus {
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

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-luxury sticky-top">
        <div class="container">
            <a class="navbar-brand font-luxury text-white fw-bold" href="../index.php">
                <i class="fa-solid fa-scissors text-gold me-2"></i>JUAN <span class="text-gold">STYLE</span>
            </a>
            <div class="ms-auto d-flex align-items-center gap-2">
                <span class="text-light small d-none d-md-inline me-2"><i class="fa-solid fa-user-circle text-gold me-1"></i> <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
                <a href="mis_citas.php" class="btn btn-outline-warning btn-sm border-gold text-gold">Mis Citas</a>
                <a href="../logout.php" class="btn btn-danger btn-sm">Salir</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card card-luxury p-4 p-md-5">
                    <div class="text-center mb-4">
                        <h2 class="font-luxury text-white mb-0">Agendar <span class="text-gold">Cita VIP</span></h2>
                        <div class="gold-divider"></div>
                        <p class="text-muted small text-uppercase">Selecciona tu barbero, servicio y el horario que mejor te convenga</p>
                    </div>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger bg-dark border-danger text-danger text-center small mb-4" role="alert">
                            <i class="fa-solid fa-circle-exclamation me-2"></i>
                            <?php
                                if ($_GET['error'] === 'campos_vacios') echo 'Por favor completa todos los campos requeridos.';
                                elseif ($_GET['error'] === 'hora_ocupada') echo 'El barbero ya tiene una cita reservada a esa misma hora. Elige otro horario.';
                                else echo 'Ocurrió un error al procesar tu cita. Inténtalo de nuevo.';
                            ?>
                        </div>
                    <?php endif; ?>

                    <form action="procesar_agendar.php" method="POST">
                        <!-- Servicio -->
                        <div class="mb-3">
                            <label for="servicio_id" class="form-label text-light small fw-semibold">Servicio Requerido</label>
                            <select class="form-select form-select-luxury" id="servicio_id" name="servicio_id" required>
                                <option value="" disabled <?php echo ($servicio_seleccionado == 0) ? 'selected' : ''; ?>>-- Selecciona un Servicio --</option>
                                <?php foreach ($servicios as $s): ?>
                                    <option value="<?php echo $s['id']; ?>" <?php echo ($servicio_seleccionado == $s['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($s['nombre_servicio']); ?> - $<?php echo number_format($s['precio'], 0, ',', '.'); ?> (<?php echo $s['duracion_minutos']; ?> min)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Barbero -->
                        <div class="mb-3">
                            <label for="barbero_id" class="form-label text-light small fw-semibold">Barbero Preferido</label>
                            <select class="form-select form-select-luxury" id="barbero_id" name="barbero_id" required>
                                <option value="" disabled selected>-- Selecciona un Barbero --</option>
                                <?php foreach ($barberos as $b): ?>
                                    <option value="<?php echo $b['id']; ?>">
                                        <?php echo htmlspecialchars($b['nombre']); ?> (<?php echo htmlspecialchars($b['especialidad']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <!-- Fecha -->
                            <div class="col-md-6 mb-3">
                                <label for="fecha_cita" class="form-label text-light small fw-semibold">Fecha de la Cita</label>
                                <input type="date" class="form-control form-control-luxury" id="fecha_cita" name="fecha_cita" min="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <!-- Hora -->
                            <div class="col-md-6 mb-4">
                                <label for="hora_cita" class="form-label text-light small fw-semibold">Hora de la Cita</label>
                                <select class="form-select form-select-luxury" id="hora_cita" name="hora_cita" required>
                                    <option value="" disabled selected>-- Selecciona Hora --</option>
                                    <option value="08:00">08:00 AM</option>
                                    <option value="09:00">09:00 AM</option>
                                    <option value="10:00">10:00 AM</option>
                                    <option value="11:00">11:00 AM</option>
                                    <option value="12:00">12:00 PM</option>
                                    <option value="14:00">02:00 PM</option>
                                    <option value="15:00">03:00 PM</option>
                                    <option value="16:00">04:00 PM</option>
                                    <option value="17:00">05:00 PM</option>
                                    <option value="18:00">06:00 PM</option>
                                    <option value="19:00">07:00 PM</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-gold mb-3">
                            <i class="fa-solid fa-calendar-plus me-2"></i>Confirmar Reserva
                        </button>
                        
                        <div class="text-center">
                            <a href="mis_citas.php" class="text-secondary text-decoration-none small"><i class="fa-solid fa-arrow-left me-1"></i> Volver a Mis Citas</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>