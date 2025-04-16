<?php
/**
 * API para rechazar una solicitud
 * 
 * Método: POST
 * 
 * Campos requeridos:
 * - solicitud_id: int (ID de la solicitud)
 * - motivo: string (Motivo del rechazo)
 * 
 * Ejemplo:
 * POST /api/post/rechazar_solicitud.php
 * Content-Type: application/json
 *
 * {
 *     "solicitud_id": 456,
 *     "motivo": "Documentación incompleta"
 * }
 *
 * @package API
 * @author [Tu Nombre]
 * @version 1.0
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

require_once __DIR__ . '/../../controllers/SolicitudController.php';

$data = json_decode(file_get_contents('php://input'), true);

try {
    $controller = new SolicitudController();
    $controller->rechazarSolicitud($data);
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>