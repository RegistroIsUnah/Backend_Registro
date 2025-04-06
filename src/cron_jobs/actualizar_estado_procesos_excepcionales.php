<?php
/**
 * Cron Job para actualizar el estado de ProcesosExcepcionales.
 *
 * Este script actualiza el estado_proceso_id de las entradas en la tabla ProcesosExcepcionales
 * al estado correspondiente a 'INACTIVO' cuando la fecha_fin es menor que la fecha actual
 * y el estado actual es 'ACTIVO'.
 *
 * @package CronJobs
 * @author Ruben Diaz
 * @version 2.0
 */

require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Actualiza el estado de los procesos excepcionales.
 *
 * Se conecta a la base de datos y ejecuta la consulta para actualizar el estado_proceso_id
 * a INACTIVO para procesos cuya fecha de finalización ya ha pasado y están actualmente ACTIVOS.
 *
 * @return void
 */
function actualizarEstadoProcesosExcepcionales() {
    $database = new Database();
    $conn = $database->getConnection();
    
    try {
        // 1. Obtener el ID del estado INACTIVO
        $queryEstado = "SELECT estado_proceso_id FROM EstadoProceso WHERE nombre = 'INACTIVO' LIMIT 1";
        $result = $conn->query($queryEstado);
        
        if ($result->num_rows === 0) {
            throw new Exception("Estado INACTIVO no encontrado en la tabla EstadoProceso");
        }
        
        $row = $result->fetch_assoc();
        $estadoInactivoId = $row['estado_proceso_id'];
        
        // 2. Actualizar los procesos vencidos
        $queryUpdate = "UPDATE ProcesosExcepcionales pe
                       JOIN EstadoProceso ep ON pe.estado_proceso_id = ep.estado_proceso_id
                       SET pe.estado_proceso_id = ?
                       WHERE pe.fecha_fin < NOW() 
                       AND ep.nombre = 'ACTIVO'";
        
        $stmt = $conn->prepare($queryUpdate);
        $stmt->bind_param("i", $estadoInactivoId);
        
        if ($stmt->execute()) {
            echo "Procesos excepcionales actualizados a INACTIVO: " . $stmt->affected_rows;
        } else {
            throw new Exception("Error al actualizar: " . $stmt->error);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    } finally {
        $conn->close();
    }
}

actualizarEstadoProcesosExcepcionales();
?>