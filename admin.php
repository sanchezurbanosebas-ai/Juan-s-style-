<?php
session_start();
require_once 'conexion.php';

// Control de Acceso: Solo administradores
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Actualizar estado de la cita
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $nuevo_estado = $_GET['accion'];
    $id_cita = intval($_GET['id']);
    
    $stmt = $conn->prepare("UPDATE citas SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $nuevo_estado, $id_cita);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// Consultar todas las citas con datos del cliente y servicio (JOIN)
$sql = "SELECT c.id, u.nombre AS cliente_nombre, u.apellido AS cliente_apellido, u.correo, 
               s.nombre AS servicio, s.precio, c.fecha, c.hora, c.estado 
        FROM citas c
        INNER JOIN usuarios u ON c.id_usuario = u.id
        INNER JOIN servicios s ON c.id_servicio = s.id
        ORDER BY c.fecha DESC, c.hora ASC";

$resultado = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Administrador - Barbería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-speedometer2 me-2"></i>Panel de Administración</h2>
            <span class="badge bg-danger fs-6">Administrador: <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white fw-bold">
                Gestión Global de Citas Agendadas
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0 align-middle">
                        <thead class="table-secondary">
                            <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Correo</th>
                                <th>Servicio</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultado->num_rows > 0): ?>
                                <?php while ($cita = $resultado->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $cita['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($cita['cliente_nombre'] . ' ' . $cita['cliente_apellido']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($cita['correo']); ?></td>
                                        <td><?php echo htmlspecialchars($cita['servicio']); ?> ($<?php echo number_format($cita['precio'], 0); ?>)</td>
                                        <td><?php echo date('d/m/Y', strtotime($cita['fecha'])); ?></td>
                                        <td><?php echo date('h:i A', strtotime($cita['hora'])); ?></td>
                                        <td>
                                            <?php 
                                            $badge = 'bg-warning text-dark';
                                            if ($cita['estado'] === 'Confirmada') $badge = 'bg-success';
                                            if ($cita['estado'] === 'Cancelada') $badge = 'bg-danger';
                                            if ($cita['estado'] === 'Completada') $badge = 'bg-primary';
                                            ?>
                                            <span class="badge <?php echo $badge; ?>"><?php echo $cita['estado']; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <a href="admin.php?accion=Confirmada&id=<?php echo $cita['id']; ?>" class="btn btn-sm btn-outline-success" title="Confirmar"><i class="bi bi-check-lg"></i></a>
                                            <a href="admin.php?accion=Completada&id=<?php echo $cita['id']; ?>" class="btn btn-sm btn-outline-primary" title="Completar"><i class="bi bi-flag"></i></a>
                                            <a href="admin.php?accion=Cancelada&id=<?php echo $cita['id']; ?>" class="btn btn-sm btn-outline-danger" title="Cancelar"><i class="bi bi-x-lg"></i></a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">No hay citas registradas en el sistema.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>