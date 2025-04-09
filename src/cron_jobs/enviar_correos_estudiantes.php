<?php
/**
 * Cron Job para enviar correos a los estudiantes 
 * 
 * @package CronJobs
 * @author Ruben Diaz
 * @version 2.1
 */

require_once __DIR__ . '/../modules/config/DataBase.php';
require_once __DIR__ . '/../mail/mail_sender.php';

function obtenerIdEstado($conn, $nombreEstado) {
    $query = "SELECT estado_id FROM EstadoCorreo WHERE nombre = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nombreEstado);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Estado no encontrado");
    }
    
    return $result->fetch_assoc()['estado_id'];
}

function enviarCorreosEstudiantesPendientes() {
    $database = new Database();
    $conn = $database->getConnection();
    
    try {
        // Obtener IDs de estados necesarios
        $pendienteId = obtenerIdEstado($conn, 'PENDIENTE');
        $enviadoId = obtenerIdEstado($conn, 'ENVIADO');
        $fallidoId = obtenerIdEstado($conn, 'FALLIDO');
        
        // Consulta para obtener correos pendientes y fallidos
        $query = "SELECT * FROM ColaCorreosEstudiantes 
                 WHERE (estado_id = ? OR estado_id = ?)
                 AND intentos < 3
                 ORDER BY 
                   CASE WHEN estado_id = ? THEN 0 ELSE 1 END,
                   fecha_creacion ASC
                 LIMIT 100";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iii", $pendienteId, $fallidoId, $pendienteId);
        $stmt->execute();
        $result = $stmt->get_result();
        
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
                
                $update = "UPDATE ColaCorreosEstudiantes 
                          SET estado_id = ?, 
                              fecha_envio = IF(?, NOW(), NULL),
                              intentos = intentos + 1,
                              ultimo_error = ?
                          WHERE correo_id = ?";
                
                $stmtUpdate = $conn->prepare($update);
                $stmtUpdate->bind_param("iisi", $nuevoEstado, $enviado, $errorInfo, $correo['correo_id']);
                $stmtUpdate->execute();
                $stmtUpdate->close();
                
                $enviado ? $enviados++ : $fallidos++;
                
                sleep(1); // Pausa entre correos
                
            } catch (Exception $e) {
                $fallidos++;
                
                // Registrar error mínimo en base de datos
                $updateError = "UPDATE ColaCorreosEstudiantes 
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
        
        // Solo muestra resumen básico
        echo "Proceso completado. Enviados: $enviados, Fallidos: $fallidos";
        
    } catch (Exception $e) {
        echo "Error en el proceso: " . $e->getMessage();
    } finally {
        if (isset($stmt)) $stmt->close();
        $conn->close();
    }
}

// Ejecutar el proceso
enviarCorreosEstudiantesPendientes();