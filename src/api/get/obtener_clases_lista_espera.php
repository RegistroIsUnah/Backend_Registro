<?php
/**
 * API para obtener las clases matriculadas en estado 'EN_ESPERA' de un estudiante.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/clases_en_espera.php?estudiante_id=123
 *
 * Responde en formato JSON.
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

if (!isset($_GET['estudiante_id']) || !is_numeric($_GET['estudiante_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro estudiante_id es inválido o faltante']);
    exit;
}

$estudiante_id = (int) $_GET['estudiante_id'];

require_once __DIR__ . '/../../controllers/MatriculaController.php';

$controller = new MatriculaController();
$controller->obtenerClasesEnEspera($estudiante_id);
?>
