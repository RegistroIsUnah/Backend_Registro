<?php
/**
 * API para obtener solicitudes filtradas por tipo
 * 
 * Método: GET
 * Ejemplo:
 * http://localhost/Backend_Registro/src/get/obtener_solicitud_tipo.php?tipo_solicitud=CAMBIO_CENTRO
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
