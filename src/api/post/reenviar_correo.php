<?php
/**
 * API para reenviar el correo de confirmación a un aspirante por email.
 * 
 * Ejemplo de URL: servidor:puerto/api/post/reenviar_correo.php
 * 
 * Método: POST
 * 
 * Ejemplo de envío en el Body JSON: 
 * { "correo": "ejemplo@dominio.com" }
 *
 * Respuestas:
 * - 200: { "success": true, "message": "Correo reenviado", "correo": "ejemplo@dominio.com" }
 * - 400: { "error": "Correo electrónico requerido", "details": "El campo correo es obligatorio y debe ser válido" }
 * - 404: { "error": "Aspirante no encontrado", "correo": "ejemplo@dominio.com" }
 * - 500: { "error": "Error al reenviar el correo", "details": "Mensaje de error específico" }
 * 
 * @package API
 * @author Ruben Diaz
 * @version 2.0
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

// Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'error' => 'Método no permitido',
        'allowed_methods' => ['POST']
    ]);
    exit;
}

// Obtener datos del cuerpo (soporta JSON y form-data)
$input = json_decode(file_get_contents('php://input'), true);
$data = $input ?? $_POST;

// Validar campo requerido
if (empty($data['correo'])) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Campo requerido',
        'details' => 'El campo correo es obligatorio'
    ]);
    exit;
}

// Pasar el control al controlador
require_once __DIR__ . '/../../controllers/AspiranteController.php';


    $controller = new AspiranteController();
    $controller->reenviarCorreoAction();

