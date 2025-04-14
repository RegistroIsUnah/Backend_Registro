<?php
/**
 * API para rechazar una solicitud
 * 
 * Método: POST
 * 
 * Campos requeridos:
 * - solicitud_id: int (ID de la solicitud)
 * - motivo: string (Motivo del rechazo)
 * 
 * Ejemplo:
 * POST /api/post/rechazar_solicitud.php
 * Content-Type: application/json
 *
 * {
 *     "solicitud_id": 456,
 *     "motivo": "Documentación incompleta"
 * }
 *
 * @package API
 * @author [Tu Nombre]
 * @version 1.0
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../controllers/SolicitudController.php';

$data = json_decode(file_get_contents('php://input'), true);

try {
    $controller = new SolicitudController();
    $controller->rechazarSolicitud($data);
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>