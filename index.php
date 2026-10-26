<?php
// index.php
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/config/conexion.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

switch ($page) {
    case 'home':
        $titulo = "Inicio - Juan'Style";
        require_once __DIR__ . '/views/layout/header.php';
        require_once __DIR__ . '/views/home.php';
        require_once __DIR__ . '/views/layout/footer.php';
        break;

    case 'login':
        if (Auth::check()) {
            header('Location: index.php?page=' . (Auth::user()['rol'] === 'admin' ? 'admin_dashboard' : 'mis_reservas'));
            exit();
        }
        require_once __DIR__ . '/views/layout/header.php';
        require_once __DIR__ . '/views/login.php';
        require_once __DIR__ . '/views/layout/footer.php';
        break;

    case 'registro':
        if (Auth::check()) {
            header('Location: index.php');
            exit();
        }
        require_once __DIR__ . '/views/layout/header.php';
        require_once __DIR__ . '/views/registro.php';
        require_once __DIR__ . '/views/layout/footer.php';
        break;

    case 'procesar_login':
        require_once __DIR__ . '/controllers/AuthController.php';
        AuthController::loginProcess($pdo);
        break;

    case 'procesar_registro':
        require_once __DIR__ . '/controllers/AuthController.php';
        AuthController::registroProcess($pdo);
        break;

    // --- PROCESAMIENTO DE RESERVAS (INCLUIDO Y CORREGIDO) ---
    case 'procesar_reserva':
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $servicio_id = (int)$_POST['servicio_id'];
            $fecha = $_POST['fecha'];
            $hora = $_POST['hora'];
            $cliente_id = Auth::user()['id'];

            if (empty($servicio_id) || empty($fecha) || empty($hora)) {
                header('Location: index.php?page=home&error=campos_vacios#reservar');
                exit();
            }

            $fecha_inicio = $fecha . ' ' . $hora . ':00';

            try {
                // Consultar precio del servicio seleccionado
                $stmtServ = $pdo->prepare("SELECT precio FROM servicios WHERE id = :id");
                $stmtServ->execute([':id' => $servicio_id]);
                $precio = $stmtServ->fetchColumn();

                if (!$precio) {
                    header('Location: index.php?page=home&error=servicio_invalido#reservar');
                    exit();
                }

                // Insertar la cita en la tabla reservas (barbero_id = 1 para Juan González)
                $stmt = $pdo->prepare("INSERT INTO reservas (cliente_id, barbero_id, servicio_id, fecha_hora_inicio, precio_acordado, estado) VALUES (:cliente, 1, :servicio, :fecha_inicio, :precio, 'pendiente')");
                $stmt->execute([
                    ':cliente' => $cliente_id,
                    ':servicio' => $servicio_id,
                    ':fecha_inicio' => $fecha_inicio,
                    ':precio' => $precio
                ]);

                // Redirigir de inmediato a la vista de Mis Citas con éxito
                header('Location: index.php?page=mis_reservas&reserva_exito=1');
                exit();
            } catch (PDOException $e) {
                die("Error al procesar la reserva: " . $e->getMessage());
            }
        }
        break;

    case 'logout':
        Auth::logout();
        header('Location: index.php?page=home');
        exit();
        break;

    case 'mis_reservas':
        Auth::requireLogin();
        require_once __DIR__ . '/views/layout/header.php';
        require_once __DIR__ . '/views/mis_reservas.php';
        require_once __DIR__ . '/views/layout/footer.php';
        break;

    case 'admin_dashboard':
        Auth::requireAdmin();
        require_once __DIR__ . '/views/layout/header.php';
        require_once __DIR__ . '/views/admin/dashboard.php';
        require_once __DIR__ . '/views/layout/footer.php';
        break;

    default:
        http_response_code(404);
        require_once __DIR__ . '/views/layout/header.php';
        echo '<div class="container py-5 text-center"><h1>404</h1><p>Página no encontrada en Juan’Style.</p><a href="index.php" class="btn btn-gold">Volver al inicio</a></div>';
        require_once __DIR__ . '/views/layout/footer.php';
        break;
}
?>