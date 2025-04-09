<?php
/**
 * Cron Job para enviar correos a los estudiantes
 *
 * Este script envia los correos con las credenciales para el sistema
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

function enviarCorreosEstudiantesPendientes() {
    $database = new Database();
    $conn = $database->getConnection();
    
    try {
        // Obtener IDs de estados necesarios
        $pendienteId = obtenerIdEstado($conn, 'PENDIENTE');
        $enviadoId = obtenerIdEstado($conn, 'ENVIADO');
        $fallidoId = obtenerIdEstado($conn, 'FALLIDO');
        
        // 1. Obtener correos pendientes 
        $query = "SELECT * FROM ColaCorreosEstudiantes 
                 WHERE estado_id = ? AND intentos < 3
                 ORDER BY fecha_creacion ASC
                 LIMIT 100";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $pendienteId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $mailSender = new \Mail\MailSender();
        $enviados = 0;
        $fallidos = 0;
        
        // 2. Procesar cada correo
        while ($correo = $result->fetch_assoc()) {
            try {
                // 3. Intentar enviar
                $enviado = $mailSender->sendMail(
                    $correo['destinatario'],
                    $correo['nombre_destinatario'],
                    $correo['asunto'],
                    $correo['cuerpo_html'],
                    $correo['cuerpo_texto']
                );
                
                // 4. Actualizar estado
                $nuevoEstado = $enviado ? $enviadoId : $fallidoId;
                $update = "UPDATE ColaCorreosEstudiantes 
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
                // Contar como fallido
                $fallidos++;
            }
        }
        
        // 5. Salida 
        echo "Enviados: $enviados, Fallidos: $fallidos";
        
    } catch (Exception $e) {
        echo "Error crítico: " . $e->getMessage();
    } finally {
        $conn->close();
    }
}

// Ejecutar
enviarCorreosEstudiantesPendientes();
?>