<?php
/**
 * API para asignar roles a un usuario.
 *
 * Se espera recibir vía POST (multipart/form-data o x-www-form-urlencoded):
 *   - usuario_id: int
 *   - roles: JSON string (ej. [1,2,3])
 * 
 * Ejemplo de URL:
 * servidor:puerto/api/post/asignar_roles
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

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

$data = $_POST;

require_once __DIR__ . '/../../controllers/UsuarioRolController.php';

$controller = new UsuarioRolController();
$controller->asignarRoles($data);
?>
