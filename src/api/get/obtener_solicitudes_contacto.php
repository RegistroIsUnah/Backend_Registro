<?php
/**
 * API para obtener solicitudes de contacto pendientes
 * 
 * Método: GET
 * 
 * Parámetros (query string):
 * - numero_cuenta (string): Número de cuenta del estudiante
 * 
 * Ejemplo de URL 
 * servidor:puerto/api/get/obtener_solicitudes_contacto.php?numero_cuenta=20241000001
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

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

if (empty($_GET['numero_cuenta'])) {
    http_response_code(400);
    echo json_encode(['error' => 'numero_cuenta es requerido']);
    exit;
}

require_once __DIR__ . '/../../controllers/ContactoController.php';

$controller = new ContactoController();
$result = $controller->obtenerSolicitudesPendientes($_GET['numero_cuenta']);

http_response_code(isset($result['error']) ? 400 : 200);
echo json_encode($result);
?>