<?php
/**
 * API para obtener los datos del docente a partir del ID de la sección.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/listar_docente_por_seccion.php?seccion_id=1
 *
 * Método soportado:
 *  GET
 *
 * Respuestas HTTP:
 * - 200 OK: Datos del docente en formato JSON.
 * - 400 Bad Request: Si falta el parámetro de entrada.
 * - 500 Internal Server Error: Si ocurre un error en el servidor.
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

// Verificar el método de la solicitud
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Obtener el parámetro de entrada (ID de la sección)
if (empty($_GET['seccion_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el parámetro seccion_id']);
    exit;
}

$seccion_id = intval($_GET['seccion_id']);

// Incluir el controlador y ejecutar la función
require_once __DIR__ . '/../../controllers/DocenteController.php';

$docenteController = new DocenteController();
$docenteController->obtenerDocentePorSeccion($seccion_id);
?>
