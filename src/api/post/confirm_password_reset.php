<?php
/**
 * API para confirmar restablecimiento de contraseña.
 *
 * Método: POST
 *
 * Parámetros (JSON):
 *  - token (string): Token de restablecimiento.
 *  - password (string): Nueva contraseña.
 * 
 * Ejemplo de URL: 
 * servidor:puerto/api/post/confirm_password_reset.php
 *
 * @package API
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
// Obtener datos JSON
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos no proporcionados']);
    exit;
}

if (empty($input['token']) || empty($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Token y nueva contraseña son requeridos']);
    exit;
}

if (strlen($input['password']) < 8) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'La contraseña debe tener al menos 8 caracteres']);
    exit;
}

require_once __DIR__ . '/../../controllers/PasswordResetController.php';

try {
    $controller = new PasswordResetController();
    $controller->confirmReset();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}