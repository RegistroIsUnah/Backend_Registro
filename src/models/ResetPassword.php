<?php
require_once __DIR__ . '/../modules/config/DataBase.php';
require_once __DIR__ . '/../mail/mail_sender.php';

class ResetPassword {
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;

    /**
     * Constructor de la clase ResetPassword.
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Crea una solicitud de restablecimiento de contraseña.
     * 
     * @param int $usuario_id ID del usuario
     * @param string $token Token único
     * @return bool True si se creó correctamente
     */
    public function createResetRequest($usuario_id, $token) {
        try {
            $expiration = date('Y-m-d H:i:s', strtotime('+2 minutes'));
            
            $query = "INSERT INTO ResetPasswordRequest 
                      (usuario_id, token, fecha_solicitud, fecha_expiracion, estado_password_id) 
                      VALUES (?, ?, NOW(), ?, 1)";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en preparación de consulta: " . $this->conn->error);
            }

            $stmt->bind_param("iss", $usuario_id, $token, $expiration);
            $result = $stmt->execute();
            $stmt->close();

            return $result;
        } catch (Exception $e) {
            throw new Exception("Error al crear solicitud de restablecimiento: " . $e->getMessage());
        }
    }

    /**
     * Valida un token de restablecimiento.
     * 
     * @param string $token Token a validar
     * @return array|false Datos del token o false si no es válido
     */
    public function validateToken($token) {
        try {
            $query = "SELECT r.*, u.username 
                      FROM ResetPasswordRequest r
                      JOIN Usuario u ON r.usuario_id = u.usuario_id
                      WHERE r.token = ? AND r.estado_password_id = 1 
                      AND r.fecha_expiracion > NOW()";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en preparación de consulta: " . $this->conn->error);
            }

            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            $stmt->close();

            return $data ?: false;
        } catch (Exception $e) {
            throw new Exception("Error al validar token: " . $e->getMessage());
        }
    }

    /**
     * Marca un token como usado.
     * 
     * @param string $token Token a marcar
     * @return bool True si se actualizó correctamente
     */
    public function markTokenAsUsed($token) {
        try {
            $query = "UPDATE ResetPasswordRequest 
                      SET estado_password_id = 2 
                      WHERE token = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en preparación de consulta: " . $this->conn->error);
            }

            $stmt->bind_param("s", $token);
            $result = $stmt->execute();
            $stmt->close();

            return $result;
        } catch (Exception $e) {
            throw new Exception("Error al marcar token como usado: " . $e->getMessage());
        }
    }

    /**
     * Obtiene usuario por email.
     * 
     * @param string $email Correo electrónico
     * @return array|false Datos del usuario o false si no existe
     */
    public function getUserByEmail($email) {
        try {
            // Busca en estudiantes
            $query = "SELECT e.estudiante_id, u.usuario_id, u.username, 
                             CONCAT(e.nombre, ' ', e.apellido) as nombre_completo, e.correo_personal as correo
                      FROM Estudiante e
                      JOIN Usuario u ON e.usuario_id = u.usuario_id
                      WHERE e.correo_personal = ?
                      
                      UNION
                      
                      SELECT d.docente_id, u.usuario_id, u.username, 
                             CONCAT(d.nombre, ' ', d.apellido) as nombre_completo, d.correo as correo
                      FROM Docente d
                      JOIN Usuario u ON d.usuario_id = u.usuario_id
                      WHERE d.correo = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en preparación de consulta: " . $this->conn->error);
            }

            $stmt->bind_param("ss", $email, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            return $user ?: false;
        } catch (Exception $e) {
            throw new Exception("Error al buscar usuario por email: " . $e->getMessage());
        }
    }

    /**
     * Actualiza la contraseña de un usuario.
     * 
     * @param int $usuario_id ID del usuario
     * @param string $new_password Nueva contraseña (sin encriptar)
     * @return bool True si se actualizó correctamente
     */
    public function updatePassword($usuario_id, $new_password) {
        try {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            
            $query = "UPDATE Usuario SET password = ? WHERE usuario_id = ?";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en preparación de consulta: " . $this->conn->error);
            }

            $stmt->bind_param("si", $hashed_password, $usuario_id);
            $result = $stmt->execute();
            $stmt->close();

            return $result;
        } catch (Exception $e) {
            throw new Exception("Error al actualizar contraseña: " . $e->getMessage());
        }
    }

    /**
     * Crea una solicitud de restablecimiento y envía el correo.
     * 
     * @param string $email Correo del usuario
     * @return bool True si se creó y envió correctamente
     */
    public function createAndSendResetRequest($email) {
        try {
            // Buscar usuario por email
            $user = $this->getUserByEmail($email);
            
            if (!$user) {
                throw new Exception('No se encontró una cuenta asociada a este correo');
            }
            
            // Generar token único
            $token = bin2hex(random_bytes(32));
            
            // Crear solicitud en la base de datos
            $expiration = date('Y-m-d H:i:s', strtotime('+2 minutes'));
            $query = "INSERT INTO ResetPasswordRequest 
                      (usuario_id, token, fecha_solicitud, fecha_expiracion, estado_password_id) 
                      VALUES (?, ?, NOW(), ?, 1)";
            
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error en preparación de consulta: " . $this->conn->error);
            }

            $stmt->bind_param("iss", $user['usuario_id'], $token, $expiration);
            if (!$stmt->execute()) {
                throw new Exception('Error al crear la solicitud de restablecimiento');
            }
            $stmt->close();
            
            // Enviar correo electrónico
            $resetUrl = "https://tudominio.com/reset-password?token=$token";
            $this->sendResetEmail($user['correo'], $user['nombre_completo'], $resetUrl);
            
            return true;
        } catch (Exception $e) {
            error_log("Error en createAndSendResetRequest: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envía el correo electrónico de restablecimiento de contraseña.
     * 
     * @param string $email Correo electrónico del destinatario
     * @param string $name Nombre completo del destinatario
     * @param string $resetUrl URL única para restablecer la contraseña
     * @return bool True si el correo se envió correctamente, False en caso contrario
     */
    private function sendResetEmail($email, $name, $resetUrl) 
    {
        $subject = 'Restablecimiento de contraseña';
        
        // Cuerpo del mensaje en formato HTML
        $html = <<<HTML
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Restablecimiento de Contraseña</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    line-height: 1.6;
                    color: #333333;
                    margin: 0;
                    padding: 0;
                }
                .container { 
                    max-width: 600px; 
                    margin: 0 auto; 
                    padding: 20px;
                    border: 1px solid #eeeeee;
                    border-radius: 5px;
                }
                .header {
                    text-align: center;
                    padding-bottom: 10px;
                    border-bottom: 1px solid #eeeeee;
                    margin-bottom: 20px;
                }
                .button {
                    display: inline-block;
                    padding: 12px 24px;
                    background-color: #007bff;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                    margin: 20px 0;
                    font-weight: bold;
                }
                .footer {
                    margin-top: 30px;
                    padding-top: 15px;
                    border-top: 1px solid #eeeeee;
                    font-size: 0.9em;
                    color: #666666;
                }
                .important {
                    color: #dc3545;
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Restablecimiento de Contraseña</h2>
                </div>
                <p>Hola <strong>{$name}</strong>,</p>
                <p>Hemos recibido una solicitud para restablecer tu contraseña.</p>
                <p>Para continuar con el proceso, haz clic en el siguiente enlace:</p>
                <div style="text-align: center;">
                    <a href="{$resetUrl}" class="button">Restablecer contraseña</a>
                </div>
                <p class="important">Este enlace expirará en 2 minutos.</p>
                <p>Si no solicitaste este cambio, puedes ignorar este mensaje y tu cuenta permanecerá segura.</p>
                <div class="footer">
                    <p>Saludos,<br>El equipo de soporte</p>
                </div>
            </div>
        </body>
        </html>
    HTML;
        
        // Versión de texto plano para clientes de correo que no soportan HTML
        $text = "Hola {$name},\n\n"
            . "Hemos recibido una solicitud para restablecer tu contraseña.\n\n"
            . "Para continuar con el proceso, visita el siguiente enlace:\n"
            . "{$resetUrl}\n\n"
            . "IMPORTANTE: Este enlace expirará en 2 minutos.\n\n"
            . "Si no solicitaste este cambio, puedes ignorar este mensaje y tu cuenta permanecerá segura.\n\n"
            . "Saludos,\nEl equipo de soporte";
        
        // Instanciar el servicio de envío de correos y enviar el mensaje
        $mailSender = new \Mail\MailSender();
        return $mailSender->sendMail($email, $name, $subject, $html, $text);
    }

}
?>