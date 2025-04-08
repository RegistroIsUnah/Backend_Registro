<?php
/*
 * API GET para obtener solicitudes por tipo
 * 
 * Parámetros requeridos:
 * - tipo_solicitud: Nombre del tipo de solicitud (ej: CAMBIO_CENTRO)
 * 
 * Ejemplo: 
 * /api/get/solicitudes_por_tipo.php?tipo_solicitud=CAMBIO_CENTRO
 * 
 * Respuestas:
 * - 200 OK: Lista de solicitudes
 * - 400 Bad Request: Parámetro faltante
 * - 404 Not Found: No hay resultados
 * - 500 Internal Server Error: Error en el servidor
 * 
 * @package API
 * @version 1.0
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require_once __DIR__ . '/../../controllers/SolicitudController.php';

try {
    $controller = new SolicitudController();
    $controller->obtenerSolicitudesPorTipo();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>