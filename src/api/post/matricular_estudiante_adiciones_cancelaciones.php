<?php
/**
 * API para matricular a un estudiante en el proceso de Adiciones y Cancelaciones.
 * 
 * Ejemplo de URL 
 * servidor:puerto/api/post/matricular_estudiante_adiciones
 * 
 * Metodos soportados:
 *  POST
 *
 * Este endpoint recibe datos en formato JSON, por ejemplo:
 * {
 *   "estudiante_id": 10,
 *   "seccion_id": 15,
 *   "tipo_proceso": "ADICIONES_CANCELACIONES",
 *   "lab_seccion_id": 0
 * }
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve los datos resultantes de la matrícula (matricula_id, estado, orden_inscripcion, etc.).
 * - 400 Bad Request: Datos faltantes o inválidos.
 * - 500 Internal Server Error: Error durante la matriculación.
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
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos JSON inválidos']);
    exit;
}

require_once __DIR__ . '/../../controllers/MatriculaAdicionesController.php';

$controller = new MatriculaController();
$controller->matricularEstudianteAdiciones($input);
?>
