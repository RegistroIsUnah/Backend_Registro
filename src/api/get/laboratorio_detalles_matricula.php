<?php
/**
 * API para obtener los laboratorios matriculados de una clase.
 *
 * Recibe el parámetro GET 'clase_id' y devuelve los detalles de los laboratorios relacionados.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/laboratorios.php?clase_id=1
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

if (!isset($_GET['clase_id']) || !is_numeric($_GET['clase_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro clase_id es inválido o faltante']);
    exit;
}

$clase_id = (int) $_GET['clase_id'];

require_once __DIR__ . '/../../controllers/LaboratorioController.php';

$controller = new LaboratorioController();
$controller->obtenerLaboratorios($clase_id);
?>