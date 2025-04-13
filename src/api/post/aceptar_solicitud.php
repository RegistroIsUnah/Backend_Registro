<?php
/**
 * API para aceptar una solicitud
 * 
 * Método: POST
 * 
 * Campos requeridos:
 * - solicitud_id: int (ID de la solicitud)
 * 
 * Ejemplo:
 * POST /api/post/aceptar_solicitud.php
 * Content-Type: application/json
 *
 * {
 *     "solicitud_id": 123
 * }
 *
 * @package API
 * @author [Tu Nombre]
 * @version 1.0
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require_once __DIR__ . '/../../controllers/SolicitudController.php';

$data = json_decode(file_get_contents('php://input'), true);

try {
    $controller = new SolicitudController();
    $controller->aceptarSolicitud($data);
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>