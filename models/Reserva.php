<?php
// models/Reserva.php
require_once __DIR__ . '/../config/conexion.php';

class ReservaModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Validar si Juan González tiene libre un rango de tiempo específico
    public function estaDisponible($barbero_id, $fecha_hora_inicio, $fecha_hora_fin) {
        $sql = "SELECT COUNT(*) FROM reservas 
                WHERE barbero_id = :barbero_id 
                AND estado IN ('pendiente', 'confirmada')
                AND (
                    (fecha_hora_inicio < :fin AND fecha_hora_fin > :inicio)
                )";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':barbero_id' => $barbero_id,
            ':inicio' => $fecha_hora_inicio,
            ':fin' => $fecha_hora_fin
        ]);

        return $stmt->fetchColumn() == 0;
    }

    // Crear una nueva reserva con manejo de transacciones
    public function crearReserva($cliente_id, $barbero_id, $servicio_id, $fecha_hora_inicio, $duracion_minutos, $precio) {
        try {
            $this->pdo->beginTransaction();

            // Calcular fecha y hora de finalización
            $inicio = new DateTime($fecha_hora_inicio);
            $fin = clone $inicio;
            $fin->modify("+{$duracion_minutos} minutes");

            $strInicio = $inicio->format('Y-m-d H:i:s');
            $strFin = $fin->format('Y-m-d H:i:s');

            // Verificar disponibilidad con bloqueo/transacción
            if (!$this->estaDisponible($barbero_id, $strInicio, $strFin)) {
                $this->pdo->rollBack();
                return ['success' => false, 'message' => 'El horario seleccionado ya no está disponible con Juan González.'];
            }

            // Insertar la reserva
            $sql = "INSERT INTO reservas (cliente_id, barbero_id, servicio_id, fecha_hora_inicio, fecha_hora_fin, precio_acordado, estado) 
                    VALUES (:cliente_id, :barbero_id, :servicio_id, :inicio, :fin, :precio, 'pendiente')";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':cliente_id' => $cliente_id,
                ':barbero_id' => $barbero_id,
                ':servicio_id' => $servicio_id,
                ':inicio' => $strInicio,
                ':fin' => $strFin,
                ':precio' => $precio
            ]);

            $reserva_id = $this->pdo->lastInsertId();
            $this->pdo->commit();

            return ['success' => true, 'reserva_id' => $reserva_id, 'message' => '¡Cita reservada con éxito!'];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => 'Error al procesar la reserva: ' . $e->getMessage()];
        }
    }
}
?>