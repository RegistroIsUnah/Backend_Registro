<?php
/**
 * API para obtener las clases de un departamento.
 *
 * Permite filtrar las clases mediante el parámetro GET 'dept_id'.
 * servidor:puerto/api/get/clases_depto.php?dept_id=1
 *
 * Respuestas HTTP:
 * - 200 OK: Retorna la lista de clases en formato JSON.
 * - 400 Bad Request: Si no se proporciona el parámetro 'dept_id'.
 * - 500 Internal Server Error: Si ocurre un error en la consulta.
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

$dept_id = isset($_GET['dept_id']) ? intval($_GET['dept_id']) : null;
if ($dept_id === null) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro dept_id es requerido']);
    exit;
}

require_once __DIR__ . '/../../controllers/ClaseController.php';

$claseController = new ClaseController();
$claseController->getClasesPorDepartamento($dept_id);
?>
