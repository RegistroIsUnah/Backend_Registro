<?php
/**
 * API para listar usuarios con sus roles.
 *
 * Devuelve un JSON con la lista de usuarios y, para cada uno, los roles asociados.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/listar_usuarios_con_roles.php
 *
 * Respuestas:
 *  - 200 OK: Devuelve la lista de usuarios con roles.
 *  - 500 Internal Server Error: Si ocurre un error en la consulta.
 * 
 * Ejemplo respuesta:
 * 
 * [
 *  {
 *    "usuario_id": 1,
 *    "username": "juan.perez",
 *    "roles": ["Biblioteca_Jefe de Departamento", "Otro Rol"]
 *  },
 *  {
 *    "usuario_id": 2,
 *    "username": "ana.gomez",
 *    "roles": ["Biblioteca_Coordinador"]
 *  }
 * ]
 *
 * @package API
 * @author Ruben DIaz
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

require_once __DIR__ . '/../../controllers/UsuarioController.php';

$controller = new UsuarioController();
$controller->listarUsuariosConRoles();
?>
