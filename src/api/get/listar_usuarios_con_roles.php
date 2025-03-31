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

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require_once __DIR__ . '/../../controllers/UsuarioController.php';

$controller = new UsuarioController();
$controller->listarUsuariosConRoles();
?>
