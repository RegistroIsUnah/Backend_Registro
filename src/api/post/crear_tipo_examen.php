<?php
/**
 * API para crear un nuevo tipo de examen.
 *
 * Método: POST
 *
 * Parámetros (en form-data o JSON):
 *  - nombre (string): Nombre del tipo de examen.
 *  - nota_minima (float): Nota mínima para aprobar el examen.
 * 
 * Ejemplo de URL 
 * servidor:puerto/api/post/crear_tipo_examen.php
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

if (empty($input['nombre']) || empty($input['nota_minima'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El nombre y la nota mínima son requeridos.']);
    exit;
}

require_once __DIR__ . '/../../controllers/TipoExamenController.php';

$tipoExamenController = new TipoExamenController();
$tipoExamenController->crearTipoExamen();
?>
