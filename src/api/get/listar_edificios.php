<?php
/**
 * API para obtener la lista básica de edificios.
 *
 * Retorna la lista de edificios con su ID y nombre en formato JSON.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/listar_edificios.php
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve la lista de edificios.
 * - 404 Not Found: No se encontraron edificios registrados.
 * - 500 Internal Server Error: Error en el servidor.
 *
 * Ejemplo respuesta:
 * 
 * [
 *   {
 *       "edificio_id": 1,
 *       "nombre": "Edificio de Ciencias"
 *   },
 *   {
 *       "edificio_id": 2,
 *       "nombre": "Edificio de Ingenierías"
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

$edificioController = new CentroController();
$edificioController->getEdificios();
?>