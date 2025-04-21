<?php
/**
 * API para el inicio de sesión de usuarios.
 *
 * Este script recibe la petición POST, valida los datos y llama a AuthController::login().
 *
 * Métodos soportados:
 * - `POST`: Requiere un JSON con `username` y `password`.
 *
 * Respuestas HTTP:
 * - `200 OK`: Inicio de sesión exitoso.
 * - `400 Bad Request`: Faltan datos en la solicitud.
 * - `401 Unauthorized`: Credenciales incorrectas.
 * - `405 Method Not Allowed`: Método HTTP no permitido.
 * 
 * Ejemplo envio de datos
 * 
 * {
 * "username": "docente2",
 * "password": "docente345"
 * }
 * 
 * Ejemplo respuesta
 * 
 * {
 *   "token": "s50un5803hculv2boajbb3lr58",
 *   "user": {
 *       "id": 7,
 *       "username": "docente2",
 *       "roles": [
 *           "docente",
 *           "coordinador"
 *       ],
 *       "details": {
 *           "docente": {
 *               "docente_id": 2,
 *               "nombre": "Alex",
 *               "apellido": "Diaz",
 *               "correo": "alex.diaz@unah.hn",
 *               "foto": "alex.jpg"
 *           }
 *       }
 *   },
 *   "message": "Inicio de sesión exitoso"
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


// Obtener datos del JSON
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['username']) || !isset($input['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan datos de usuario o contraseña']);
    exit;
}

// Llamar al controlador
$authController = new AuthController();
$authController->login($input['username'], $input['password']);
?>
