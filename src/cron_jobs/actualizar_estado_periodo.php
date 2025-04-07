<?php
/**
 * Cron Job para actualizar el estado de PeriodoAcademico a INACTIVO
 * cuando la fecha_fin ha pasado y el estado actual es ACTIVO
 */

require_once __DIR__ . '/../modules/config/DataBase.php';

function actualizarEstadoPeriodo() {
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
        
        // 2. Actualizar los periodos vencidos
        $queryUpdate = "UPDATE PeriodoAcademico pa
                       JOIN EstadoProceso ep ON pa.estado_proceso_id = ep.estado_proceso_id
                       SET pa.estado_proceso_id = ?
                       WHERE pa.fecha_fin <= NOW() 
                       AND ep.nombre = 'ACTIVO'";
        
        $stmt = $conn->prepare($queryUpdate);
        $stmt->bind_param("i", $estadoInactivoId);
        
        if ($stmt->execute()) {
            echo "Periodos actualizados a INACTIVO: " . $stmt->affected_rows;
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

actualizarEstadoPeriodo();
?>