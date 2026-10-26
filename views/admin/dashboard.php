<?php
// views/admin/dashboard.php
require_once __DIR__ . '/../../config/conexion.php';
Auth::requireAdmin();

$mensaje_exito = '';
$mensaje_error = '';
$admin_id = Auth::user()['id'];

// 1. GESTIÓN DE CITAS (Cambiar estado, reprogramar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_cita'])) {
    $reserva_id = (int)$_POST['reserva_id'];
    $nuevo_estado = $_POST['nuevo_estado'];
    $stmt = $pdo->prepare("UPDATE reservas SET estado = :estado WHERE id = :id");
    $stmt->execute([':estado' => $nuevo_estado, ':id' => $reserva_id]);
    header('Location: index.php?page=admin_dashboard&tab=citas&exito=1');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_reprogramar_cita'])) {
    $reserva_id = (int)$_POST['reserva_id'];
    $nueva_fecha = $_POST['nueva_fecha'];
    $nueva_hora = $_POST['nueva_hora'];
    if (!empty($nueva_fecha) && !empty($nueva_hora)) {
        $fecha_hora = $nueva_fecha . ' ' . $nueva_hora . ':00';
        $stmtRep = $pdo->prepare("UPDATE reservas SET fecha_hora_inicio = :fecha_hora WHERE id = :id");
        $stmtRep->execute([':fecha_hora' => $fecha_hora, ':id' => $reserva_id]);
        $mensaje_exito = "¡Cita reprogramada correctamente!";
    }
}

// 2. GESTIÓN DE SERVICIOS (Crear, Editar, Desactivar, Eliminar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_servicio'])) {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = (float)$_POST['precio'];
    $duracion = (int)$_POST['duracion'];
    $stmt = $pdo->prepare("INSERT INTO servicios (nombre, descripcion, precio, duracion_minutos, estado) VALUES (:nombre, :descripcion, :precio, :duracion, 1)");
    $stmt->execute([':nombre' => $nombre, ':descripcion' => $descripcion, ':precio' => $precio, ':duracion' => $duracion]);
    header('Location: index.php?page=admin_dashboard&tab=servicios&exito=1');
    exit();
}

if (isset($_GET['toggle_servicio'])) {
    $serv_id = (int)$_GET['toggle_servicio'];
    $stmt = $pdo->prepare("UPDATE servicios SET estado = IF(estado=1, 0, 1) WHERE id = :id");
    $stmt->execute([':id' => $serv_id]);
    header('Location: index.php?page=admin_dashboard&tab=servicios');
    exit();
}

if (isset($_GET['eliminar_servicio'])) {
    $serv_id = (int)$_GET['eliminar_servicio'];
    $stmtDel = $pdo->prepare("DELETE FROM servicios WHERE id = :id");
    $stmtDel->execute([':id' => $serv_id]);
    header('Location: index.php?page=admin_dashboard&tab=servicios');
    exit();
}

// 3. INVENTARIO Y PRODUCTOS
$pdo->exec("CREATE TABLE IF NOT EXISTS inventario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    stock INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL
)");
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_producto'])) {
    $prod_nombre = trim($_POST['prod_nombre']);
    $prod_stock = (int)$_POST['prod_stock'];
    $prod_precio = (float)$_POST['prod_precio'];
    $stmtProd = $pdo->prepare("INSERT INTO inventario (nombre, stock, precio) VALUES (:nombre, :stock, :precio)");
    $stmtProd->execute([':nombre' => $prod_nombre, ':stock' => $prod_stock, ':precio' => $prod_precio]);
    header('Location: index.php?page=admin_dashboard&tab=inventario');
    exit();
}

// 4. SEGURIDAD ADMIN Y ACTUALIZAR PERFIL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_actualizar_perfil'])) {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);

    $stmtUpAdmin = $pdo->prepare("UPDATE usuarios SET nombre = :nombre, email = :email, telefono = :telefono WHERE id = :id");
    $stmtUpAdmin->execute([':nombre' => $nombre, ':email' => $email, ':telefono' => $telefono, ':id' => $admin_id]);
    $mensaje_exito = "¡Tu información de perfil ha sido actualizada con éxito!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_cambiar_pass'])) {
    $pass_actual = $_POST['pass_actual'];
    $pass_nueva = $_POST['pass_nueva'];

    $stmtPass = $pdo->prepare("SELECT password FROM usuarios WHERE id = :id");
    $stmtPass->execute([':id' => $admin_id]);
    $userPass = $stmtPass->fetchColumn();

    if (password_verify($pass_actual, $userPass)) {
        $nuevo_hash = password_hash($pass_nueva, PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("UPDATE usuarios SET password = :pass WHERE id = :id");
        $updateStmt->execute([':pass' => $nuevo_hash, ':id' => $admin_id]);
        $mensaje_exito = "¡Contraseña de administrador actualizada con éxito!";
    } else {
        $mensaje_error = "La contraseña actual no es correcta.";
    }
}

