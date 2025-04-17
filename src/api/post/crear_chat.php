<?php
/**
 * API para crear un nuevo chat (individual o grupal)
 * 
 * Método: POST
 * 
 * Parámetros (JSON):
 * - es_grupal (bool): True para chat grupal, false para individual
 * - nombre (string, opcional): Nombre del chat (requerido para grupos)
 * - participantes (array): Lista de números de cuenta de los participantes
 * 
 * Ejemplo de URL:
 * servidor:puerto/api/post/crear_chat.php
 * 
 * Métodos soportados:
 * POST
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

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Obtener datos JSON
$input = json_decode(file_get_contents('php://input'), true);

// Validar datos básicos
if (empty($input['es_grupal']) || empty($input['participantes'])) {
    http_response_code(400);
    echo json_encode(['error' => 'es_grupal y participantes son requeridos']);
    exit;
}

if ($input['es_grupal'] && empty($input['nombre'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El nombre es requerido para chats grupales']);
    exit;
}

require_once __DIR__ . '/../../controllers/ChatController.php';

try {
    $controller = new ChatController();
    $result = $controller->crearChat(
        $input['es_grupal'],
        $input['nombre'] ?? null,
        $input['participantes']
    );
    
    http_response_code($result['success'] ? 200 : 400);
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>