<?php
/**
 * API para obtener las secciones de una clase con detalles del docente, aula y edificio que estan en estado activo
 * y los cupos disponibles.
 *
 * Permite filtrar las secciones mediante el parámetro GET 'clase_id'.
 * servidor:puerto/api/get/seccion_detalles_matricula.php?clase_id=1
 *
 * Respuestas HTTP:
 * - 200 OK: Retorna la lista de secciones en formato JSON.
 * - 400 Bad Request: Si no se proporciona el parámetro 'clase_id'.
 * - 500 Internal Server Error: Si ocurre un error en la consulta.
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

$clase_id = isset($_GET['clase_id']) ? intval($_GET['clase_id']) : null;
if ($clase_id === null) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro clase_id es requerido']);
    exit;
}

require_once __DIR__ . '/../../controllers/SeccionController.php';

$seccionController = new SeccionController();
$seccionController->getSeccionesPorClaseMatricula($clase_id);
?>
