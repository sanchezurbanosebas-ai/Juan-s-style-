<?php
declare(strict_types=1);
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'], $_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

if (empty($_SESSION['csrf_clientes'])) {
    $_SESSION['csrf_clientes'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_clientes'];
$mensaje = '';
$tipoMensaje = 'success';

function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function redirigir(string $query = ''): never {
    header('Location: gestion_clientes.php' . ($query !== '' ? '?' . $query : ''));
    exit;
}
function validarCsrf(): void {
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf_clientes'] ?? '', (string)$_POST['csrf'])) {
        http_response_code(403);
        exit('Solicitud no válida (CSRF). Recarga la página e inténtalo nuevamente.');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarCsrf();
    $accion = (string)($_POST['accion'] ?? '');
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;

    try {
        if ($accion === 'crear' || $accion === 'editar') {
            $nombre = trim((string)($_POST['nombre'] ?? ''));
            $apellido = trim((string)($_POST['apellido'] ?? ''));
            $correo = strtolower(trim((string)($_POST['correo'] ?? '')));
            $telefono = trim((string)($_POST['telefono'] ?? ''));

            if ($nombre === '' || $apellido === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Completa nombre y apellido e introduce un correo válido.');
            }
            if (mb_strlen($nombre) > 50 || mb_strlen($apellido) > 50 || mb_strlen($correo) > 100 || mb_strlen($telefono) > 30) {
                throw new RuntimeException('Uno de los campos supera la longitud permitida.');
            }

            if ($accion === 'crear') {
                $password = (string)($_POST['password'] ?? '');
                if (strlen($password) < 8) throw new RuntimeException('La contraseña inicial debe tener al menos 8 caracteres.');
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellido, correo, password, rol, telefono, estado_cuenta) VALUES (?, ?, ?, ?, 'cliente', ?, 'activo')");
                $stmt->bind_param('sssss', $nombre, $apellido, $correo, $hash, $telefono);
                $stmt->execute();
                redirigir('ok=creado');
            }

            if ($id < 1) throw new RuntimeException('Identificador de cliente no válido.');
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE id = ? AND rol = 'cliente'");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            if (!$stmt->get_result()->fetch_assoc()) throw new RuntimeException('No se encontró ese cliente.');

            $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, apellido = ?, correo = ?, telefono = ? WHERE id = ? AND rol = 'cliente'");
            $stmt->bind_param('ssssi', $nombre, $apellido, $correo, $telefono, $id);
            $stmt->execute();
            redirigir('ok=editado');
        }

        if ($accion === 'password') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
            $password = (string)($_POST['password_nueva'] ?? '');
            $confirmar = (string)($_POST['confirmar_password'] ?? '');
            if ($id < 1 || strlen($password) < 8) throw new RuntimeException('La contraseña debe tener al menos 8 caracteres.');
            if (!hash_equals($password, $confirmar)) throw new RuntimeException('Las contraseñas no coinciden.');
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ? AND rol = 'cliente'");
            $stmt->bind_param('si', $hash, $id);
            $stmt->execute();
            if ($stmt->affected_rows !== 1) throw new RuntimeException('No se pudo cambiar la contraseña del cliente.');
            redirigir('ok=password');
        }

        if ($accion === 'estado') {
            $estado = (string)($_POST['estado_cuenta'] ?? '');
            if ($id < 1 || !in_array($estado, ['activo', 'inactivo'], true)) throw new RuntimeException('Estado no válido.');
            $stmt = $conn->prepare("UPDATE usuarios SET estado_cuenta = ? WHERE id = ? AND rol = 'cliente'");
            $stmt->bind_param('si', $estado, $id);
            $stmt->execute();
            redirigir($estado === 'activo' ? 'ok=activado' : 'ok=desactivado');
        }

        if ($accion === 'eliminar') {
            if ($id < 1) throw new RuntimeException('Identificador no válido.');
            // No borrar historial de citas: si tiene citas, primero desactiva la cuenta.
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE id = ? AND rol = 'cliente' FOR UPDATE");
            $conn->begin_transaction();
            $stmt->bind_param('i', $id);
            $stmt->execute();
            if (!$stmt->get_result()->fetch_assoc()) {
                $conn->rollback();
                throw new RuntimeException('No se encontró el cliente o no es una cuenta de cliente.');
            }
            $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM citas WHERE id_usuario = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $total = (int)$stmt->get_result()->fetch_assoc()['total'];
            if ($total > 0) {
                $conn->rollback();
                throw new RuntimeException("Este cliente tiene $total cita(s) asociada(s). Para conservar el historial, desactiva la cuenta en lugar de eliminarla.");
            }
            $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ? AND rol = 'cliente'");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $conn->commit();
            redirigir('ok=eliminado');
        }
        throw new RuntimeException('Acción no reconocida.');
    } catch (mysqli_sql_exception $ex) {
        if ($conn->errno) { try { $conn->rollback(); } catch (Throwable $ignored) {} }
        if ((int)$ex->getCode() === 1062) $mensaje = 'Ese correo electrónico ya está registrado.';
        else $mensaje = 'Ocurrió un error de base de datos. Verifica los datos e inténtalo de nuevo.';
        $tipoMensaje = 'danger';
    } catch (RuntimeException $ex) {
        $mensaje = $ex->getMessage();
        $tipoMensaje = 'danger';
    }
}

