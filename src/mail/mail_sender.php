<?php

namespace Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Incluir los archivos de PHPMailer desde la carpeta 'PHPMailer-master/src' usando rutas relativas
require_once __DIR__ . '/../modules/config/Environments.php'; 
require_once __DIR__ . '/../PHPMailer-master/src/Exception.php';  // Subir un nivel y acceder a PHPMailer-master/src
require_once __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';   // Subir un nivel y acceder a PHPMailer-master/src
require_once __DIR__ . '/../PHPMailer-master/src/SMTP.php';        // Subir un nivel y acceder a PHPMailer-master/src



class MailSender {

    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer();
        $this->configureMailer();
    }

    private function configureMailer() {
        // Cargar las configuraciones de entorno
        $env = \Environments::read();  // Lee las variables del archivo .env
        $this->mail->Host = $env['SMTP_HOST'];
        $this->mail->Username = $env['SMTP_USERNAME'];
        $this->mail->Password = $env['SMTP_PASSWORD'];
        $this->mail->SMTPSecure = $env['SMTP_SECURE'];
        $this->mail->Port = $env['SMTP_PORT'];

        // Remitente
        $this->mail->setFrom($env['MAIL_FROM'], $env['MAIL_NAME']);
        $this->mail->CharSet = 'UTF-8';
        $this->mail->SMTPDebug = 0;  // Si necesitas depuración, usa 2
    }

    /**
     * Envia un correo
     *
     * @param string $to Correo destinatario
     * @param string $nameto Nombre del destinatario
     * @param string $subject Asunto del correo
     * @param string $message Cuerpo del correo en HTML
     * @param string $altmess Cuerpo alternativo en texto plano
     * @return bool
     */
    public function sendMail($to, $nameto, $subject, $message, $altmess) {
        try {
            // Establecer el destinatario
            $this->mail->addAddress($to, $nameto);

            // Asunto
            $this->mail->Subject = $subject;
            
            // Cuerpo del correo
            $this->mail->isHTML(true);
            $this->mail->Body    = $message;
            $this->mail->AltBody = $altmess;

            // Enviar el correo
            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: " . $this->mail->ErrorInfo);
            return false;
        }
    }
}
?>
