<?php
/**
 * API para asignar roles a un usuario.
 *
 * Se espera recibir vía POST (multipart/form-data o x-www-form-urlencoded):
 *   - usuario_id: int
 *   - roles: JSON string (ej. [1,2,3])
 * 
 * Ejemplo de URL:
 * servidor:puerto/api/post/asignar_roles.php
 *
 * Ejemplo de solicitud:
 *   usuario_id: 5
 *   roles: [1,2,3]
 *
 * Responde en formato JSON.
 * 
 * Respuestas HTTP:
 * - 200 OK: Roles asignados correctamente
 * - 400 Bad Request: El parámetro roles debe ser un array JSON válido
 * - 500 Internal Server Error: Error durante la creación del proceso.
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

$data = $_POST;

require_once __DIR__ . '/../../controllers/UsuarioRolController.php';

$controller = new UsuarioRolController();
$controller->asignarRoles($data);
?>