$avisos = [
    'creado' => 'Cliente creado correctamente.',
    'editado' => 'Datos del cliente actualizados.',
    'password' => 'Contraseña restablecida correctamente.',
    'activado' => 'Cuenta activada.',
    'desactivado' => 'Cuenta desactivada.',
    'eliminado' => 'Cliente eliminado. No tenía citas asociadas.'
];
if (isset($_GET['ok'], $avisos[$_GET['ok']])) $mensaje = $avisos[$_GET['ok']];

$q = trim((string)($_GET['q'] ?? ''));
$like = '%' . $q . '%';
if ($q !== '') {
    $stmt = $conn->prepare("SELECT id, nombre, apellido, correo, telefono, fecha_registro, estado_cuenta FROM usuarios WHERE rol = 'cliente' AND (nombre LIKE ? OR apellido LIKE ? OR correo LIKE ? OR CAST(id AS CHAR) LIKE ?) ORDER BY id DESC");
    $stmt->bind_param('ssss', $like, $like, $like, $like);
} else {
    $stmt = $conn->prepare("SELECT id, nombre, apellido, correo, telefono, fecha_registro, estado_cuenta FROM usuarios WHERE rol = 'cliente' ORDER BY id DESC");
}
$stmt->execute();
$clientes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$editar = null;
$ver = null;
if (isset($_GET['editar']) || isset($_GET['ver'])) {
    $targetId = filter_input(INPUT_GET, isset($_GET['editar']) ? 'editar' : 'ver', FILTER_VALIDATE_INT) ?: 0;
    $stmt = $conn->prepare("SELECT id, nombre, apellido, correo, telefono, fecha_registro, estado_cuenta FROM usuarios WHERE id = ? AND rol = 'cliente'");
    $stmt->bind_param('i', $targetId);
    $stmt->execute();
    $registro = $stmt->get_result()->fetch_assoc();
    if (isset($_GET['editar'])) $editar = $registro;
    else $ver = $registro;
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Gestión de Clientes | Barbería Central</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
:root{--gold:#c5a059;--gold2:#a37f37;--bg:#111315;--panel:#1b1e21;--line:#34383c}
body{background:var(--bg);color:#f4f1eb}.navbar,.card,.modal-content{background:var(--panel);color:#f4f1eb;border:1px solid var(--line)}
h1,h2,h3{color:var(--gold)}.text-muted{color:#b7b7b7!important}.table{--bs-table-bg:var(--panel);--bs-table-color:#f4f1eb;--bs-table-striped-bg:#222629;--bs-table-hover-bg:#292d30;--bs-table-border-color:var(--line);vertical-align:middle}
.form-control,.form-select{background:#121416;color:#fff;border-color:#45494d}.form-control:focus,.form-select:focus{background:#121416;color:#fff;border-color:var(--gold);box-shadow:0 0 0 .2rem #c5a05933}
.btn-gold{background:var(--gold);border-color:var(--gold);color:#151515;font-weight:700}.btn-gold:hover{background:var(--gold2);border-color:var(--gold2);color:white}.gold{color:var(--gold)}.table-responsive{border-radius:.4rem}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg mb-4">
 <div class="container"><a class="navbar-brand gold fw-bold" href="admin.php"><i class="bi bi-scissors me-2"></i>BARBERÍA CENTRAL</a><div class="ms-auto d-flex gap-2"><a class="btn btn-outline-light btn-sm" href="admin.php"><i class="bi bi-speedometer2"></i> Panel</a><a class="btn btn-outline-warning btn-sm" href="logout.php"><i class="bi bi-box-arrow-right"></i> Salir</a></div></div>
</nav>
<main class="container pb-5">
 <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
  <div><div class="text-uppercase small gold">Administración</div><h1 class="h2 mb-1">Gestión de Clientes</h1><p class="text-muted mb-0">Consulta y administra las cuentas registradas.</p></div>
  <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#crearModal"><i class="bi bi-person-plus me-1"></i> Crear cliente</button>
 </div>
 <?php if ($mensaje !== ''): ?><div class="alert alert-<?php echo e($tipoMensaje); ?> alert-dismissible fade show" role="alert"><?php echo e($mensaje); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
 <?php if ($ver): ?>
 <section class="card mb-4"><div class="card-header d-flex justify-content-between"><strong><i class="bi bi-person-vcard me-2 gold"></i>Detalle del cliente #<?php echo (int)$ver['id']; ?></strong><a class="btn-close btn-close-white" href="gestion_clientes.php" aria-label="Cerrar"></a></div><div class="card-body"><div class="row g-3">
 <?php foreach (['Nombre'=>'nombre','Apellido'=>'apellido','Correo electrónico'=>'correo','Teléfono'=>'telefono','Fecha de registro'=>'fecha_registro','Estado'=>'estado_cuenta'] as $label=>$key): ?><div class="col-md-4"><div class="small text-muted"><?php echo e($label); ?></div><div><?php echo e($ver[$key] ?: 'No informado'); ?></div></div><?php endforeach; ?>
 </div><div class="mt-3"><a class="btn btn-gold" href="gestion_clientes.php?editar=<?php echo (int)$ver['id']; ?>"><i class="bi bi-pencil"></i> Editar</a></div></div></section>
 <?php endif; ?>
 <?php if ($editar): ?>
 <section class="card mb-4"><div class="card-header"><strong>Editar cliente #<?php echo (int)$editar['id']; ?></strong></div><div class="card-body">
 <form method="post" class="row g-3"><input type="hidden" name="csrf" value="<?php echo e($csrf); ?>"><input type="hidden" name="accion" value="editar"><input type="hidden" name="id" value="<?php echo (int)$editar['id']; ?>">
 <div class="col-md-6"><label class="form-label">Nombre</label><input class="form-control" name="nombre" maxlength="50" required value="<?php echo e($editar['nombre']); ?>"></div>
 <div class="col-md-6"><label class="form-label">Apellido</label><input class="form-control" name="apellido" maxlength="50" required value="<?php echo e($editar['apellido']); ?>"></div>
 <div class="col-md-6"><label class="form-label">Correo electrónico</label><input class="form-control" type="email" name="correo" maxlength="100" required value="<?php echo e($editar['correo']); ?>"></div>
 <div class="col-md-6"><label class="form-label">Teléfono</label><input class="form-control" name="telefono" maxlength="30" value="<?php echo e($editar['telefono']); ?>"></div>
 <div class="col-12 d-flex gap-2"><button class="btn btn-gold">Guardar cambios</button><a class="btn btn-outline-light" href="gestion_clientes.php">Cancelar</a></div></form>
 </div></section>
 <?php endif; ?>
 <section class="card mb-4"><div class="card-body">
 <form method="get" class="row g-2 align-items-end"><div class="col-md-9"><label class="form-label" for="q">Buscar cliente</label><input class="form-control" id="q" name="q" value="<?php echo e($q); ?>" placeholder="Nombre, apellido, correo o ID"></div><div class="col-md-3 d-grid"><button class="btn btn-outline-warning"><i class="bi bi-search me-1"></i> Buscar</button></div></form>
 </div></section>
 <section class="card"><div class="card-header d-flex justify-content-between align-items-center"><strong>Clientes registrados</strong><span class="badge text-bg-secondary"><?php echo count($clientes); ?> resultado(s)</span></div><div class="table-responsive"><table class="table table-striped table-hover mb-0">
 <thead><tr><th>ID</th><th>Cliente</th><th>Correo</th><th>Teléfono</th><th>Registro</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead><tbody>
 <?php if (!$clientes): ?><tr><td colspan="7" class="text-center py-4 text-muted">No se encontraron clientes.</td></tr><?php else: foreach ($clientes as $c): ?>
 <tr><td><?php echo (int)$c['id']; ?></td><td class="fw-semibold"><?php echo e($c['nombre'].' '.$c['apellido']); ?></td><td><?php echo e($c['correo']); ?></td><td><?php echo e($c['telefono'] ?: '—'); ?></td><td><?php echo e(date('d/m/Y', strtotime($c['fecha_registro']))); ?></td><td><span class="badge <?php echo $c['estado_cuenta']==='activo'?'text-bg-success':'text-bg-secondary'; ?>"><?php echo e(ucfirst($c['estado_cuenta'])); ?></span></td>
 <td><div class="d-flex justify-content-end gap-1 flex-wrap">
 <a class="btn btn-sm btn-outline-info" title="Ver detalles" href="gestion_clientes.php?ver=<?php echo (int)$c['id']; ?>"><i class="bi bi-eye"></i></a>
 <a class="btn btn-sm btn-outline-warning" title="Editar" href="gestion_clientes.php?editar=<?php echo (int)$c['id']; ?>"><i class="bi bi-pencil"></i></a>
 <button class="btn btn-sm btn-outline-light" title="Restablecer contraseña" data-bs-toggle="modal" data-bs-target="#passModal" data-id="<?php echo (int)$c['id']; ?>" data-nombre="<?php echo e($c['nombre'].' '.$c['apellido']); ?>"><i class="bi bi-key"></i></button>
 <form method="post" class="d-inline"><input type="hidden" name="csrf" value="<?php echo e($csrf); ?>"><input type="hidden" name="accion" value="estado"><input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>"><input type="hidden" name="estado_cuenta" value="<?php echo $c['estado_cuenta']==='activo'?'inactivo':'activo'; ?>"><button class="btn btn-sm btn-outline-secondary" title="<?php echo $c['estado_cuenta']==='activo'?'Desactivar':'Activar'; ?>" onclick="return confirm('¿Confirmas <?php echo $c['estado_cuenta']==='activo'?'desactivar':'activar'; ?> esta cuenta?')"><i class="bi <?php echo $c['estado_cuenta']==='activo'?'bi-person-slash':'bi-person-check'; ?>"></i></button></form>
 <form method="post" class="d-inline"><input type="hidden" name="csrf" value="<?php echo e($csrf); ?>"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>"><button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="return confirm('¿Eliminar definitivamente a <?php echo e($c['nombre'].' '.$c['apellido']); ?>? Solo se permite si no tiene citas asociadas.')"><i class="bi bi-trash"></i></button></form>
 </div></td></tr>
 <?php endforeach; endif; ?></tbody></table></div></section>
</main>

<div class="modal fade" id="crearModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form method="post"><div class="modal-header"><h2 class="modal-title fs-5">Crear cliente</h2><button class="btn-close btn-close-white" data-bs-dismiss="modal" type="button"></button></div><div class="modal-body"><input type="hidden" name="csrf" value="<?php echo e($csrf); ?>"><input type="hidden" name="accion" value="crear">
 <div class="mb-3"><label class="form-label">Nombre</label><input class="form-control" name="nombre" maxlength="50" required></div><div class="mb-3"><label class="form-label">Apellido</label><input class="form-control" name="apellido" maxlength="50" required></div><div class="mb-3"><label class="form-label">Correo electrónico</label><input class="form-control" type="email" name="correo" maxlength="100" required></div><div class="mb-3"><label class="form-label">Teléfono (opcional)</label><input class="form-control" name="telefono" maxlength="30"></div><div class="mb-3"><label class="form-label">Contraseña inicial</label><input class="form-control" type="password" name="password" minlength="8" autocomplete="new-password" required><div class="form-text text-muted">Mínimo 8 caracteres. Se almacena como hash.</div></div></div><div class="modal-footer"><button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-gold">Crear cuenta</button></div></form></div></div></div>

<div class="modal fade" id="passModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form method="post" onsubmit="return confirm('¿Confirmas restablecer la contraseña de este cliente?');"><div class="modal-header"><h2 class="modal-title fs-5">Restablecer contraseña</h2><button class="btn-close btn-close-white" data-bs-dismiss="modal" type="button"></button></div><div class="modal-body"><p id="passNombre" class="text-muted"></p><input type="hidden" name="csrf" value="<?php echo e($csrf); ?>"><input type="hidden" name="accion" value="password"><input type="hidden" name="id" id="passId"><div class="mb-3"><label class="form-label">Nueva contraseña</label><input class="form-control" type="password" name="password_nueva" minlength="8" autocomplete="new-password" required></div><div><label class="form-label">Confirmar contraseña</label><input class="form-control" type="password" name="confirmar_password" minlength="8" autocomplete="new-password" required></div><div class="form-text text-muted mt-2">La contraseña actual nunca se muestra.</div></div><div class="modal-footer"><button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-gold">Confirmar cambio</button></div></form></div></div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('passModal').addEventListener('show.bs.modal', function (event) {
 const button = event.relatedTarget;
 document.getElementById('passId').value = button.getAttribute('data-id');
 document.getElementById('passNombre').textContent = 'Cliente: ' + button.getAttribute('data-nombre');
});
</script>
</body></html>
