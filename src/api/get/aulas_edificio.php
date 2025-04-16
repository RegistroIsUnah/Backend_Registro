<?php
/**
 * API para obtener la lista de aulas asociadas a un edificio.
 *
 * Se espera recibir el ID del edificio mediante el parámetro "edificio_id" en la query string.
 *
 * Ejemplo de URL:
 *   servidor:puerto/api/get/aulas_edificio.php?edificio_id=2
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve la lista de aulas en formato JSON.
 * - 400 Bad Request: Falta el parámetro edificio_id o es inválido.
 * - 404 Not Found: No se encontraron aulas para el edificio.
 * - 500 Internal Server Error: Error en la consulta.
 *
 * Ejemplo de respuesta:
 * {
 *   "aulas": [
 *       {
 *           "aula_id": 3,
 *           "nombre": "Aula 201",
 *           "capacidad": 60
 *       }
 *   ]
 * }
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

// Validar que se reciba el parámetro "edificio_id"
if (!isset($_GET['edificio_id']) || empty($_GET['edificio_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el parámetro edificio_id']);
    exit;
}

$edificio_id = intval($_GET['edificio_id']);

require_once __DIR__ . '/../../controllers/AulaController.php';

$aulaController = new AulaController();
$aulaController->getAulasPorEdificio($edificio_id);
?>