// CONSULTAS PARA ESTADÍSTICAS Y VISTAS
$totalCitasHoy = $pdo->query("SELECT COUNT(*) FROM reservas WHERE DATE(fecha_hora_inicio) = CURDATE()")->fetchColumn();
$totalClientes = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'cliente'")->fetchColumn();
$ingresosMes = $pdo->query("SELECT COALESCE(SUM(precio_acordado), 0) FROM reservas WHERE estado = 'completada' AND MONTH(fecha_hora_inicio) = MONTH(CURRENT_DATE())")->fetchColumn();
$citasPendientes = $pdo->query("SELECT COUNT(*) FROM reservas WHERE estado = 'pendiente'")->fetchColumn();

$stmtAdminInfo = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmtAdminInfo->execute([':id' => $admin_id]);
$adminInfo = $stmtAdminInfo->fetch();

$reservas = $pdo->query("SELECT r.*, u.nombre AS cliente_nombre, u.telefono AS cliente_telefono, s.nombre AS servicio_nombre FROM reservas r JOIN usuarios u ON r.cliente_id = u.id JOIN servicios s ON r.servicio_id = s.id ORDER BY r.fecha_hora_inicio DESC")->fetchAll();
$servicios = $pdo->query("SELECT * FROM servicios ORDER BY id ASC")->fetchAll();
$clientes = $pdo->query("SELECT * FROM usuarios WHERE rol = 'cliente' ORDER BY fecha_registro DESC")->fetchAll();
$inventario = $pdo->query("SELECT * FROM inventario ORDER BY id ASC")->fetchAll();

$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'resumen';
?>

