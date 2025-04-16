<?php
/**
 * API para desasociar un examen de una carrera.
 *
 * Método: POST
 *
 * Parámetros (en form-data o JSON):
 *  - examen_id (int): ID del examen a desasociar.
 *  - carrera_id (int): ID de la carrera de la cual se desasociará el examen.
 * 
 * Ejemplo de URL 
 * servidor:puerto/api/post/desasociar_examen_carrera.php
 * 
 * Métodos soportados:
 *  POST
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.1
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

// Permitir recibir datos JSON
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
 
if (empty($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos no proporcionados']);
    exit;
}
 
if (empty($input['examen_ids']) || empty($input['carrera_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El array examen_ids y carrera_id son requeridos.']);
    exit;
}

require_once __DIR__ . '/../../controllers/TipoExamenController.php';

$examenController = new TipoExamenController();
$examenController->desasociarExamenesDeCarrera($input['examen_ids'], $input['carrera_id']);
?>
