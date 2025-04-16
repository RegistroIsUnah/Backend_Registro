<?php
/**
 * API para obtener la lista de carreras.
 *
 * Permite filtrar carreras por centro a través del parámetro GET 'centro_id'.
 * Ejemplo de URL:
 * servidor:puerto/api/get/carreras.php?centro_id=1
 * Si no se especifica, retorna todas las carreras.
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve la lista de carreras en formato JSON.
 * - 500 Internal Server Error: En caso de error al obtener los datos.
 *
 * Ejemplo respuesta
 * 
 * [
 *   {
 *       "carrera_id": "1",
 *       "nombre": "Ingeniería en Sistemas"
 *   },
 *   {
 *       "carrera_id": "2",
 *       "nombre": "Ingeniería Civil"
 *   }
 * ]
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

// Obtener el parámetro centro_id si se envía; si no, será null.
$centro_id = isset($_GET['centro_id']) ? intval($_GET['centro_id']) : null;

require_once __DIR__ . '/../../controllers/CarreraController.php';

$carreraController = new CarreraController();
$carreraController->getCarreras($centro_id);
?>
