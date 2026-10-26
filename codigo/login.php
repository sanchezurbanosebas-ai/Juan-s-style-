<?php
session_start();
require_once 'conexion.php';
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST['correo']);
    $pass = trim($_POST['password']);
    $rol_seleccionado = $_POST['rol_acceso']; // 'cliente' o 'admin'

    // Consultar usuario por correo y rol
    $stmt = $conn->prepare("SELECT id, nombre, password, rol FROM usuarios WHERE correo = ? AND rol = ?");
    $stmt->bind_param("ss", $correo, $rol_seleccionado);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        
        if (password_verify($pass, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol'];

            // Redirección por rol
            if ($usuario['rol'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: agendar.php");
            }
            exit();
        } else {
            $mensaje = "<div class='alert alert-danger'>Contraseña incorrecta.</div>";
        }
    } else {
        $mensaje = "<div class='alert alert-danger'>Credenciales no encontradas para el tipo de usuario seleccionado.</div>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - Barbería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow border-0">
                    <div class="card-header bg-dark text-white text-center py-3">
                        <h4 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Iniciar Sesión</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php echo $mensaje; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tipo de Usuario</label>
                                <select name="rol_acceso" class="form-select" required>
                                    <option value="cliente" selected>Soy Cliente</option>
                                    <option value="admin">Soy Administrador</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Correo Electrónico</label>
                                <input type="email" name="correo" class="form-control" placeholder="ejemplo@correo.com" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Contraseña</label>
                                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                            </div>

                            <button type="submit" class="btn btn-dark w-100 py-2 mt-2">Ingresar al Sistema</button>
                        </form>

                        <hr class="my-4">

                        <div class="text-center">
                            <p class="mb-0 text-muted">¿Eres cliente nuevo?</p>
                            <a href="registro.php" class="btn btn-outline-primary btn-sm mt-1">Crear una cuenta de Cliente</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>