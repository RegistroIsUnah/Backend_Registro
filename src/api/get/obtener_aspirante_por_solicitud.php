<?php
/**
 * API para obtener los detalles del aspirante por su número de solicitud.
 *
 * Este endpoint recibe el parámetro numSolicitud y devuelve los detalles del aspirante en formato JSON.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/obtener_aspirante_por_solicitud?numSolicitud=SOL-1631772378
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve los detalles del aspirante.
 * - 400 Bad Request: Si falta el parámetro numSolicitud.
 * - 404 Not Found: Si no se encuentra el aspirante.
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
        'numSolicitud' => isset($_GET['numSolicitud']) ? $_GET['numSolicitud'] : null
    ];
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

if (!$data || !isset($data['numSolicitud'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro numSolicitud es requerido']);
    exit;
}

require_once __DIR__ . '/../../controllers/AspiranteController.php';

$controller = new AspiranteController();
$controller->obtenerAspirantePorSolicitud($data);
?>
