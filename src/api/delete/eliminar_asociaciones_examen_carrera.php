<?php
/**
 * API para desasociar un examen de una carrera.
 *
 * Método: POST
 *
 * Parámetros (en form-data o JSON):
 *  - examen_id (int): ID del examen a desasociar.
 *  - carrera_id (int): ID de la carrera de la cual se desasociará el examen.
 * 
 * Ejemplo de URL 
 * servidor:puerto/api/post/desasociar_examen_carrera.php
 * 
 * Métodos soportados:
 *  POST
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.1
 * 
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
 
// Permitir recibir datos JSON
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
 
if (empty($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos no proporcionados']);
    exit;
}
 
if (empty($input['examen_ids']) || empty($input['carrera_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El array examen_ids y carrera_id son requeridos.']);
    exit;
}

require_once __DIR__ . '/../../controllers/TipoExamenController.php';

$examenController = new TipoExamenController();
$examenController->desasociarExamenesDeCarrera($input['examen_ids'], $input['carrera_id']);
?>
