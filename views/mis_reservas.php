<?php
// views/mis_reservas.php
require_once __DIR__ . '/../config/conexion.php';
Auth::requireLogin();

$cliente_id = Auth::user()['id'];
$mensaje_exito = '';
$mensaje_error = '';

// 1. PROCESAR REPROGRAMACIÓN DE CITA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reprogramar_cita'])) {
    $cita_id = (int)$_POST['cita_id'];
    $nueva_fecha = $_POST['nueva_fecha'];
    $nueva_hora = $_POST['nueva_hora'];

    if (!empty($nueva_fecha) && !empty($nueva_hora)) {
        $nueva_fecha_hora = $nueva_fecha . ' ' . $nueva_hora . ':00';

        $stmtRep = $pdo->prepare("UPDATE reservas SET fecha_hora_inicio = :nueva_fecha_hora WHERE id = :id AND cliente_id = :cliente AND estado = 'pendiente'");
        $stmtRep->execute([
            ':nueva_fecha_hora' => $nueva_fecha_hora,
            ':id' => $cita_id,
            ':cliente' => $cliente_id
        ]);

        $mensaje_exito = "¡La cita ha sido reprogramada con éxito!";
    } else {
        $mensaje_error = "Por favor selecciona una fecha y hora válidas.";
    }
}

// 2. PROCESAR ACTUALIZACIÓN DE PERFIL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_perfil'])) {
    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);

    $stmtUp = $pdo->prepare("UPDATE usuarios SET nombre = :nombre, telefono = :telefono, email = :email WHERE id = :id");
    $stmtUp->execute([':nombre' => $nombre, ':telefono' => $telefono, ':email' => $email, ':id' => $cliente_id]);
    $mensaje_exito = "¡Tus datos personales han sido actualizados con éxito!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_pass'])) {
    $pass_actual = $_POST['pass_actual'];
    $pass_nueva = $_POST['pass_nueva'];

    $stmtPass = $pdo->prepare("SELECT password FROM usuarios WHERE id = :id");
    $stmtPass->execute([':id' => $cliente_id]);
    $userPass = $stmtPass->fetchColumn();

    if (password_verify($pass_actual, $userPass)) {
        $nuevo_hash = password_hash($pass_nueva, PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("UPDATE usuarios SET password = :pass WHERE id = :id");
        $updateStmt->execute([':pass' => $nuevo_hash, ':id' => $cliente_id]);
        $mensaje_exito = "¡Contraseña actualizada con éxito!";
    } else {
        $mensaje_error = "La contraseña actual no es correcta.";
    }
}

// 3. PROCESAR CANCELACIÓN DE CITA
if (isset($_GET['cancelar_cita'])) {
    $cita_id = (int)$_GET['cancelar_cita'];
    $stmtCancel = $pdo->prepare("UPDATE reservas SET estado = 'cancelada' WHERE id = :id AND cliente_id = :cliente");
    $stmtCancel->execute([':id' => $cita_id, ':cliente' => $cliente_id]);
    $mensaje_exito = "La cita fue cancelada correctamente.";
}

// 4. CONSULTAR DATOS DEL CLIENTE
$stmtUser = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmtUser->execute([':id' => $cliente_id]);
$clienteActual = $stmtUser->fetch();

