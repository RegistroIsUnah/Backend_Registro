<?php
/**
 * API para obtener los detalles de una carrera, su coordinador y jefe de departamento.
 *
 * Este endpoint recibe el parámetro carrera_id y devuelve los detalles de la carrera en formato JSON.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/obtener_detalles_carrera.php?carrera_id=1
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve los detalles de la carrera, coordinador y jefe de departamento.
 * - 400 Bad Request: Si falta el parámetro carrera_id.
 * - 404 Not Found: Si no se encuentra la carrera.
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
        'carrera_id' => isset($_GET['carrera_id']) ? $_GET['carrera_id'] : null
    ];
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

if (!$data || !isset($data['carrera_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro carrera_id es requerido']);
    exit;
}

require_once __DIR__ . '/../../controllers/CarreraController.php';

$controller = new CarreraController();
$controller->obtenerDetallesCarrera($data);
?>
