<?php
session_start();
require_once '../conexion.php';

// Validar que el usuario haya iniciado sesión y sea cliente
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: ../login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Procesar cancelación de cita por parte del cliente
if (isset($_GET['cancelar_id'])) {
    $cita_id = intval($_GET['cancelar_id']);
    
    // Asegurar que la cita pertenezca al usuario en sesión
    $sql_cancelar = "UPDATE citas SET estado = 'Cancelada' WHERE id = ? AND usuario_id = ?";
    $stmt_cancelar = $pdo->prepare($sql_cancelar);
    $stmt_cancelar->execute([$cita_id, $usuario_id]);
    
    header("Location: mis_citas.php?exito=cancelada");
    exit();
}

// Consultar citas del usuario con INNER JOIN para traer los nombres de barbero y servicio
$sql = "SELECT c.id, c.fecha_cita, c.hora_cita, c.estado, 
               b.nombre AS barbero_nombre, 
               s.nombre_servicio, s.precio 
        FROM citas c
        INNER JOIN barberos b ON c.barbero_id = b.id
        INNER JOIN servicios s ON c.servicio_id = s.id
        WHERE c.usuario_id = ?
        ORDER BY c.fecha_cita DESC, c.hora_cita DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id]);
$citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Citas | JUAN STYLE</title>

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
        }

        .btn-gold {
            background: linear-gradient(135deg, #D4AF37 0%, #AA7C11 100%);
            color: #000;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
        }

        .btn-gold:hover {
            background: linear-gradient(135deg, #F3E5AB 0%, #D4AF37 100%);
            color: #000;
        }

        .table-dark-custom {
            background-color: var(--color-gris-oscuro);
            color: #fff;
            border-color: rgba(212, 175, 55, 0.2);
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
                <a href="agendar.php" class="btn btn-gold btn-sm"><i class="fa-solid fa-plus me-1"></i> Nueva Cita</a>
                <a href="../logout.php" class="btn btn-danger btn-sm">Salir</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="text-center mb-4">
            <h2 class="font-luxury text-white">Mis Citas <span class="text-gold">Reservadas</span></h2>
            <div class="gold-divider"></div>
            <p class="text-muted small">Consulta el historial y estado de tus reservas en JUAN STYLE</p>
        </div>

        <?php if (isset($_GET['exito'])): ?>
            <div class="alert alert-success bg-dark border-success text-success text-center small mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>
                <?php 
                    if ($_GET['exito'] === 'creada') echo '¡Tu cita ha sido agendada con éxito!';
                    elseif ($_GET['exito'] === 'cancelada') echo 'La cita ha sido cancelada correctamente.';
                ?>
            </div>
        <?php endif; ?>

        <div class="card card-luxury p-3 p-md-4">
            <?php if (count($citas) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover table-dark-custom align-middle mb-0">
                        <thead>
                            <tr class="text-gold border-bottom border-warning">
                                <th>Servicio</th>
                                <th>Barbero</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Precio</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($citas as $cita): ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($cita['nombre_servicio']); ?></td>
                                    <td><?php echo htmlspecialchars($cita['barbero_nombre']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($cita['fecha_cita'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($cita['hora_cita'])); ?></td>
                                    <td class="text-gold fw-bold">$<?php echo number_format($cita['precio'], 0, ',', '.'); ?></td>
                                    <td>
                                        <?php
                                            $estado = $cita['estado'];
                                            if ($estado === 'Pendiente') echo '<span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i>Pendiente</span>';
                                            elseif ($estado === 'Confirmada') echo '<span class="badge bg-primary"><i class="fa-solid fa-circle-check me-1"></i>Confirmada</span>';
                                            elseif ($estado === 'Completada') echo '<span class="badge bg-success"><i class="fa-solid fa-check-double me-1"></i>Completada</span>';
                                            elseif ($estado === 'Cancelada') echo '<span class="badge bg-danger"><i class="fa-solid fa-circle-xmark me-1"></i>Cancelada</span>';
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($cita['estado'] !== 'Cancelada' && $cita['estado'] !== 'Completada'): ?>
                                            <!-- Botón confirmación WhatsApp -->
                                            <?php 
                                                $mensaje_wa = urlencode("Hola, soy " . $_SESSION['nombre'] . ". Quiero confirmar mi cita del " . $cita['fecha_cita'] . " a las " . $cita['hora_cita'] . " para " . $cita['nombre_servicio']);
                                            ?>
                                            <a href="https://wa.me/573001234567?text=<?php echo $mensaje_wa; ?>" target="_blank" class="btn btn-sm btn-outline-success me-1" title="Confirmar por WhatsApp">
                                                <i class="fa-brands fa-whatsapp"></i>
                                            </a>
                                            <!-- Cancelar Cita -->
                                            <a href="mis_citas.php?cancelar_id=<?php echo $cita['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Estás seguro de cancelar esta cita?');" title="Cancelar Cita">
                                                <i class="fa-solid fa-xmark"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">Sin acciones</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fa-solid fa-calendar-xmark text-muted fa-3x mb-3"></i>
                    <h5 class="text-light">Aún no tienes citas agendadas</h5>
                    <p class="text-muted small mb-4">Reserva tu primer servicio VIP con nuestros barberos profesionales.</p>
                    <a href="agendar.php" class="btn btn-gold">Agendar Cita Ahora</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>