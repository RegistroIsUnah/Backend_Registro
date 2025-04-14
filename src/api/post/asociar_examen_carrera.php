<?php
/**
 * API para asociar un examen con una carrera.
 *
 * Método: POST
 *
 * Parámetros (en form-data o JSON):
 *  - examen_id (int): ID del examen.
 *  - carrera_id (int): ID de la carrera.
 * 
 * Ejemplo de URL 
 * servidor:puerto/api/post/asociar_examen_carrera.php
 * 
 * Métodos soportados:
 *  POST
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

// Permitir recibir datos JSON
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

if (empty($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos no proporcionados']);
    exit;
}

if (empty($input['examen_id']) || empty($input['carrera_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El examen_id y carrera_id son requeridos.']);
    exit;
}

require_once __DIR__ . '/../../controllers/TipoExamenController.php';

$examenController = new TipoExamenController();
$examenController->asociarExamenCarrera();
?>
