<?php

require_once __DIR__ . '/../modules/config/DataBase.php';
require_once __DIR__ . '/../mail/mail_sender.php';

/**
 * Clase Contacto
 *
 * Maneja la interacción con la tabla Contacto de la base de datos.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class Contacto {
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;
    
     /**
     * Constructor de la clase Departamento.
     *
     * Establece la conexión con la base de datos.
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Obtiene el ID de estudiante por número de cuenta
     */
    private function obtenerIdPorNumeroCuenta($numeroCuenta) {
        $query = "SELECT estudiante_id FROM Estudiante WHERE numero_cuenta = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $numeroCuenta);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        $row = $result->fetch_assoc();
        return $row['estudiante_id'];
    }
    
    /**
     * Envía una solicitud de contacto a otro estudiante
     * 
     * @param string $solicitanteNumeroCuenta Número de cuenta del estudiante que envía
     * @param string $destinoNumeroCuenta Número de cuenta del estudiante destino
     * @param string|null $motivo Motivo de la solicitud (opcional)
     * @return array Resultado de la operación
     */
    public function enviarSolicitudContacto($solicitanteNumeroCuenta, $destinoNumeroCuenta, $motivo = null) {
        try {
            // Obtener ID del solicitante
            $solicitanteId = $this->obtenerIdPorNumeroCuenta($solicitanteNumeroCuenta);
            if (!$solicitanteId) {
                return ['success' => false, 'error' => 'Solicitante no encontrado'];
            }
            
            // Verificar si el estudiante destino existe
            $query = "SELECT estudiante_id, nombre, apellido, correo_personal 
                      FROM Estudiante 
                      WHERE numero_cuenta = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $destinoNumeroCuenta);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return ['success' => false, 'error' => 'Estudiante no encontrado'];
            }
            
            $destino = $result->fetch_assoc();
            $destinoId = $destino['estudiante_id'];
            
            // Verificar si ya son contactos
            $query = "SELECT 1 FROM Contacto 
                      WHERE estudiante_id = ? AND contacto_estudiante_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $solicitanteId, $destinoId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return ['success' => false, 'error' => 'Ya son contactos'];
            }
            
            // Verificar si ya existe una solicitud pendiente
            $query = "SELECT 1 FROM SolicitudContacto 
                      WHERE estudiante_solicitante = ? AND estudiante_destino = ? 
                      AND estado_solicitud_contacto_id = 1"; // 1 = PENDIENTE
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $solicitanteId, $destinoId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return ['success' => false, 'error' => 'Ya existe una solicitud pendiente'];
            }
            
            // Insertar la nueva solicitud con estado_solicitud_contacto_id = 1 (Pendiente)
            $query = "INSERT INTO SolicitudContacto (
                          estudiante_solicitante, estudiante_destino, 
                          estado_solicitud_contacto_id, fecha_solicitud, motivo
                      ) VALUES (?, ?, 1, NOW(), ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("iis", $solicitanteId, $destinoId, $motivo);
            $stmt->execute();
            
            // Obtener información del solicitante para el correo
            $query = "SELECT nombre, apellido, numero_cuenta 
                      FROM Estudiante 
                      WHERE estudiante_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $solicitanteId);
            $stmt->execute();
            $result = $stmt->get_result();
            $solicitante = $result->fetch_assoc();
            
            // Enviar notificación por correo
            $this->enviarNotificacionCorreo($solicitante, $destino);
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Responde a una solicitud de contacto (aceptar o rechazar)
     * 
     * @param int $solicitudId ID de la solicitud
     * @param string $destinoNumeroCuenta Número de cuenta del estudiante que responde
     * @param bool $aceptar True para aceptar, false para rechazar
     * @return array Resultado de la operación
     */
    public function responderSolicitudContacto($solicitudId, $destinoNumeroCuenta, $aceptar) {
        try {
            // Obtener ID del destino
            $destinoId = $this->obtenerIdPorNumeroCuenta($destinoNumeroCuenta);
            if (!$destinoId) {
                return ['success' => false, 'error' => 'Estudiante no encontrado'];
            }
            
            $this->conn->begin_transaction();
            
            // Verificar que la solicitud existe y está pendiente
            $query = "SELECT estudiante_solicitante 
                      FROM SolicitudContacto 
                      WHERE solicitud_id = ? AND estudiante_destino = ? 
                      AND estado_solicitud_contacto_id = 1"; // 1 = PENDIENTE
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $solicitudId, $destinoId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $this->conn->rollback();
                return ['success' => false, 'error' => 'Solicitud no encontrada o ya procesada'];
            }
            
            $solicitud = $result->fetch_assoc();
            $solicitanteId = $solicitud['estudiante_solicitante'];
            
            // Actualizar estado de la solicitud (2 = ACEPTADA, 3 = RECHAZADA)
            $nuevoEstado = $aceptar ? 2 : 3;
            $query = "UPDATE SolicitudContacto 
                      SET estado_solicitud_contacto_id = ? 
                      WHERE solicitud_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $nuevoEstado, $solicitudId);
            $stmt->execute();
            
            // Si se acepta, crear relación de contacto en ambas direcciones
            if ($aceptar) {
                $query = "INSERT INTO Contacto (estudiante_id, contacto_estudiante_id) 
                          VALUES (?, ?), (?, ?)";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("iiii", $solicitanteId, $destinoId, $destinoId, $solicitanteId);
                $stmt->execute();
            }
            
            $this->conn->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Obtiene las solicitudes pendientes de un estudiante
     * 
     * @param string $numeroCuenta Número de cuenta del estudiante
     * @return array Lista de solicitudes pendientes
     */
    public function obtenerSolicitudesPendientes($numeroCuenta) {
        try {
            // Obtener ID del estudiante
            $estudianteId = $this->obtenerIdPorNumeroCuenta($numeroCuenta);
            if (!$estudianteId) {
                return ['error' => 'Estudiante no encontrado'];
            }
            
            $query = "SELECT sc.solicitud_id, e.estudiante_id, e.numero_cuenta, 
                             e.nombre, e.apellido, sc.fecha_solicitud, sc.motivo, esc.nombre AS estado
                      FROM SolicitudContacto sc
                      JOIN Estudiante e ON sc.estudiante_solicitante = e.estudiante_id
                      JOIN EstadoSolicitudContacto esc ON sc.estado_solicitud_contacto_id = esc.estado_solicitud_contacto_id
                      WHERE sc.estudiante_destino = ? AND sc.estado_solicitud_contacto_id = (Select estado_solicitud_contacto_id from EstadoSolicitudContacto where nombre = 'Pendiente')
                      ORDER BY sc.fecha_solicitud DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $estudianteId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $solicitudes = [];
            while ($row = $result->fetch_assoc()) {
                $solicitudes[] = $row;
            }
            
            return $solicitudes;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Envía una notificación por correo sobre la solicitud de contacto
     * 
     * @param array $solicitante Datos del estudiante solicitante
     * @param array $destino Datos del estudiante destino
     */
    private function enviarNotificacionCorreo($solicitante, $destino, $motivo = '') {
        $asunto = "Solicitud de contacto en el sistema UNAH";
        
        // Mensaje HTML
        $mensajeHTML = '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Solicitud de Contacto</title>
            <style>
                body {
                    font-family: "Segoe UI", Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    background-color: #f7f7f7;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 20px auto;
                    padding: 0;
                    border: 1px solid #e0e0e0;
                    border-radius: 8px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    background-color: #ffffff;
                }
                .header {
                    background: linear-gradient(135deg, #0056b3 0%, #007bff 100%);
                    color: white;
                    padding: 25px 20px;
                    text-align: center;
                    border-radius: 8px 8px 0 0;
                }
                .header h1 {
                    margin: 0;
                    font-size: 24px;
                    font-weight: 600;
                    letter-spacing: 0.5px;
                }
                .content {
                    padding: 30px 25px;
                    font-size: 16px;
                }
                .info-box {
                    background-color: #f1f8ff;
                    border-left: 4px solid #007bff;
                    padding: 15px;
                    margin: 20px 0;
                    border-radius: 0 4px 4px 0;
                }
                .button-container {
                    text-align: center;
                    margin: 30px 0 20px;
                }
                .button {
                    display: inline-block;
                    background: linear-gradient(to bottom, #28b463, #24a259);
                    color: white;
                    padding: 12px 25px;
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: bold;
                    transition: all 0.3s;
                    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                }
                .button:hover {
                    background: linear-gradient(to bottom, #2ecc71, #28b463);
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
                }
                .student-info {
                    margin: 15px 0;
                }
                .student-info strong {
                    color: #0056b3;
                }
                .footer {
                    margin-top: 30px;
                    padding: 20px;
                    font-size: 0.9em;
                    color: #777;
                    text-align: center;
                    background-color: #f7f7f7;
                    border-top: 1px solid #e0e0e0;
                    border-radius: 0 0 8px 8px;
                }
                .logo {
                    margin-bottom: 15px;
                }
                .logo img {
                    height: 60px;
                }
                @media only screen and (max-width: 480px) {
                    .container {
                        width: 95%;
                        margin: 10px auto;
                    }
                    .content {
                        padding: 20px 15px;
                    }
                    .header {
                        padding: 20px 15px;
                    }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div class="logo">
                        <!-- Aquí puedes añadir un logo de la UNAH si lo tienes disponible -->
                        <!-- <img src="logo-unah.png" alt="Logo UNAH"> -->
                    </div>
                    <h1>Solicitud de Contacto</h1>
                </div>
                <div class="content">
                    <p>Hola <strong>' . htmlspecialchars($destino['nombre']) . '</strong>,</p>
                    
                    <div class="info-box">
                        <p class="student-info">El estudiante <strong>' . htmlspecialchars($solicitante['nombre']) . ' ' . htmlspecialchars($solicitante['apellido']) . '</strong> 
                        (Cuenta: <strong>' . htmlspecialchars($solicitante['numero_cuenta']) . '</strong>) te ha enviado una solicitud de contacto.</p>';
        
        if (!empty($motivo)) {
            $mensajeHTML .= '<p><strong>Motivo de la solicitud:</strong> ' . htmlspecialchars($motivo) . '</p>';
        }
        
        $mensajeHTML .= '
                    </div>
                    
                    <p>Por favor inicia sesión en el sistema para revisar y responder a esta solicitud a la brevedad posible.</p>
                    
                    <div class="button-container">
                        <a href="https://registroisunah.xyz/login.php" class="button">Revisar Solicitud</a>
                    </div>
                    
                    <p>Si tienes problemas para acceder, copia y pega este enlace en tu navegador:</p>
                    <p><a href="https://registroisunah.xyz/login.php">https://registroisunah.xyz/login.php</a></p>
                </div>
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' Universidad Nacional Autónoma de Honduras</p>
                    <p>Sistema de Registro Académico</p>
                </div>
            </div>
        </body>
        </html>';
        
        // Mensaje de texto plano
        $mensajeTexto = "Hola " . $destino['nombre'] . ",\n\n";
        $mensajeTexto .= "El estudiante " . $solicitante['nombre'] . " " . $solicitante['apellido'] . " ";
        $mensajeTexto .= "(cuenta: " . $solicitante['numero_cuenta'] . ") te ha enviado una solicitud de contacto.\n\n";
        
        if (!empty($motivo)) {
            $mensajeTexto .= "Motivo: " . $motivo . "\n\n";
        }
        
        $mensajeTexto .= "Por favor inicia sesión en el sistema para aceptar o rechazar esta solicitud:\n";
        $mensajeTexto .= "https://registroisunah.xyz/login.php\n\n";
        $mensajeTexto .= "© " . date('Y') . " Universidad Nacional Autónoma de Honduras - Sistema de Registro Académico";
        
        // Usar PHPMailer para enviar el correo
        $emailService = new \Mail\MailSender();
        $result = $emailService->sendMail(
            $destino['correo_personal'],
            $destino['nombre'] . ' ' . $destino['apellido'],
            $asunto,
            $mensajeHTML,
            $mensajeTexto
        );
        
        if (!$result) {
            error_log("Error al enviar notificación de contacto a " . $destino['correo_personal']);
        }
    }

    /**
     * Obtiene todos los contactos de un estudiante
     * @param string $numeroCuenta Número de cuenta del estudiante
     * @return array Lista de contactos
     */
    public function obtenerContactos($numeroCuenta) {
        try {
            $estudianteId = $this->obtenerIdPorNumeroCuenta($numeroCuenta);
            if (!$estudianteId) {
                return ['error' => 'Estudiante no encontrado'];
            }

            $query = "SELECT 
                        e.estudiante_id,
                        e.numero_cuenta,
                        e.nombre,
                        e.apellido,
                        e.correo_personal,
                        (SELECT COUNT(*) FROM Mensaje m 
                        JOIN ChatParticipante cp ON m.chat_id = cp.chat_id 
                        WHERE cp.estudiante_id = e.estudiante_id 
                        AND m.estudiante_id = c.contacto_estudiante_id 
                        AND m.fecha_envio > IFNULL((SELECT ultima_vista FROM ChatParticipante 
                                                WHERE chat_id = cp.chat_id 
                                                AND estudiante_id = e.estudiante_id), '1970-01-01')) AS mensajes_sin_leer
                    FROM Contacto c
                    JOIN Estudiante e ON c.contacto_estudiante_id = e.estudiante_id
                    WHERE c.estudiante_id = ?
                    ORDER BY e.nombre, e.apellido";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $estudianteId);
            $stmt->execute();
            $result = $stmt->get_result();

            $contactos = [];
            while ($row = $result->fetch_assoc()) {
                $contactos[] = $row;
            }

            return $contactos;
        } catch (Exception $e) {
            error_log("Error al obtener contactos: " . $e->getMessage());
            return ['error' => 'Error al obtener contactos'];
        }
    }

    /**
     * Elimina un contacto mutuo entre dos estudiantes
     * @param string $numeroCuenta Número de cuenta del estudiante que elimina
     * @param string $contactoNumeroCuenta Número de cuenta del contacto a eliminar
     * @return array Resultado de la operación
     */
    public function eliminarContacto($numeroCuenta, $contactoNumeroCuenta) {
        $this->conn->begin_transaction();
        
        try {
            $estudianteId = $this->obtenerIdPorNumeroCuenta($numeroCuenta);
            $contactoId = $this->obtenerIdPorNumeroCuenta($contactoNumeroCuenta);

            if (!$estudianteId || !$contactoId) {
                $this->conn->rollback();
                return ['success' => false, 'error' => 'Estudiante o contacto no encontrado'];
            }

            // Eliminar ambas direcciones de la relación
            $query = "DELETE FROM Contacto 
                    WHERE (estudiante_id = ? AND contacto_estudiante_id = ?)
                    OR (estudiante_id = ? AND contacto_estudiante_id = ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("iiii", $estudianteId, $contactoId, $contactoId, $estudianteId);
            $stmt->execute();

            // Opcional: Eliminar chats privados existentes
            $this->eliminarChatsPrivados($estudianteId, $contactoId);

            $this->conn->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error al eliminar contacto: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error al eliminar contacto'];
        }
    }

    /**
     * Elimina chats privados entre dos estudiantes (opcional)
     */
    private function eliminarChatsPrivados($estudianteId1, $estudianteId2) {
        // Obtener chats privados entre estos dos estudiantes
        $query = "SELECT c.chat_id 
                FROM Chat c
                JOIN ChatParticipante cp1 ON c.chat_id = cp1.chat_id
                JOIN ChatParticipante cp2 ON c.chat_id = cp2.chat_id
                WHERE c.es_grupal = 0
                AND cp1.estudiante_id = ?
                AND cp2.estudiante_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $estudianteId1, $estudianteId2);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $chatId = $row['chat_id'];
            
            // Eliminar mensajes del chat
            $this->conn->query("DELETE FROM Mensaje WHERE chat_id = $chatId");
            // Eliminar participantes del chat
            $this->conn->query("DELETE FROM ChatParticipante WHERE chat_id = $chatId");
            // Eliminar el chat
            $this->conn->query("DELETE FROM Chat WHERE chat_id = $chatId");
        }
    }
}
?>