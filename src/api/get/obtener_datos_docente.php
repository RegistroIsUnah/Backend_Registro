<?php
/**
 * API para obtener los datos completos de un docente por su ID.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/obtener_datos_docente.php?docente_id=1
 *
 * Método soportado:
 *  GET
 *
 * Respuestas HTTP:
 * - 200 OK: Datos del docente en formato JSON (incluye departamento y centro)
 * - 400 Bad Request: Si falta el parámetro de entrada o es inválido
 * - 404 Not Found: Si no se encuentra el docente
 * - 500 Internal Server Error: Si ocurre un error en el servidor
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
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Validar y obtener el parámetro de entrada
if (empty($_GET['docente_id']) || !is_numeric($_GET['docente_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Se requiere un ID de docente válido']);
    exit;
}

$docente_id = intval($_GET['docente_id']);

try {
    // Incluir el controlador
    require_once __DIR__ . '/../../controllers/DocenteController.php';
    
    $docenteController = new DocenteController();
    $docenteController->getDocente($docente_id);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error en el servidor',
        'details' => $e->getMessage()
    ]);
}
?>