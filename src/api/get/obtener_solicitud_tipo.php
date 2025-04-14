<?php
/**
 * API para obtener solicitudes filtradas por tipo
 * 
 * Método: GET
 * Ejemplo:
 * http://localhost/Backend_Registro/src/get/obtener_solicitud_tipo.php?tipo_solicitud=CAMBIO_CENTRO
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
 
 $controller = new SolicitudController($conn);
 $controller->obtenerSolicitudesPorTipo($tipoSolicitud);
?>
