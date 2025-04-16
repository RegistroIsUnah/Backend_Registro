<?php
/**
 * API para obtener las secciones de una clase con detalles del docente, aula y edificio.
 *
 * Permite filtrar las secciones mediante el parámetro GET 'clase_id'.
 * servidor:puerto/api/get/seccion_detalles.php?clase_id=1
 *
 * Respuestas HTTP:
 * - 200 OK: Retorna la lista de secciones en formato JSON.
 * - 400 Bad Request: Si no se proporciona el parámetro 'clase_id'.
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

$clase_id = isset($_GET['clase_id']) ? intval($_GET['clase_id']) : null;
if ($clase_id === null) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro clase_id es requerido']);
    exit;
}

require_once __DIR__ . '/../../controllers/SeccionController.php';

$seccionController = new SeccionController();
$seccionController->getSeccionesPorClase($clase_id);
?>