// 5. CONSULTAR CITAS DEL CLIENTE
$stmt = $pdo->prepare("SELECT r.*, s.nombre AS servicio_nombre 
                       FROM reservas r 
                       JOIN servicios s ON r.servicio_id = s.id 
                       WHERE r.cliente_id = :cliente_id 
                       ORDER BY r.fecha_hora_inicio DESC");
$stmt->execute([':cliente_id' => $cliente_id]);
$mis_reservas = $stmt->fetchAll();

$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'citas';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-0">Panel de Cliente</h2>
            <p class="text-muted small">Bienvenido, <strong><?= htmlspecialchars($clienteActual['nombre']) ?></strong></p>
        </div>
        <a href="index.php#servicios" class="btn btn-gold rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Agendar Nueva Cita
        </a>
    </div>

    <?php if (isset($_GET['reserva_exito'])): ?>
        <div class="alert alert-success rounded-pill text-center py-2 mb-4">
            ¡Tu reserva ha sido confirmada con éxito! Te esperamos en Juan’Style.
        </div>
    <?php endif; ?>

    <?php if (!empty($mensaje_exito)): ?>
        <div class="alert alert-success rounded-pill text-center py-2 mb-4"><?= $mensaje_exito ?></div>
    <?php endif; ?>
    <?php if (!empty($mensaje_error)): ?>
        <div class="alert alert-danger rounded-pill text-center py-2 mb-4"><?= $mensaje_error ?></div>
    <?php endif; ?>

    <!-- NAVEGACIÓN -->
    <ul class="nav nav-pills mb-4 gap-2">
        <li class="nav-item">
            <a class="nav-link rounded-pill px-4 fw-bold <?= $activeTab === 'citas' ? 'bg-gold text-white' : 'btn-light border text-dark' ?>" href="index.php?page=mis_reservas&tab=citas">
                <i class="bi bi-calendar-check me-1"></i> Mis Citas
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-4 fw-bold <?= $activeTab === 'perfil' ? 'bg-gold text-white' : 'btn-light border text-dark' ?>" href="index.php?page=mis_reservas&tab=perfil">
                <i class="bi bi-person-gear me-1"></i> Mi Perfil & Datos
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-4 fw-bold <?= $activeTab === 'notificaciones' ? 'bg-gold text-white' : 'btn-light border text-dark' ?>" href="index.php?page=mis_reservas&tab=notificaciones">
                <i class="bi bi-bell me-1"></i> Notificaciones
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-4 fw-bold <?= $activeTab === 'resenas' ? 'bg-gold text-white' : 'btn-light border text-dark' ?>" href="index.php?page=mis_reservas&tab=resenas">
                <i class="bi bi-star me-1"></i> Calificaciones & Reseñas
            </a>
        </li>
    </ul>

    <!-- SECCIÓN: MIS CITAS -->
    <?php if ($activeTab === 'citas'): ?>
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-calendar3 text-gold me-2"></i>Historial y Gestión de Citas</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Servicio</th>
                            <th>Barbero</th>
                            <th>Fecha y Hora</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($mis_reservas)): ?>
                            <?php foreach ($mis_reservas as $res): ?>
                                <tr>
                                    <td>#<?= $res['id'] ?></td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($res['servicio_nombre']) ?></td>
                                    <td>Juan González</td>
                                    <td><?= date('d/m/Y - g:i A', strtotime($res['fecha_hora_inicio'])) ?></td>
                                    <td class="fw-bold text-gold">$<?= number_format($res['precio_acordado'], 0, ',', '.') ?> COP</td>
                                    <td>
                                        <?php 
                                            $badgeClass = 'bg-secondary';
                                            if ($res['estado'] === 'pendiente') $badgeClass = 'bg-warning text-dark';
                                            elseif ($res['estado'] === 'confirmada') $badgeClass = 'bg-primary';
                                            elseif ($res['estado'] === 'completada') $badgeClass = 'bg-success';
                                            elseif ($res['estado'] === 'cancelada') $badgeClass = 'bg-danger';
                                        ?>
                                        <span class="badge <?= $badgeClass ?> rounded-pill text-uppercase px-3 py-1"><?= $res['estado'] ?></span>
                                    </td>
                                    <td>
                                        <?php if ($res['estado'] === 'pendiente'): ?>
                                            <div class="d-flex gap-1">
                                                <!-- Botón para abrir modal de reprogramación -->
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2" data-bs-toggle="modal" data-bs-target="#reprogramarModal<?= $res['id'] ?>">
                                                    Reprogramar
                                                </button>
                                                <a href="index.php?page=mis_reservas&cancelar_cita=<?= $res['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill px-2" onclick="return confirm('¿Seguro que deseas cancelar esta cita?');">
                                                    Cancelar
                                                </a>
                                            </div>

                                            <!-- MODAL DE REPROGRAMACIÓN -->
                                            <div class="modal fade" id="reprogramarModal<?= $res['id'] ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content rounded-4 border-0 shadow">
                                                        <div class="modal-header border-0 pb-0">
                                                            <h5 class="fw-bold text-dark">Reprogramar Cita #<?= $res['id'] ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form action="index.php?page=mis_reservas&tab=citas" method="POST">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="reprogramar_cita" value="1">
                                                                <input type="hidden" name="cita_id" value="<?= $res['id'] ?>">
                                                                <div class="mb-3">
                                                                    <label class="form-label small fw-bold">Nueva Fecha</label>
                                                                    <input type="date" name="nueva_fecha" class="form-control rounded-pill px-3" min="<?= date('Y-m-d') ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label small fw-bold">Nueva Hora</label>
                                                                    <input type="time" name="nueva_hora" class="form-control rounded-pill px-3" min="08:00" max="20:00" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer border-0 pt-0">
                                                                <button type="button" class="btn btn-outline-dark rounded-pill px-4 btn-sm" data-bs-dismiss="modal">Cerrar</button>
                                                                <button type="submit" class="btn btn-gold rounded-pill px-4 btn-sm fw-bold">Guardar Cambios</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                        <?php else: ?>
                                            <span class="text-muted small">No modifificable</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No tienes citas registradas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- SECCIÓN: PERFIL -->
    <?php if ($activeTab === 'perfil'): ?>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-person-badge text-gold me-2"></i>Editar Datos Personales</h5>
                    <form action="index.php?page=mis_reservas&tab=perfil" method="POST">
                        <input type="hidden" name="actualizar_perfil" value="1">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control rounded-pill px-3" value="<?= htmlspecialchars($clienteActual['nombre']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control rounded-pill px-3" value="<?= htmlspecialchars($clienteActual['email']) ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Teléfono / WhatsApp</label>
                            <input type="text" name="telefono" class="form-control rounded-pill px-3" value="<?= htmlspecialchars($clienteActual['telefono'] ?? '') ?>" required>
                        </div>
                        <button type="submit" class="btn btn-gold w-100 rounded-pill py-2 fw-bold shadow-sm">Guardar Cambios</button>
                    </form>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-shield-lock text-gold me-2"></i>Seguridad & Contraseña</h5>
                    <form action="index.php?page=mis_reservas&tab=perfil" method="POST">
                        <input type="hidden" name="cambiar_pass" value="1">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Contraseña Actual</label>
                            <input type="password" name="pass_actual" class="form-control rounded-pill px-3" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Nueva Contraseña</label>
                            <input type="password" name="pass_nueva" class="form-control rounded-pill px-3" required>
                        </div>
                        <button type="submit" class="btn btn-outline-dark w-100 rounded-pill py-2 fw-bold shadow-sm">Actualizar Contraseña</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- SECCIÓN: NOTIFICACIONES -->
    <?php if ($activeTab === 'notificaciones'): ?>
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-bell text-gold me-2"></i>Centro de Notificaciones</h5>
            <div class="list-group list-group-flush">
                <div class="list-group-item px-0 py-3 border-bottom">
                    <h6 class="mb-1 fw-bold text-dark"><i class="bi bi-check-circle-fill text-success me-2"></i>Cita Confirmada</h6>
                    <p class="mb-1 small text-muted">Tu cita con Juan González se encuentra activa. Puedes reprogramarla o cancelarla si lo requieres antes de la fecha.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- SECCIÓN: RESEÑAS -->
    <?php if ($activeTab === 'resenas'): ?>
        <div class="row g-4">
            <div class="col-md-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-secondary-custom">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-star-fill text-gold me-2"></i>Califica tu Experiencia</h5>
                    <form onsubmit="event.preventDefault(); alert('¡Gracias por tu comentario!');">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Puntuación</label>
                            <select class="form-select rounded-pill px-3">
                                <option value="5">⭐⭐⭐⭐⭐ (Excelente)</option>
                                <option value="4">⭐⭐⭐⭐ (Muy Bueno)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Comentarios</label>
                            <textarea class="form-control rounded-3" rows="3" placeholder="¿Qué te pareció el servicio?"></textarea>
                        </div>
                        <button type="submit" class="btn btn-gold w-100 rounded-pill py-2 fw-bold shadow-sm">Enviar Reseña</button>
                    </form>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-chat-quote text-gold me-2"></i>Opiniones de Clientes</h5>
                    <p class="small text-muted">"Excelente atención por parte de Juan. El degradado quedó impecable." — Carlos M.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>