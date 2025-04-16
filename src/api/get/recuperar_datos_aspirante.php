<?php
/**
 * API para obtener los detalles del aspirante por su correo.
 
 * Este endpoint recibe el parámetro documento y devuelve los detalles del aspirante en formato JSON.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/recuperar_datos_aspirante.php?documento=correo
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve los detalles del aspirante.
 * - 400 Bad Request: Si falta el parámetro documento.
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
        'documento' => isset($_GET['documento']) ? $_GET['documento'] : null
    ];
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

if (!$data || !isset($data['documento'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro documento es requerido']);
    exit;
}

require_once __DIR__ . '/../../controllers/AspiranteController.php';

$controller = new AspiranteController();
$controller->obtenerAspirantePorDocumento($data);
?>
