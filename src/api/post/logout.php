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

 $allowedOrigins = [
    'https://www.registroisunah.xyz',
    'https://registroisunah.xyz'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: https://www.registroisunah.xyz");
}

header('Content-Type: application/json');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Credentials: true");

// Manejar solicitud OPTIONS para CORS preflight
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../controllers/AuthController.php';

/*
// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}
*/

// Llamar al controlador
$authController = new AuthController();
$authController->logout();
