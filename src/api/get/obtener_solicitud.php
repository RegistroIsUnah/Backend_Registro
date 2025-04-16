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

// Recibimos los datos por query string
$data = $_GET;

require_once __DIR__ . '/../../controllers/SolicitudController.php';

$solicitudController = new SolicitudController();
$solicitudController->obtenerSolicitud($data);
?>
