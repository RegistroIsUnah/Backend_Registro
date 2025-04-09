<?php
/**
 * Cron Job para enviar correos a aspirantes - Versión 3.0
 * 
 * @package CronJobs
 * @author Ruben Diaz
 * @version 3.0
 */

require_once __DIR__ . '/../modules/config/DataBase.php';
require_once __DIR__ . '/../mail/mail_sender.php';

function obtenerIdEstado($conn, $nombreEstado) {
    $query = "SELECT estado_id FROM EstadoCorreo WHERE nombre = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("Error preparando consulta: " . $conn->error);
        throw new Exception("Error de base de datos");
    }
    
    $stmt->bind_param("s", $nombreEstado);
    
    if (!$stmt->execute()) {
        error_log("Error ejecutando consulta: " . $stmt->error);
        throw new Exception("Error al obtener estado");
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Estado '$nombreEstado' no encontrado");
    }
    
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row['estado_id'];
}

function enviarCorreosPendientes() {
    $database = new Database();
    $conn = $database->getConnection();
    
    try {
        // 1. Obtener IDs de estados
        $pendienteId = obtenerIdEstado($conn, 'PENDIENTE');
        $enviadoId = obtenerIdEstado($conn, 'ENVIADO');
        $fallidoId = obtenerIdEstado($conn, 'FALLIDO');
        
        // 2. Obtener correos pendientes Y fallidos con menos de 3 intentos
        $query = "SELECT c.* FROM ColaCorreosAspirantes c
                 WHERE (c.estado_id = ? OR c.estado_id = ?) 
                 AND c.intentos < 3
                 ORDER BY 
                   CASE WHEN c.estado_id = ? THEN 0 ELSE 1 END,
                   c.fecha_creacion ASC
                 LIMIT 100";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Error preparando consulta: " . $conn->error);
        }
        
        $stmt->bind_param("iii", $pendienteId, $fallidoId, $pendienteId);
        
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando consulta: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $totalCorreos = $result->num_rows;
        
        if ($totalCorreos === 0) {
            echo "No hay correos pendientes o fallidos con menos de 3 intentos";
            return;
        }
        
        $mailSender = new \Mail\MailSender();
        $enviados = 0;
        $fallidos = 0;
        
        while ($correo = $result->fetch_assoc()) {
            try {
                // Intentar enviar el correo
                $enviado = $mailSender->sendMail(
                    $correo['destinatario'],
                    $correo['nombre_destinatario'],
                    $correo['asunto'],
                    $correo['cuerpo_html'],
                    $correo['cuerpo_texto']
                );
                
                // Actualizar estado
                $nuevoEstado = $enviado ? $enviadoId : $fallidoId;
                $errorInfo = $enviado ? NULL : substr($mailSender->getLastError(), 0, 255);
                
                $update = "UPDATE ColaCorreosAspirantes 
                          SET estado_id = ?, 
                              fecha_envio = IF(?, NOW(), NULL),
                              intentos = intentos + 1,
                              ultimo_error = ?
                          WHERE correo_id = ?";
                
                $stmtUpdate = $conn->prepare($update);
                $stmtUpdate->bind_param(
                    "iisi",
                    $nuevoEstado,
                    $enviado,
                    $errorInfo,
                    $correo['correo_id']
                );
                $stmtUpdate->execute();
                $stmtUpdate->close();
                
                $enviado ? $enviados++ : $fallidos++;
                
                // Pequeña pausa entre correos
                sleep(1);
                
            } catch (Exception $e) {
                error_log("Error procesando correo ID {$correo['correo_id']}: " . $e->getMessage());
                $fallidos++;
                
                // Registrar error
                $updateError = "UPDATE ColaCorreosAspirantes 
                              SET intentos = intentos + 1,
                                  ultimo_error = ?
                              WHERE correo_id = ?";
                
                $stmtError = $conn->prepare($updateError);
                $errorMsg = substr($e->getMessage(), 0, 255);
                $stmtError->bind_param("si", $errorMsg, $correo['correo_id']);
                $stmtError->execute();
                $stmtError->close();
            }
        }
        
        echo "Proceso completado. Enviados: $enviados, Fallidos: $fallidos";
        
    } catch (Exception $e) {
        error_log("Error crítico: " . $e->getMessage());
        echo "Error en el proceso: " . $e->getMessage();
    } finally {
        if (isset($stmt)) $stmt->close();
        $conn->close();
    }
}

// Ejecutar el proceso
enviarCorreosPendientes();