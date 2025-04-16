<?php
/**
 * Endpoint para obtener los detalles de un libro para un estudiante solo obtendra los libros que estan activos.
 *
 * Permite obtener la información completa de un libro (datos principales, autores y tags).
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/obtener_libro.php?libro_id=5
 *
 * Respuestas:
 *  - 200 OK: Devuelve el libro en formato JSON.
 *  - 400 Bad Request: Si falta el parámetro o es inválido.
 *  - 404 Not Found: Si el libro no se encuentra o está inactivo.
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

if (!isset($_GET['libro_id']) || !is_numeric($_GET['libro_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro libro_id es inválido o faltante']);
    exit;
}

$libro_id = (int) $_GET['libro_id'];

require_once __DIR__ . '/../../controllers/LibroController.php';

$controller = new LibroController();
$controller->obtenerLibro($libro_id);
?>
