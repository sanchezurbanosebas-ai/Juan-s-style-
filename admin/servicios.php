<?php
session_start();
require_once '../conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Agregar servicio
if (isset($_POST['agregar_servicio'])) {
    $nombre = trim($_POST['nombre_servicio']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $duracion = intval($_POST['duracion_minutos']);

    if (!empty($nombre) && $precio > 0) {
        $sql = "INSERT INTO servicios (nombre_servicio, descripcion, precio, duracion_minutos) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $descripcion, $precio, $duracion]);
        header("Location: servicios.php?exito=agregado");
        exit();
    }
}

// Eliminar servicio
if (isset($_GET['eliminar_id'])) {
    $id = intval($_GET['eliminar_id']);
    $sql = "DELETE FROM servicios WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    header("Location: servicios.php?exito=eliminado");
    exit();
}

// Listar servicios
$servicios = $pdo->query("SELECT * FROM servicios ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Servicios | JUAN STYLE Admin</title>

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

        .form-control-luxury {
            background-color: #121413;
            border: 1px solid rgba(212, 175, 55, 0.3);
            color: #fff;
        }

        .form-control-luxury:focus {
            background-color: #161817;
            border-color: var(--color-dorado);
            color: #fff;
        }

        .btn-gold {
            background: linear-gradient(135deg, #D4AF37 0%, #AA7C11 100%);
            color: #000;
            font-weight: 700;
            border: none;
        }

        .btn-gold:hover {
            background: linear-gradient(135deg, #F3E5AB 0%, #D4AF37 100%);
            color: #000;
        }
    </style>
</head>
<body>

    <!-- NAVBAR ADMIN -->
    <nav class="navbar navbar-expand-lg navbar-luxury sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand font-luxury text-white fw-bold" href="dashboard.php">
                <i class="fa-solid fa-scissors text-gold me-2"></i>JUAN <span class="text-gold">STYLE</span> <small class="text-muted fs-6">| Servicios</small>
            </a>
            <div class="ms-auto d-flex align-items-center gap-2">
                <a href="dashboard.php" class="btn btn-outline-warning btn-sm border-gold text-gold"><i class="fa-solid fa-arrow-left me-1"></i> Volver al Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row g-4">
            <!-- FORMULARIO NUEVO SERVICIO -->
            <div class="col-md-5">
                <div class="card card-luxury p-4">
                    <h4 class="font-luxury text-white mb-3"><i class="fa-solid fa-plus text-gold me-2"></i>Nuevo Servicio</h4>
                    <form action="servicios.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label text-light small">Nombre del Servicio</label>
                            <input type="text" name="nombre_servicio" class="form-control form-control-luxury" placeholder="Ej: Arreglo de Barba VIP" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-light small">Descripción</label>
                            <textarea name="descripcion" class="form-control form-control-luxury" rows="2" placeholder="Detalles del servicio..."></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label text-light small">Precio ($)</label>
                                <input type="number" step="1000" name="precio" class="form-control form-control-luxury" placeholder="25000" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-light small">Duración (min)</label>
                                <input type="number" name="duracion_minutos" class="form-control form-control-luxury" value="30" required>
                            </div>
                        </div>
                        <button type="submit" name="agregar_servicio" class="btn btn-gold w-100"><i class="fa-solid fa-plus me-1"></i> Guardar Servicio</button>
                    </form>
                </div>
            </div>

            <!-- TABLA DE SERVICIOS -->
            <div class="col-md-7">
                <div class="card card-luxury p-4">
                    <h4 class="font-luxury text-white mb-3"><i class="fa-solid fa-list text-gold me-2"></i>Servicios Existentes</h4>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-gold">
                                    <th>Servicio</th>
                                    <th>Precio</th>
                                    <th>Duración</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($servicios as $s): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-white d-block"><?php echo htmlspecialchars($s['nombre_servicio']); ?></strong>
                                            <small class="text-muted"><?php echo htmlspecialchars($s['descripcion']); ?></small>
                                        </td>
                                        <td class="text-gold fw-bold">$<?php echo number_format($s['precio'], 0, ',', '.'); ?></td>
                                        <td><?php echo $s['duracion_minutos']; ?> min</td>
                                        <td class="text-center">
                                            <a href="servicios.php?eliminar_id=<?php echo $s['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Deseas eliminar este servicio?');"><i class="fa-solid fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>