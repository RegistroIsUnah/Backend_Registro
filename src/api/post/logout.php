<?php
/**
 * API para el cierre de sesión de usuarios.
 *
 * Este script recibe la petición POST y llama a AuthController::logout().
 *
 * Métodos soportados:
 * - `POST`: Finaliza la sesión activa.
 *
 * Respuestas HTTP:
 * - `200 OK`: Cierre de sesión exitoso.
 * - `405 Method Not Allowed`: Método HTTP no permitido.
 * 
 * Ejemplo Respuesta
 * 
 * {
 *   "message": "Cierre de sesión exitoso"
 * }
 * 
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../controllers/AuthController.php';

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Llamar al controlador
$authController = new AuthController();
$authController->logout();
