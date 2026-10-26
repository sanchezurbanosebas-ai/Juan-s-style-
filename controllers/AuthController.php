<?php
// controllers/AuthController.php
require_once __DIR__ . '/../core/Auth.php';

class AuthController {

    // Procesar el inicio de sesión
    public static function loginProcess($pdo) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                header('Location: index.php?page=login&error=campos_vacios');
                exit();
            }

            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($password, $usuario['password'])) {
                Auth::login($usuario);
                if ($usuario['rol'] === 'admin') {
                    header('Location: index.php?page=admin_dashboard');
                } else {
                    header('Location: index.php?page=home');
                }
                exit();
            } else {
                header('Location: index.php?page=login&error=credenciales_invalidas');
                exit();
            }
        }
    }

    // Procesar el registro público de nuevos clientes
    public static function registroProcess($pdo) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($nombre) || empty($email) || empty($password)) {
                header('Location: index.php?page=registro&error=campos_vacios');
                exit();
            }

            // Verificar si el correo ya existe
            $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
            $stmtCheck->execute([':email' => $email]);
            if ($stmtCheck->rowCount() > 0) {
                header('Location: index.php?page=registro&error=correo_existente');
                exit();
            }

            // Encriptar contraseña de forma segura
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            // Insertar usuario con rol estricto 'cliente'
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, telefono, password, rol) VALUES (:nombre, :email, :telefono, :password, 'cliente')");
            $resultado = $stmt->execute([
                ':nombre' => $nombre,
                ':email' => $email,
                ':telefono' => $telefono,
                ':password' => $passwordHash
            ]);

            if ($resultado) {
                header('Location: index.php?page=login&success=registrado');
                exit();
            } else {
                header('Location: index.php?page=registro&error=bd_error');
                exit();
            }
        }
    }
}
?>