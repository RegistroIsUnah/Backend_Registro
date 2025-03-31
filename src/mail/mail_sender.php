<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Incluir los archivos de PHPMailer desde la carpeta 'PHPMailer-master/src' usando rutas relativas
require_once __DIR__ . '/../modules/config/Environments.php'; 
require_once __DIR__ . '/../PHPMailer-master/src/Exception.php';  // Subir un nivel y acceder a PHPMailer-master/src
require_once __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';   // Subir un nivel y acceder a PHPMailer-master/src
require_once __DIR__ . '/../PHPMailer-master/src/SMTP.php';        // Subir un nivel y acceder a PHPMailer-master/src

/**
 * Función para enviar correos electrónicos.
 * 
 * @param string $to Correo destinatario.
 * @param string $nameto Nombre del destinatario.
 * @param string $subject Asunto del correo.
 * @param string $message Cuerpo del correo en HTML.
 * @param string $altmess Cuerpo alternativo en texto plano.
 * 
 * @return bool Devuelve true si el correo se envió correctamente, false en caso de error.
 */
function sendmail($to, $nameto, $subject, $message, $altmess) {
    // Cargar las configuraciones del archivo .env usando la clase Environments
    $env = Environments::read();  // Lee las variables del archivo .env

    // Configuración de los parámetros SMTP y del remitente
    $from  = $env['MAIL_FROM'];  // Correo remitente
    $namefrom = $env['MAIL_NAME'];  // Nombre del remitente
    $smtpHost = $env['SMTP_HOST'];  // Servidor SMTP de Namecheap
    $smtpUsername = $env['SMTP_USERNAME'];  // Usuario SMTP
    $smtpPassword = $env['SMTP_PASSWORD'];  // La contraseña de tu correo
    $smtpPort = $env['SMTP_PORT'];  // Puerto SMTP para SSL
    $smtpSecure = $env['SMTP_SECURE'];  // Tipo de seguridad

    // Crear una instancia de PHPMailer
    $mail = new PHPMailer();
    $mail->SMTPDebug = 0;  // Desactivar depuración (puedes poner 2 si quieres ver más detalles)
    $mail->CharSet = 'UTF-8';  // Configurar codificación

    // Configuración SMTP
    $mail->isSMTP();  // Usar SMTP
    $mail->SMTPAuth   = true;  // Autenticación SMTP
    $mail->Host       = $smtpHost;  // Servidor SMTP
    $mail->Port       = $smtpPort;  // Puerto SMTP
    $mail->Username   = $smtpUsername;  // Usuario SMTP
    $mail->Password   = $smtpPassword;  // Contraseña SMTP
    $mail->SMTPSecure = $smtpSecure;  // Tipo de seguridad (ssl o tls)

    // Establecer remitente y dirección
    $mail->setFrom($from, $namefrom);
    $mail->addAddress($to, $nameto);  // Dirección de destino

    // Contenido del correo
    $mail->Subject = $subject;
    $mail->isHTML(true);  // Formato HTML
    $mail->Body    = $message;
    $mail->AltBody = $altmess;  // Cuerpo alternativo para clientes que no soportan HTML

    // Enviar correo
    if ($mail->send()) {
        return true;  // Correo enviado exitosamente
    } else {
        // En caso de error, devolver el mensaje de error
        return false;  // Error al enviar el correo
    }
}
?>
