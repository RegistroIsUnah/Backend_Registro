<?php
/**
 * API para modificar una sección.
 *
 * Este endpoint recibe datos en formato JSON, valida la información y llama al controlador para modificar la sección.
 *
 * Ejemplo de URL 
 * servidor:puerto/api/post/modificar_seccion.php
 * 
 * Métodos soportados:
 *  POST
 * 
 * Ejemplo de JSON de entrada:
 * {
 *   "seccion_id": 15,                     // Requerido
 *   "docente_id": 3,                      // Opcional
 *   "aula_id": 5,                         // Opcional
 *   "estado": "CANCELADA",                // Opcional; 'ACTIVA' o 'CANCELADA'
 *   "motivo_cancelacion": "Razón...",     // Requerido si estado es 'CANCELADA'
 *   "cupos": 30,                          // Opcional
 *   "video_url": "https://...",           // Opcional
 *   "hora_inicio": "08:00:00",            // Opcional (formato HH:MM:SS)
 *   "hora_fin": "10:00:00",               // Opcional (formato HH:MM:SS)
 *   "dias": "1,3,5"                       // Opcional (números de días separados por comas, ej: 1=Lunes)
 * }
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve un mensaje de éxito.
 * - 400 Bad Request: Datos faltantes o formato inválido.
 * - 500 Internal Server Error: Error durante la modificación.
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(["error" => "Datos JSON inválidos"]);
    exit;
}

require_once __DIR__ . '/../../controllers/SeccionController.php';

$seccionController = new SeccionController();
$seccionController->modificarSeccion($input);
?>
