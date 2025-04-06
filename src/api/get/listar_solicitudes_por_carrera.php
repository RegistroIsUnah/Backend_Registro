<?php
/**
 * API para obtener todas las solicitudes de una carrera específica.
 *
 * Método: GET
 *
 * Ejemplo de URL: servidor:puerto/api/get/listar_solicitudes_por_carrera.php?carrera_id=1
 *
 * Métodos soportados: GET
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

// Recibimos los datos por query string
$data = $_GET;

require_once __DIR__ . '/../../controllers/SolicitudController.php';

$solicitudController = new SolicitudController();
$solicitudController->listarSolicitudesPorCarrera($data);
?>
