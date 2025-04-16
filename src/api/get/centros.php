<?php
/**
 * API para obtener la lista de centros.
 *
 * Retorna la lista de centros en formato JSON.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/centros.php
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve la lista de centros.
 * - 500 Internal Server Error: En caso de error al obtener los datos.
 *
 *  Ejemplo respuesta
 * 
 * [
 *   {
 *       "centro_id": "1",
 *       "nombre": "Centro Universitario Regional Tegucigalpa"
 *   },
 *   {
 *       "centro_id": "2",
 *       "nombre": "Centro Universitario Regional San Pedro Sula"
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

require_once __DIR__ . '/../../controllers/CentroController.php';

$centroController = new CentroController();
$centroController->getCentros();
?>
