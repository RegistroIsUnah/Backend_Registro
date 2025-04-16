<?php
/**
 * API para listar todos los roles (ID y nombre).
 *
 * 
 * Ejemplo de URL 
 * servidor:puerto/api/get/obtener_roles.php
 * 
 * Metodos soportados:
 *  GET
 *
 * Respuestas:
 * - 200 OK: Devuelve listado de docentes
 * - 400 Bad Request: Si falta el parámetro requerido
 * - 500 Internal Server Error: Si ocurre un error
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
 
 require_once __DIR__ . '/../../controllers/RolController.php';
 
 try {
     $controller = new RolController();
     $controller->listarRoles();
 
 } catch (Exception $e) {
     http_response_code(500);
     echo json_encode(['error' => 'Error interno del servidor', 'mensaje' => $e->getMessage()]);
 }
?>