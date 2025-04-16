<?php
/**
 * API para obtener los detalles del aspirante por su número de solicitud.
 *
 * Este endpoint recibe el parámetro numSolicitud y devuelve los detalles del aspirante en formato JSON.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/obtener_aspirante_por_solicitud.php?numSolicitud=SOL-1631772378
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve los detalles del aspirante.
 * - 400 Bad Request: Si falta el parámetro numSolicitud.
 * - 404 Not Found: Si no se encuentra el aspirante.
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
        'numSolicitud' => isset($_GET['numSolicitud']) ? $_GET['numSolicitud'] : null
    ];
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

if (!$data || !isset($data['numSolicitud'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro numSolicitud es requerido']);
    exit;
}

require_once __DIR__ . '/../../controllers/AspiranteController.php';

$controller = new AspiranteController();
$controller->obtenerAspirantePorSolicitud($data);
?>
