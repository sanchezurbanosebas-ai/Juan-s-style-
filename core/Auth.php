<?php
// core/Auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Auth {
    // Verificar si hay sesión activa
    public static function check() {
        return isset($_SESSION['user_id']);
    }

    // Obtener datos del usuario actual
    public static function user() {
        if (self::check()) {
            return [
                'id' => $_SESSION['user_id'],
                'nombre' => $_SESSION['user_nombre'],
                'email' => $_SESSION['user_email'],
                'rol' => $_SESSION['user_rol']
            ];
        }
        return null;
    }

    // Exigir que sea Administrador
    public static function requireAdmin() {
        if (!self::check() || $_SESSION['user_rol'] !== 'admin') {
            header('Location: index.php?page=login&error=acceso_denegado');
            exit();
        }
    }

    // Exigir que esté autenticado (Cliente o Admin)
    public static function requireLogin() {
        if (!self::check()) {
            header('Location: index.php?page=login&error=requiere_login');
            exit();
        }
    }

    // Iniciar sesión guardando datos esenciales y regenerando ID de sesión
    public static function login($usuario) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_nombre'] = $usuario['nombre'];
        $_SESSION['user_email'] = $usuario['email'];
        $_SESSION['user_rol'] = $usuario['rol'];
    }

    // Cierre seguro de sesión
    public static function logout() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
}
?>