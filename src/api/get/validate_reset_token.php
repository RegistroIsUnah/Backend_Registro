<?php
/**
 * API para validar token de restablecimiento de contraseña.
 *
 * Método: GET
 *
 * Parámetros (query string):
 *  - token (string): Token de restablecimiento.
 * 
 * Ejemplo de URL: 
 * servidor:puerto/api/get/validate_reset_token.php?token=ABC123
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

if (empty($_GET['token'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Token no proporcionado']);
    exit;
}

require_once __DIR__ . '/../../controllers/PasswordResetController.php';

try {
    $controller = new PasswordResetController();
    $controller->validateToken();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}