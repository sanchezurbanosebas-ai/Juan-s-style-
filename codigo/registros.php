<?php
require 'conexion.php';
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo = $_POST['correo'];
    $pass = $_POST['password'];

    // Encriptar contraseña (Buena práctica de seguridad)
    $pass_hash = password_hash($pass, PASSWORD_DEFAULT);

    // Consulta preparada para evitar inyección SQL
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellido, correo, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombre, $apellido, $correo, $pass_hash);

    if ($stmt->execute()) {
        $mensaje = "<div class='alert alert-success'>Registro exitoso. <a href='login.php'>Inicia sesión aquí</a></div>";
    } else {
        $mensaje = "<div class='alert alert-danger'>Error al registrar: El correo ya existe.</div>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Barbería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white text-center">
                        <h4>Registro de Cliente</h4>
                    </div>
                    <div class="card-body">
                        <?php echo $mensaje; ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label>Nombre</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Apellido</label>
                                <input type="text" name="apellido" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Correo Electrónico</label>
                                <input type="email" name="correo" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Contraseña</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-dark w-100">Registrarse</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>