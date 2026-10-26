<?php
session_start();
require_once 'conexion.php';

// Verificar que sea cliente autenticado
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'cliente') {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['usuario_id'];

// Consultar únicamente las citas del cliente que inició sesión
$sql = "SELECT c.id, s.nombre AS servicio, s.precio, c.fecha, c.hora, c.estado 
        FROM citas c 
        INNER JOIN servicios s ON c.id_servicio = s.id 
        WHERE c.id_usuario = ? 
        ORDER BY c.fecha DESC, c.hora DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Citas - Barbería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2 class="mb-4">Mis Citas Agendadas</h2>

        <?php if ($resultado->num_rows > 0): ?>
            <div class="table-responsive bg-white shadow-sm rounded p-3">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Servicio</th>
                            <th>Precio</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['servicio']); ?></strong></td>
                                <td>$<?php echo number_format($row['precio'], 0, ',', '.'); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['fecha'])); ?></td>
                                <td><?php echo date('h:i A', strtotime($row['hora'])); ?></td>
                                <td>
                                    <?php 
                                    $estado = $row['estado'];
                                    $clase = 'bg-warning text-dark';
                                    if ($estado == 'Confirmada') $clase = 'bg-success';
                                    if ($estado == 'Cancelada') $clase = 'bg-danger';
                                    if ($estado == 'Completada') $clase = 'bg-primary';
                                    ?>
                                    <span class="badge <?php echo $clase; ?>"><?php echo $estado; ?></span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Aún no tienes citas agendadas. <a href="agendar.php">Agenda tu primera cita aquí</a>.</div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>