<?php
/**
 * API para enviar solicitud de contacto
 * 
 * Método: POST
 * 
 * Parámetros (JSON):
 * - numero_cuenta_solicitante (string): Número de cuenta del estudiante que envía
 * - numero_cuenta_destino (string): Número de cuenta del estudiante destino
 * - motivo (string, opcional): Motivo de la solicitud
 * 
 * Ejemplo de URL 
 * servidor:puerto/api/post/enviar_solicitud_contacto.php
 * 
 * Métodos soportados:
 *  POST
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

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['numero_cuenta_solicitante']) || empty($input['numero_cuenta_destino'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Números de cuenta requeridos']);
    exit;
}

require_once __DIR__ . '/../../controllers/ContactoController.php';

$controller = new ContactoController();
$result = $controller->enviarSolicitud($input);

http_response_code(isset($result['success']) ? ($result['success'] ? 200 : 400) : 500);
echo json_encode($result);
?>