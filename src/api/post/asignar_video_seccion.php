<?php
/*
 * API POST para actualizar la URL del video de una sección específica
 * 
 * Método: POST
 * Autenticación requerida: Sí (mismo estudiante o admin)
 * 
 * Respuestas:
 * - 200 OK: URL del video actualizada correctamente
 * - 400 Bad Request: Parámetros inválidos o faltantes
 * - 401 Unauthorized: No autenticado
 * - 403 Forbidden: No autorizado
 * - 404 Not Found: Sección no existe
 * - 500 Internal Server Error: Error en el servidor
 * 
 * @package API
 * @author Jose Vargas
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

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['seccionId']) || !is_numeric($data['seccionId']) || !isset($data['videoUrl']) || empty($data['videoUrl'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros inválidos o faltantes']);
    exit;
}

$seccionId = (int) $data['seccionId'];
$videoUrl = $data['videoUrl'];

require_once __DIR__ . '/../../controllers/SeccionController.php';

$controller = new SeccionController();
$controller->actualizarUrlVideo($seccionId, $videoUrl);
?>