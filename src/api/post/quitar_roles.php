<?php
/**
 * API para quitar roles a un usuario.
 * 
 * Ejemplo de URL 
 * servidor:puerto/api/post/quitar_roles.php
 * 
 * Metodos soportados:
 *  POST
 *
 * Se espera recibir vía POST:
 *   - usuario_id: int
 *   - roles: JSON string (ej. [2,3])
 *
 * Ejemplo de solicitud:
 *   usuario_id: 5
 *   roles: [2,3]
 *
 * Responde en formato JSON.
 * 
 * Respuestas HTTP:
 * - 200 OK: Roles eliminados correctamente
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
 header("Access-Control-Allow-Methods: POST, OPTIONS");
 header("Access-Control-Allow-Headers: Content-Type");

 $data = $_POST;

 require_once __DIR__ . '/../../controllers/UsuarioRolController.php';

 $controller = new UsuarioRolController();
 $controller->quitarRoles($data);
?>
