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
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
 
 if (!isset($_GET['tipo_solicitud']) || empty(trim($_GET['tipo_solicitud']))) {
     http_response_code(400);
     echo json_encode(['error' => 'El parámetro tipo_solicitud es requerido']);
     exit;
 }
 
 $tipoSolicitud = $_GET['tipo_solicitud'];
 
 require_once __DIR__ . '/../../controllers/SolicitudController.php';
 
 $controller = new SolicitudController();
 $controller->obtenerSolicitudesPorTipo($tipoSolicitud);
?>