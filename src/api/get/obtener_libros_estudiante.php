<?php
/**
 * Endpoint para obtener los libros asociados a las clases en las que el estudiante está matriculado o que ya cursó.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/obtener_libros_estudiante.php?estudiante_id=7
 *
 * Responde en formato JSON.
 *
 * Respuestas:
 *  - 200 OK: Devuelve un JSON con las clases y sus libros.
 *  - 400 Bad Request: Si falta el parámetro o es inválido.
 *  - 500 Internal Server Error: Si ocurre un error en la consulta.
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

if (!isset($_GET['estudiante_id']) || !is_numeric($_GET['estudiante_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro estudiante_id es inválido o faltante']);
    exit;
}

$estudiante_id = (int) $_GET['estudiante_id'];

require_once __DIR__ . '/../../controllers/LibroController.php';

$controller = new LibroController();
$controller->obtenerLibrosPorEstudiante($estudiante_id);
?>