<div class="container py-4">
    <!-- CABECERA -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h2 class="fw-bold text-dark mb-0">Panel de Control General</h2>
            <p class="text-muted small mb-0">Gestión Integral Juan’Style | Barbero: <strong><?= htmlspecialchars($adminInfo['nombre']) ?></strong></p>
        </div>
        <div>
            <a href="index.php?page=home" class="btn btn-outline-dark rounded-pill btn-sm px-3">Ver Sitio Web</a>
            <a href="index.php?page=logout" class="btn btn-outline-danger rounded-pill btn-sm px-3">Salir</a>
        </div>
    </div>

    <?php if (!empty($mensaje_exito)): ?>
        <div class="alert alert-success rounded-pill text-center py-2 mb-4"><?= $mensaje_exito ?></div>
    <?php endif; ?>
    <?php if (!empty($mensaje_error)): ?>
        <div class="alert alert-danger rounded-pill text-center py-2 mb-4"><?= $mensaje_error ?></div>
    <?php endif; ?>

    <!-- PESTAÑAS PRINCIPALES -->
    <ul class="nav nav-pills mb-4 gap-2 flex-wrap">
        <li class="nav-item"><a class="nav-link rounded-pill px-4 fw-bold <?= $activeTab === 'resumen' ? 'bg-gold text-white' : 'btn-light border text-dark' ?>" href="index.php?page=admin_dashboard&tab=resumen">Resumen</a></li>
        <li class="nav-item"><a class="nav-link rounded-pill px-4 fw-bold <?= $activeTab === 'citas' ? 'bg-gold text-white' : 'btn-light border text-dark' ?>" href="index.php?page=admin_dashboard&tab=citas">Agenda & Citas (<?= count($reservas) ?>)</a></li>
        <li class="nav-item"><a class="nav-link rounded-pill px-4 fw-bold <?= $activeTab === 'servicios' ? 'bg-gold text-white' : 'btn-light border text-dark' ?>" href="index.php?page=admin_dashboard&tab=servicios">Servicios & Precios</a></li>
        <li class="nav-item"><a class="nav-link rounded-pill px-4 fw-bold <?= $activeTab === 'clientes' ? 'bg-gold text-white' : 'btn-light border text-dark' ?>" href="index.php?page=admin_dashboard&tab=clientes">Clientes (<?= count($clientes) ?>)</a></li>
        <li class="nav-item"><a class="nav-link rounded-pill px-4 fw-bold <?= $activeTab === 'inventario' ? 'bg-gold text-white' : 'btn-light border text-dark' ?>" href="index.php?page=admin_dashboard&tab=inventario"><i class="bi bi-box-seam me-1"></i> Inventario</a></li>
        <li class="nav-item"><a class="nav-link rounded-pill px-4 fw-bold <?= $activeTab === 'resenas' ? 'bg-gold text-white' : 'btn-light border text-dark' ?>" href="index.php?page=admin_dashboard&tab=resenas"><i class="bi bi-star me-1"></i> Reseñas</a></li>
        <li class="nav-item"><a class="nav-link rounded-pill px-4 fw-bold <?= $activeTab === 'seguridad' ? 'bg-gold text-white' : 'btn-light border text-dark' ?>" href="index.php?page=admin_dashboard&tab=seguridad"><i class="bi bi-shield-lock me-1"></i> Perfil & Seguridad</a></li>
    </ul>

    <!-- 1. RESUMEN -->
    <?php if ($activeTab === 'resumen'): ?>
        <div class="row g-3 mb-4">
            <div class="col-md-3"><div class="card p-3 border-0 shadow-sm rounded-4 bg-white border-start border-gold border-4"><small class="text-muted fw-bold">CITAS DE HOY</small><h3 class="fw-bold mt-1 mb-0"><?= $totalCitasHoy ?></h3></div></div>
            <div class="col-md-3"><div class="card p-3 border-0 shadow-sm rounded-4 bg-white border-start border-warning border-4"><small class="text-muted fw-bold">PENDIENTES</small><h3 class="fw-bold text-warning mt-1 mb-0"><?= $citasPendientes ?></h3></div></div>
            <div class="col-md-3"><div class="card p-3 border-0 shadow-sm rounded-4 bg-white border-start border-success border-4"><small class="text-muted fw-bold">INGRESOS DEL MES</small><h3 class="fw-bold text-success mt-1 mb-0">$<?= number_format($ingresosMes, 0, ',', '.') ?> COP</h3></div></div>
            <div class="col-md-3"><div class="card p-3 border-0 shadow-sm rounded-4 bg-white border-start border-primary border-4"><small class="text-muted fw-bold">CLIENTES TOTALES</small><h3 class="fw-bold text-primary mt-1 mb-0"><?= $totalClientes ?></h3></div></div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h5 class="fw-bold text-dark mb-3">Rendimiento e Ingresos Estimados</h5>
            <div class="text-center py-4">
                <canvas id="rendimientoChart" style="max-height: 250px; width: 100%;"></canvas>
            </div>
        </div>
    <?php endif; ?>

    <!-- 2. GESTIÓN DE CITAS -->
    <?php if ($activeTab === 'citas'): ?>
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h5 class="fw-bold text-dark mb-3">Agenda General de Citas</h5>
            <div class="table-responsive">
                <table class="table align-middle table-hover">
                    <thead class="table-light"><tr><th>ID</th><th>Cliente</th><th>Teléfono</th><th>Servicio</th><th>Fecha y Hora</th><th>Precio</th><th>Estado</th><th>Acciones de Administrador</th></tr></thead>
                    <tbody>
                        <?php foreach ($reservas as $res): ?>
                            <tr>
                                <td>#<?= $res['id'] ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($res['cliente_nombre']) ?></td>
                                <td><a href="https://wa.me/57<?= preg_replace('/[^0-9]/', '', $res['cliente_telefono']) ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-2"><i class="bi bi-whatsapp"></i> <?= $res['cliente_telefono'] ?></a></td>
                                <td><?= htmlspecialchars($res['servicio_nombre']) ?></td>
                                <td><?= date('d/m/Y - g:i A', strtotime($res['fecha_hora_inicio'])) ?></td>
                                <td class="fw-bold text-gold">$<?= number_format($res['precio_acordado'], 0, ',', '.') ?></td>
                                <td><span class="badge bg-secondary rounded-pill text-uppercase"><?= $res['estado'] ?></span></td>
                                <td>
                                    <div class="d-flex gap-1 align-items-center">
                                        <form action="index.php?page=admin_dashboard&tab=citas" method="POST">
                                            <input type="hidden" name="reserva_id" value="<?= $res['id'] ?>">
                                            <input type="hidden" name="accion_cita" value="1">
                                            <select name="nuevo_estado" onchange="this.form.submit()" class="form-select form-select-sm rounded-pill" style="width: 130px;">
                                                <option value="">Estado...</option>
                                                <option value="confirmada">Confirmar</option>
                                                <option value="completada">Completar</option>
                                                <option value="cancelada">Cancelar</option>
                                            </select>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2" data-bs-toggle="modal" data-bs-target="#reprogramarAdmin<?= $res['id'] ?>">
                                            Reprogramar
                                        </button>
                                    </div>

                                    <!-- MODAL REPROGRAMAR -->
                                    <div class="modal fade" id="reprogramarAdmin<?= $res['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="fw-bold text-dark">Reprogramar Cita #<?= $res['id'] ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="index.php?page=admin_dashboard&tab=citas" method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="admin_reprogramar_cita" value="1">
                                                        <input type="hidden" name="reserva_id" value="<?= $res['id'] ?>">
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
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- 3. SERVICIOS -->
    <?php if ($activeTab === 'servicios'): ?>
        <div class="row g-4">
            <div class="col-md-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-secondary-custom">
                    <h5 class="fw-bold text-dark mb-3">Registrar Nuevo Servicio</h5>
                    <form action="index.php?page=admin_dashboard&tab=servicios" method="POST">
                        <input type="hidden" name="crear_servicio" value="1">
                        <div class="mb-3"><label class="form-label small fw-bold">Nombre</label><input type="text" name="nombre" class="form-control rounded-pill px-3" required></div>
                        <div class="mb-3"><label class="form-label small fw-bold">Descripción</label><textarea name="descripcion" class="form-control rounded-3" rows="2"></textarea></div>
                        <div class="row g-2 mb-3">
                            <div class="col-6"><label class="form-label small fw-bold">Precio (COP)</label><input type="number" name="precio" class="form-control rounded-pill px-3" required></div>
                            <div class="col-6"><label class="form-label small fw-bold">Duración (min)</label><input type="number" name="duracion" class="form-control rounded-pill px-3" value="30" required></div>
                        </div>
                        <button type="submit" class="btn btn-gold w-100 rounded-pill fw-bold py-2 shadow-sm">Guardar Servicio</button>
                    </form>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h5 class="fw-bold text-dark mb-3">Listado de Servicios</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light"><tr><th>Servicio</th><th>Precio</th><th>Duración</th><th>Estado</th><th>Acciones</th></tr></thead>
                            <tbody>
                                <?php foreach ($servicios as $s): ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($s['nombre']) ?></td>
                                        <td class="text-gold fw-bold">$<?= number_format($s['precio'], 0, ',', '.') ?></td>
                                        <td><?= $s['duracion_minutos'] ?> min</td>
                                        <td><span class="badge <?= $s['estado'] == 1 ? 'bg-success' : 'bg-danger' ?> rounded-pill"><?= $s['estado'] == 1 ? 'Activo' : 'Inactivo' ?></span></td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="index.php?page=admin_dashboard&tab=servicios&toggle_servicio=<?= $s['id'] ?>" class="btn btn-sm btn-outline-dark rounded-pill px-2">Cambiar Estado</a>
                                                <a href="index.php?page=admin_dashboard&tab=servicios&eliminar_servicio=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill px-2" onclick="return confirm('¿Seguro que deseas eliminar este servicio?');">Eliminar</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- 4. CLIENTES -->
    <?php if ($activeTab === 'clientes'): ?>
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h5 class="fw-bold text-dark mb-3">Directorio de Clientes Registrados</h5>
            <div class="table-responsive">
                <table class="table align-middle table-hover">
                    <thead class="table-light"><tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Fecha Registro</th></tr></thead>
                    <tbody>
                        <?php foreach ($clientes as $c): ?>
                            <tr>
                                <td>#<?= $c['id'] ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($c['nombre']) ?></td>
                                <td><?= htmlspecialchars($c['email']) ?></td>
                                <td><?= htmlspecialchars($c['telefono'] ?? 'No registrado') ?></td>
                                <td><?= $c['fecha_registro'] ?? 'N/A' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- 5. INVENTARIO -->
    <?php if ($activeTab === 'inventario'): ?>
        <div class="row g-4">
            <div class="col-md-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-secondary-custom">
                    <h5 class="fw-bold text-dark mb-3">Registrar Producto</h5>
                    <form action="index.php?page=admin_dashboard&tab=inventario" method="POST">
                        <input type="hidden" name="crear_producto" value="1">
                        <div class="mb-3"><label class="form-label small fw-bold">Nombre del Producto</label><input type="text" name="prod_nombre" class="form-control rounded-pill px-3" required></div>
                        <div class="mb-3"><label class="form-label small fw-bold">Stock</label><input type="number" name="prod_stock" class="form-control rounded-pill px-3" required></div>
                        <div class="mb-3"><label class="form-label small fw-bold">Precio</label><input type="number" name="prod_precio" class="form-control rounded-pill px-3" required></div>
                        <button type="submit" class="btn btn-gold w-100 rounded-pill fw-bold py-2 shadow-sm">Guardar Producto</button>
                    </form>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h5 class="fw-bold text-dark mb-3">Control de Stock</h5>
                    <table class="table align-middle">
                        <thead class="table-light"><tr><th>Producto</th><th>Stock</th><th>Precio</th></tr></thead>
                        <tbody>
                            <?php foreach ($inventario as $inv): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($inv['nombre']) ?></td>
                                    <td><span class="badge <?= $inv['stock'] < 5 ? 'bg-danger' : 'bg-success' ?> rounded-pill"><?= $inv['stock'] ?> unid.</span></td>
                                    <td class="text-gold fw-bold">$<?= number_format($inv['precio'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- 6. RESEÑAS -->
    <?php if ($activeTab === 'resenas'): ?>
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h5 class="fw-bold text-dark mb-3">Reseñas de Clientes</h5>
            <div class="border-bottom pb-3 mb-3">
                <div class="d-flex justify-content-between"><h6 class="fw-bold mb-1">Carlos Mendoza</h6><span class="text-gold">⭐⭐⭐⭐⭐</span></div>
                <p class="small text-muted mb-0">"Excelente atención por parte de Juan. El degradado quedó impecable."</p>
            </div>
            <div>
                <div class="d-flex justify-content-between"><h6 class="fw-bold mb-1">Andrés Felipe</h6><span class="text-gold">⭐⭐⭐⭐⭐</span></div>
                <p class="small text-muted mb-0">"El mejor lugar en Villapatricia. Puntualidad y excelente servicio."</p>
            </div>
        </div>
    <?php endif; ?>

    <!-- 7. PERFIL & SEGURIDAD ADMIN -->
    <?php if ($activeTab === 'seguridad'): ?>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-person-badge text-gold me-2"></i>Actualizar Información de Perfil</h5>
                    <form action="index.php?page=admin_dashboard&tab=seguridad" method="POST">
                        <input type="hidden" name="admin_actualizar_perfil" value="1">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nombre del Barbero / Admin</label>
                            <input type="text" name="nombre" class="form-control rounded-pill px-3" value="<?= htmlspecialchars($adminInfo['nombre']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control rounded-pill px-3" value="<?= htmlspecialchars($adminInfo['email']) ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Teléfono / WhatsApp</label>
                            <input type="text" name="telefono" class="form-control rounded-pill px-3" value="<?= htmlspecialchars($adminInfo['telefono'] ?? '') ?>" required>
                        </div>
                        <button type="submit" class="btn btn-gold w-100 rounded-pill py-2 fw-bold shadow-sm">Guardar Cambios de Perfil</button>
                    </form>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-shield-lock text-gold me-2"></i>Cambiar Contraseña de Administrador</h5>
                    <form action="index.php?page=admin_dashboard&tab=seguridad" method="POST">
                        <input type="hidden" name="admin_cambiar_pass" value="1">
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
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('rendimientoChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
                datasets: [{
                    label: 'Atenciones Estimadas de Juan González',
                    data: [4, 6, 5, 8, 12, 15],
                    borderColor: '#C9A227',
                    backgroundColor: 'rgba(201, 162, 39, 0.1)',
                    borderWidth: 3,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
});
</script>