<?php
session_start();
require_once '../conexion.php';

// Validar que el usuario sea Administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Procesar cambio de estado de cita
if (isset($_POST['actualizar_estado'])) {
    $cita_id = intval($_POST['cita_id']);
    $nuevo_estado = $_POST['nuevo_estado'];

    $sql_update = "UPDATE citas SET estado = ? WHERE id = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$nuevo_estado, $cita_id]);

    header("Location: dashboard.php?exito=estado_actualizado");
    exit();
}

// Consultas para tarjetas de estadísticas
$total_citas = $pdo->query("SELECT COUNT(*) FROM citas")->fetchColumn();
$citas_hoy = $pdo->query("SELECT COUNT(*) FROM citas WHERE fecha_cita = CURDATE()")->fetchColumn();
$total_clientes = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'cliente'")->fetchColumn();

$ingresos_estimados = $pdo->query("SELECT SUM(s.precio) 
                                   FROM citas c 
                                   INNER JOIN servicios s ON c.servicio_id = s.id 
                                   WHERE c.estado IN ('Confirmada', 'Completada')")->fetchColumn() ?: 0;

// Obtener listado de todas las citas agendadas
$sql_citas = "SELECT c.id, c.fecha_cita, c.hora_cita, c.estado,
                     u.nombre AS cliente_nombre, u.telefono AS cliente_telefono,
                     b.nombre AS barbero_nombre,
                     s.nombre_servicio, s.precio
              FROM citas c
              INNER JOIN usuarios u ON c.usuario_id = u.id
              INNER JOIN barberos b ON c.barbero_id = b.id
              INNER JOIN servicios s ON c.servicio_id = s.id
              ORDER BY c.fecha_cita DESC, c.hora_cita DESC";

$citas = $pdo->query($sql_citas)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo | JUAN STYLE</title>

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

        .card-stat {
            background-color: var(--color-gris-oscuro);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 8px;
            transition: transform 0.3s ease;
        }

        .card-stat:hover {
            transform: translateY(-5px);
            border-color: var(--color-dorado);
        }

        .table-dark-custom {
            background-color: var(--color-gris-oscuro);
            color: #fff;
            border-color: rgba(212, 175, 55, 0.2);
        }

        .form-select-sm-custom {
            background-color: #121413;
            color: #fff;
            border: 1px solid rgba(212, 175, 55, 0.3);
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

    <!-- NAVBAR ADMIN -->
    <nav class="navbar navbar-expand-lg navbar-luxury sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand font-luxury text-white fw-bold" href="../index.php">
                <i class="fa-solid fa-scissors text-gold me-2"></i>JUAN <span class="text-gold">STYLE</span> <small class="text-muted fs-6">| Admin</small>
            </a>
            <div class="ms-auto d-flex align-items-center gap-3">
                <a href="servicios.php" class="btn btn-outline-warning btn-sm border-gold text-gold"><i class="fa-solid fa-list-check me-1"></i> Gestionar Servicios</a>
                <a href="../logout.php" class="btn btn-danger btn-sm"><i class="fa-solid fa-right-from-bracket me-1"></i> Salir</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="font-luxury text-white mb-0">Panel de <span class="text-gold">Control</span></h2>
                <p class="text-muted small mb-0">Administración general de citas, clientes e ingresos</p>
            </div>
        </div>

        <?php if (isset($_GET['exito'])): ?>
            <div class="alert alert-success bg-dark border-success text-success small mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>Estado de la cita actualizado correctamente.
            </div>
        <?php endif; ?>

        <!-- TARJETAS DE ESTADÍSTICAS -->
        <div class="row g-3 mb-5">
            <div class="col-md-3">
                <div class="card card-stat p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase">Citas Hoy</span>
                            <h3 class="text-gold fw-bold mb-0 mt-1"><?php echo $citas_hoy; ?></h3>
                        </div>
                        <i class="fa-solid fa-calendar-day text-gold fa-2x"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-stat p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase">Total Citas</span>
                            <h3 class="text-white fw-bold mb-0 mt-1"><?php echo $total_citas; ?></h3>
                        </div>
                        <i class="fa-solid fa-calendar-check text-gold fa-2x"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-stat p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase">Clientes Registrados</span>
                            <h3 class="text-white fw-bold mb-0 mt-1"><?php echo $total_clientes; ?></h3>
                        </div>
                        <i class="fa-solid fa-users text-gold fa-2x"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-stat p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase">Ingresos Estimados</span>
                            <h3 class="text-gold fw-bold mb-0 mt-1">$<?php echo number_format($ingresos_estimados, 0, ',', '.'); ?></h3>
                        </div>
                        <i class="fa-solid fa-sack-dollar text-gold fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLA GENERAL DE CITAS -->
        <div class="card card-stat p-4">
            <h4 class="font-luxury text-white mb-3"><i class="fa-solid fa-list text-gold me-2"></i>Gestión Global de Citas</h4>
            
            <?php if (count($citas) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover table-dark-custom align-middle mb-0">
                        <thead>
                            <tr class="text-gold border-bottom border-warning">
                                <th>Cliente</th>
                                <th>Teléfono</th>
                                <th>Servicio</th>
                                <th>Barbero</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Precio</th>
                                <th>Estado Actual</th>
                                <th>Cambiar Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($citas as $c): ?>
                                <tr>
                                    <td class="fw-semibold text-white"><?php echo htmlspecialchars($c['cliente_nombre']); ?></td>
                                    <td>
                                        <a href="https://wa.me/57<?php echo preg_replace('/[^0-9]/', '', $c['cliente_telefono']); ?>" target="_blank" class="text-success text-decoration-none">
                                            <i class="fa-brands fa-whatsapp me-1"></i><?php echo htmlspecialchars($c['cliente_telefono']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($c['nombre_servicio']); ?></td>
                                    <td><?php echo htmlspecialchars($c['barbero_nombre']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($c['fecha_cita'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($c['hora_cita'])); ?></td>
                                    <td class="text-gold fw-bold">$<?php echo number_format($c['precio'], 0, ',', '.'); ?></td>
                                    <td>
                                        <?php
                                            $estado = $c['estado'];
                                            if ($estado === 'Pendiente') echo '<span class="badge bg-warning text-dark">Pendiente</span>';
                                            elseif ($estado === 'Confirmada') echo '<span class="badge bg-primary">Confirmada</span>';
                                            elseif ($estado === 'Completada') echo '<span class="badge bg-success">Completada</span>';
                                            elseif ($estado === 'Cancelada') echo '<span class="badge bg-danger">Cancelada</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <form action="dashboard.php" method="POST" class="d-flex gap-2">
                                            <input type="hidden" name="cita_id" value="<?php echo $c['id']; ?>">
                                            <select name="nuevo_estado" class="form-select form-select-sm form-select-sm-custom">
                                                <option value="Pendiente" <?php echo ($estado === 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                                <option value="Confirmada" <?php echo ($estado === 'Confirmada') ? 'selected' : ''; ?>>Confirmada</option>
                                                <option value="Completada" <?php echo ($estado === 'Completada') ? 'selected' : ''; ?>>Completada</option>
                                                <option value="Cancelada" <?php echo ($estado === 'Cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                                            </select>
                                            <button type="submit" name="actualizar_estado" class="btn btn-gold btn-sm"><i class="fa-solid fa-floppy-disk"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">No hay citas registradas en el sistema por el momento.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>