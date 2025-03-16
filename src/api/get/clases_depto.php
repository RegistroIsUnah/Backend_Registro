<?php
/**
 * API para obtener las clases de un departamento.
 *
 * Permite filtrar las clases mediante el parámetro GET 'dept_id'.
 * servidor:puerto/api/get/clases?dept_id=1
 *
 * Respuestas HTTP:
 * - 200 OK: Retorna la lista de clases en formato JSON.
 * - 400 Bad Request: Si no se proporciona el parámetro 'dept_id'.
 * - 500 Internal Server Error: Si ocurre un error en la consulta.
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

header('Content-Type: application/json');

$dept_id = isset($_GET['dept_id']) ? intval($_GET['dept_id']) : null;
if ($dept_id === null) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro dept_id es requerido']);
    exit;
}

require_once __DIR__ . '/../../controllers/ClaseController.php';

$claseController = new ClaseController();
$claseController->getClasesPorDepartamento($dept_id);
?>
