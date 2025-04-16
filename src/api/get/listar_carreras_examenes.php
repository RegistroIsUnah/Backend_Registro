<?php
/**
 * API para listar las carreras con los exámenes y sus puntajes.
 *
 * Este endpoint devuelve la lista de todas las carreras con los exámenes asociados y sus puntajes correspondientes.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/listar_carreras_examenes.php
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve la lista de carreras con exámenes y puntajes.
 * - 500 Internal Server Error: Si ocurre un error en la consulta.
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

require_once __DIR__ . '/../../controllers/CarreraExamenController.php';

$controller = new CarreraExamenController();
$controller->listarCarrerasConExamenesYPuntajes();
?>
