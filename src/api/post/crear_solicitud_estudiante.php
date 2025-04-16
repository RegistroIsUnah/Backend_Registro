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

 $allowedOrigins = [
    'https://www.registroisunah.xyz',
    'https://registroisunah.xyz'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: https://www.registroisunah.xyz");
}

header('Content-Type: application/json');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Credentials: true");

// Manejar solicitud OPTIONS para CORS preflight
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$data = $_POST;

require_once __DIR__ . '/../../controllers/SolicitudController.php';

$solicitudController = new SolicitudController();
$solicitudController->crearSolicitud($data);
?>