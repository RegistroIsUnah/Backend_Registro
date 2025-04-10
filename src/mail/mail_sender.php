<?php
namespace Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../modules/config/Environments.php'; 
require_once __DIR__ . '/../PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer-master/src/SMTP.php';

class MailSender {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        
        $env = \Environments::read();
        
        // Configuración SMTP mejorada
        $this->mail->isSMTP();
        $this->mail->Host = $env['SMTP_HOST'];
        $this->mail->Username = $env['SMTP_USERNAME'];
        $this->mail->Password = $env['SMTP_PASSWORD'];
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL explícito
        $this->mail->Port = 465;
        $this->mail->SMTPAuth = true;
        
        // Configuración de tiempo de espera
        $this->mail->Timeout = 30;
        $this->mail->SMTPKeepAlive = true;
        
        // Configuración de remitente
        $this->mail->setFrom($env['MAIL_FROM'], $env['MAIL_NAME']);
        $this->mail->CharSet = 'UTF-8';
        $this->mail->SMTPDebug = SMTP::DEBUG_OFF; // Cambiar a DEBUG_SERVER para diagnóstico
    }

    public function sendMail($to, $nameto, $subject, $message, $altmess) {
        try {
            // Limpiar destinatarios previos
            $this->mail->clearAddresses();
            $this->mail->clearReplyTos();
            
            // Configurar destinatario
            $this->mail->addAddress($to, $nameto);
            $this->mail->Subject = $subject;
            $this->mail->Body = $message;
            $this->mail->AltBody = $altmess ?: strip_tags($message);
            $this->mail->isHTML(true);
            
            // Intento de envío
            $sent = $this->mail->send();
            
            // Reiniciar conexión después de cada envío
            $this->mail->smtpClose();
            
            return $sent;
        } catch (Exception $e) {
            error_log("Mailer Error [To: $to]: " . $this->mail->ErrorInfo);
            $this->mail->smtpClose();
            return false;
        }
    }

    public function getLastError() {
        return $this->mail->ErrorInfo;
    }

    public function setDebug($level) {
        $this->mail->SMTPDebug = $level;
        if ($level > 0) {
            $this->mail->Debugoutput = function($str, $level) {
                error_log("SMTP Debug (Nivel $level): $str");
            };
        }
    }
}
?>