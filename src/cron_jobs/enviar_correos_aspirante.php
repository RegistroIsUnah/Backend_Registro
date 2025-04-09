<?php
/**
 * Cron Job para enviar correos a aspirantes
 *
 * Este script envia los correos con las notas de los examenes a los aspirantes
 *
 * @package CronJobs
 * @author Ruben Diaz
 * @version 1.0
 */

require_once __DIR__ . '/../modules/config/DataBase.php';
require_once __DIR__ . '/../mail/mail_sender.php';

function obtenerIdEstado($conn, $nombreEstado) {
    $query = "SELECT estado_id FROM EstadoCorreo WHERE nombre = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nombreEstado);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if (!$row) {
        throw new Exception("Estado '$nombreEstado' no encontrado");
    }
    
    return $row['estado_id'];
}

function enviarCorreosPendientes() {
    $database = new Database();
    $conn = $database->getConnection();
    
    try {
        // Obtener IDs de estados necesarios
        $pendienteId = obtenerIdEstado($conn, 'PENDIENTE');
        $enviadoId = obtenerIdEstado($conn, 'ENVIADO');
        $fallidoId = obtenerIdEstado($conn, 'FALLIDO');
        
        // 1. Obtener correos pendientes
        $query = "SELECT c.* 
                 FROM ColaCorreosAspirantes c
                 WHERE c.estado_id = ? AND c.intentos < 3
                 ORDER BY c.fecha_creacion ASC
                 LIMIT 100";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $pendienteId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $mailSender = new \Mail\MailSender();
        $enviados = 0;
        $fallidos = 0;
        
        while ($correo = $result->fetch_assoc()) {
            try {
                // 2. Intentar enviar el correo
                $enviado = $mailSender->sendMail(
                    $correo['destinatario'],
                    $correo['nombre_destinatario'],
                    $correo['asunto'],
                    $correo['cuerpo_html'],
                    $correo['cuerpo_texto']
                );
                
                // 3. Actualizar estado
                $nuevoEstado = $enviado ? $enviadoId : $fallidoId;
                $update = "UPDATE ColaCorreosAspirantes 
                          SET estado_id = ?, 
                              fecha_envio = IF(?, NOW(), NULL),
                              intentos = intentos + 1,
                              ultimo_error = IF(?, NULL, 'Error en el envío')
                          WHERE correo_id = ?";
                
                $stmtUpdate = $conn->prepare($update);
                $stmtUpdate->bind_param("iiii", 
                    $nuevoEstado,
                    $enviado,
                    $enviado,
                    $correo['correo_id']
                );
                $stmtUpdate->execute();
                $stmtUpdate->close();
                
                $enviado ? $enviados++ : $fallidos++;
                
            } catch (Exception $e) {
                // Registrar error pero continuar
                error_log("Error enviando correo ID {$correo['correo_id']}: " . $e->getMessage());
                $fallidos++;
            }
        }
        
        echo "Proceso completado. Enviados: $enviados, Fallidos: $fallidos";
        
    } catch (Exception $e) {
        echo "Error en el proceso: " . $e->getMessage();
    } finally {
        $conn->close();
    }
}

enviarCorreosPendientes();
?>