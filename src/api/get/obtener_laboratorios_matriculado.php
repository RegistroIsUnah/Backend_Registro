<?php
/**
 * API para obtener los laboratorios matriculados de una clase.
 *
 * Recibe el parámetro GET 'estudiante_id' y devuelve los detalles de los laboratorios matriculados
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/obtener_laboratorios_matriculado.php?estudiante_id=1
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
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if (!isset($_GET['estudiante_id']) || !is_numeric($_GET['estudiante_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro estudiante_id es inválido o faltante']);
    exit;
}

$estudiante_id = (int) $_GET['estudiante_id'];

require_once __DIR__ . '/../../controllers/MatriculaController.php';

$controller = new MatriculaController();
$controller->obtenerLaboratoriosMatriculados($estudiante_id);
?>
