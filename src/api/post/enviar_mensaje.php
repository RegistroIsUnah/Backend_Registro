<?php
/**
 * API para enviar un mensaje a un chat
 * 
 * Método: POST
 * 
 * Parámetros (form-data o JSON):
 * - chat_id (int): ID del chat
 * - numero_cuenta (string): Número de cuenta del remitente
 * - contenido (string, opcional): Contenido del mensaje
 * - archivos (file[], opcional): Archivos adjuntos
 * 
 * Ejemplo de URL:
 * servidor:puerto/api/post/enviar_mensaje.php
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

// Obtener datos (pueden venir como JSON o form-data)
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

if (empty($input['chat_id']) || empty($input['numero_cuenta'])) {
    http_response_code(400);
    echo json_encode(['error' => 'chat_id y numero_cuenta son requeridos']);
    exit;
}

if (empty($input['contenido']) && empty($_FILES['archivos'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El mensaje no puede estar vacío']);
    exit;
}

require_once __DIR__ . '/../../controllers/ChatController.php';

try {
    $controller = new ChatController();
    $result = $controller->enviarMensaje(
        $input['chat_id'],
        $input['numero_cuenta'],
        $input['contenido'] ?? '',
        $_FILES['archivos'] ?? []
    );
    
    http_response_code($result['success'] ? 200 : 400);
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>