<?php
/**
 * API para obtener los detalles del aspirante por su documento.
 *
 * Este endpoint recibe el parámetro documento y devuelve los detalles del aspirante en formato JSON.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/recuperar_datos_aspirante?documento=0801199909876
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve los detalles del aspirante.
 * - 400 Bad Request: Si falta el parámetro documento.
 * - 404 Not Found: Si no se encuentra el aspirante.
 * - 500 Internal Server Error: Si ocurre un error en la consulta.
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = [
        'documento' => isset($_GET['documento']) ? $_GET['documento'] : null
    ];
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

if (!$data || !isset($data['documento'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro documento es requerido']);
    exit;
}

require_once __DIR__ . '/../../controllers/AspiranteController.php';

$controller = new AspiranteController();
$controller->obtenerAspirantePorDocumento($data);
?>
