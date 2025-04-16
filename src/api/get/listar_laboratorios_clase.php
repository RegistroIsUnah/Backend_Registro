<?php
/**
 * API para listar los laboratorios asociados a una clase.
 *
 * Este endpoint recibe el parámetro clase_id y devuelve la lista de laboratorios con todos sus detalles.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/listar_laboratorios_clase.php?clase_id=5
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve un arreglo de laboratorios.
 * - 400 Bad Request: Si falta el parámetro clase_id.
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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = [
        'clase_id' => isset($_GET['clase_id']) ? $_GET['clase_id'] : null
    ];
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

if (!$data || !isset($data['clase_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro clase_id es requerido']);
    exit;
}

require_once __DIR__ . '/../../controllers/ClaseController.php';

$controller = new ClaseController();
$controller->listarLaboratorios($data);
?>
