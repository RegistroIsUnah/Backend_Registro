<?php
/**
 * API para crear una solicitud extraordinaria.
 *
 * Método: POST
 *
 * Parámetros (en multipart/form-data):
 * - estudiante_id: ID del estudiante.
 * - tipo_solicitud: Nombre del tipo de solicitud (CAMBIO_CENTRO, CAMBIO_CARRERA, etc.).
 * - archivo_pdf: El archivo PDF que el estudiante sube.
 * 
 * Ejemplo de URL:
 * servidor:puerto/api/post/crear_solicitud_estudiante.php
 * 
 * Ejemplo envio
 * 
 * {
 *   "estudiante_id": 1,
 *   "tipo_solicitud": "CAMBIO_CENTRO",  // Tipo de solicitud
 *   "centro_actual_id": 2,  // ID del centro actual
 *   "centro_nuevo_id": 3,  // ID del nuevo centro
 *   "archivo_pdf": <archivo_pdf>  // El archivo PDF que el estudiante sube
 * }
 *    
 *  {
 *   "estudiante_id": 1,
 *   "tipo_solicitud": "CAMBIO_CARRERA",  // Tipo de solicitud
 *   "carrera_actual_id": 2,  // ID de la carrera actual
 *   "carrera_nuevo_id": 3,  // ID de la nueva carrera
 *   "archivo_pdf": <archivo_pdf>  // El archivo PDF que el estudiante sube
 * }
 *
 * {
 *   "estudiante_id": 1,
 *   "tipo_solicitud": "CANCELACION_EXCEPCIONAL o PAGO_REPOSICION",  // Tipo de solicitud
 *   "archivo_pdf": <archivo_pdf>  // El archivo PDF que el estudiante sube
 * }
 *
 * 
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

$data = $_POST;

require_once __DIR__ . '/../../controllers/SolicitudController.php';

$solicitudController = new SolicitudController();
$solicitudController->crearSolicitud($data);
?>