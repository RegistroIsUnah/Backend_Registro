<?php
/**
 * API para obtener los detalles de una carrera, su coordinador y jefe de departamento.
 *
 * Este endpoint recibe el parámetro carrera_id y devuelve los detalles de la carrera en formato JSON.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/obtener_detalles_carrera?carrera_id=1
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve los detalles de la carrera, coordinador y jefe de departamento.
 * - 400 Bad Request: Si falta el parámetro carrera_id.
 * - 404 Not Found: Si no se encuentra la carrera.
 * - 500 Internal Server Error: Si ocurre un error en la consulta.
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = [
        'carrera_id' => isset($_GET['carrera_id']) ? $_GET['carrera_id'] : null
    ];
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

if (!$data || !isset($data['carrera_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro carrera_id es requerido']);
    exit;
}

require_once __DIR__ . '/../../controllers/CarreraController.php';

$controller = new CarreraController();
$controller->obtenerDetallesCarrera($data);
?>
