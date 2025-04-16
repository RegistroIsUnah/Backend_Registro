<?php
/**
 * API para solicitar restablecimiento de contraseña.
 *
 * Método: POST
 *
 * Parámetros (JSON):
 *  - email (string): Correo electrónico del usuario.
 * 
 * Ejemplo de URL: 
 * servidor:puerto/api/post/request_password_reset.php
 *
 * @package API
 * @version 1.0
 * @author Ruben Diaz
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

if (empty($input['email'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'El email es requerido']);
    exit;
}

require_once __DIR__ . '/../../controllers/PasswordResetController.php';

try {
    $controller = new PasswordResetController();
    $controller->requestReset();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}