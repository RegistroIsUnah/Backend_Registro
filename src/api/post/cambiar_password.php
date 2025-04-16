<?php
/**
 * API PUT para cambiar la contraseña de un usuario
 * 
 * Método: PUT
 * Autenticación requerida: Sí (usuario autenticado)
 * Content-Type: application/json
 * 
 * Parámetros (JSON):
 * - password_actual (string) - Requerido
 * - nueva_password (string) - Requerido
 * - confirmar_password (string) - Requerido
 * 
 * Respuestas:
 * - 200 OK: Contraseña actualizada exitosamente
 * - 400 Bad Request: Error en los parámetros o validación
 * - 401 Unauthorized: No autenticado
 * - 403 Forbidden: No autorizado
 * - 500 Internal Server Error: Error en el servidor
 * 
 * Ejemplo de solicitud:
 * {
 *   "password_actual": "passwordgenericavieja",
 *   "nueva_password": "passwordgenericanueva",
 *   "confirmar_password": "passwordgenericanueva"
 * }
 * 
 * Ejemplo de respuesta exitosa:
 * {
 *   "success": true,
 *   "message": "Contraseña actualizada exitosamente"
 * }
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

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(["error" => "Datos JSON inválidos"]);
    exit;
}

require_once __DIR__ . '/../../controllers/UsuarioController.php';

$usuarioontroller = new UsuarioController();
$usuarioontroller ->cambiarPassword($input);

?>
