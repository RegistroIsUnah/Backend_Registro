<?php
/**
 * API para obtener los mensajes de un chat
 * 
 * Método: GET
 * 
 * Parámetros (query string):
 * - chat_id (int): ID del chat
 * - numero_cuenta (string): Número de cuenta del estudiante
 * - limit (int, opcional): Límite de mensajes a obtener (default: 50)
 * - offset (int, opcional): Offset para paginación (default: 0)
 * 
 * Ejemplo de URL:
 * servidor:puerto/api/get/obtener_mensajes.php?chat_id=1&numero_cuenta=20241000001&limit=20
 * 
 * Métodos soportados:
 * GET
 *
 * @package API
 * @author Ruben Diaz
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

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

if (empty($_GET['chat_id']) || empty($_GET['numero_cuenta'])) {
    http_response_code(400);
    echo json_encode(['error' => 'chat_id y numero_cuenta son requeridos']);
    exit;
}

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

require_once __DIR__ . '/../../controllers/ChatController.php';

try {
    $controller = new ChatController();
    $result = $controller->obtenerMensajes(
        $_GET['chat_id'],
        $_GET['numero_cuenta'],
        $limit,
        $offset
    );
    
    http_response_code(isset($result['error']) ? 400 : 200);
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>