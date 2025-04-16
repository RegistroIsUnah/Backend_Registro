<?php
/**
 * API para asignar un usuario a un docente.
 *
 * Recibe los datos en formato JSON:
 * {
 *   "docente_id": 1,
 *   "username": "docente1",
 *   "password": "clave123"
 * }
 *
 * Respuestas:
 * - 200 OK: Retorna { "mensaje": "Credenciales correctamente asignadas" }
 * - 400/500: Error en la asignación.
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
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['docente_id']) || !isset($input['username']) || !isset($input['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan datos requeridos']);
    exit;
}

require_once __DIR__ . '/../../controllers/DocenteController.php';

$docenteController = new DocenteController();
$docenteController->asignarUsuarioDocente($input['docente_id'], $input['username'], $input['password']);
?>
