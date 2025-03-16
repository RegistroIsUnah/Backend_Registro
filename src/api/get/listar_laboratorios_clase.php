<?php
/**
 * API para listar los laboratorios asociados a una clase.
 *
 * Este endpoint recibe el parámetro clase_id y devuelve la lista de laboratorios con todos sus detalles.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/listar_laboratorios_clase?clase_id=5
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve un arreglo de laboratorios.
 * - 400 Bad Request: Si falta el parámetro clase_id.
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
        'clase_id' => isset($_GET['clase_id']) ? $_GET['clase_id'] : null
    ];
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

if (!$data || !isset($data['clase_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro clase_id es requerido']);
    exit;
}

require_once __DIR__ . '/../../controllers/ClaseController.php';

$controller = new ClaseController();
$controller->listarLaboratorios($data);
?>
