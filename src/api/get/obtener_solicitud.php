<?php
/**
 * API para obtener los detalles de una solicitud específica.
 *
 * Método: GET
 *
 * Parámetros (en query string):
 *  - solicitud_id: ID de la solicitud.
 * 
 * Ejemplo de URL:
 *  servidor:puerto/api/get/obtener_solicitud.php?solicitud_id=1
 * 
 * Métodos soportados:
 *  GET
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Recibimos los datos por query string
$data = $_GET;

require_once __DIR__ . '/../../controllers/SolicitudController.php';

$solicitudController = new SolicitudController();
$solicitudController->obtenerSolicitud($data);
?>
