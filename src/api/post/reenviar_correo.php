<?php
/**
 * API para reenviar el correo de confirmación a un aspirante.
 * 
 * Ejemplo de URL 
 * servidor:puerto/api/post/reenviar_correo
 * 
 * Método: POST
 * 
 * Ejemplo de envio en el Body Json 
 * { "numSolicitud": "SOL-GZA93FJ74Q" }
 *
 * Respuestas:
 * - 200: { "success": true, "message": "Correo reenviado" }
 * - 400: { "error": "Número de solicitud requerido" }
 * - 404: { "error": "Aspirante no encontrado" }
 * - 500: { "error": "Error al reenviar el correo" }
 * 
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

 header('Content-Type: application/json');

 //Validar método HTTP
 if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
     http_response_code(405);
     echo json_encode(['error' => 'Método no permitido']);
     exit;
 }
 
 //Pasar el control al controlador
 require_once __DIR__ . '/../../controllers/AspiranteController.php';
 
 try {
     $controller = new AspiranteController();
     $controller->reenviarCorreoAction();
 } catch (Exception $e) {
     // Solo para errores inesperados (ej: el controlador no existe)
     http_response_code(500);
     echo json_encode(['error' => 'Error interno del servidor']);
 }