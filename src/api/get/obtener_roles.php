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

 header("Access-Control-Allow-Origin: *");
 header("Content-Type: application/json");
 
 require_once __DIR__ . '/../../controllers/RolController.php';
 
 try {
     $controller = new RolController();
     $controller->listarRoles();
 
 } catch (Exception $e) {
     http_response_code(500);
     echo json_encode(['error' => 'Error interno del servidor', 'mensaje' => $e->getMessage()]);
 }
?>