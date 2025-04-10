<?php
/**
 * API para listar todos los docentes de un departamento.
 *
 * Parámetros GET:
 * - dept_id: ID del departamento
 * 
 * Ejemplo de URL 
 * servidor:puerto/api/get/listar_docentes_departamento.php?dept_id=1
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

require_once __DIR__ . '/../../controllers/DocenteController.php';

try {
    if (!isset($_GET['dept_id']) || !is_numeric($_GET['dept_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Parámetro dept_id requerido y debe ser numérico']);
        exit;
    }

    $deptId = (int)$_GET['dept_id'];
    $controller = new DocenteController();
    $resultado = $controller->listarDocentesPorDepartamento($deptId);
    

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor',
        'mensaje' => $e->getMessage()
    ]);
}
?>