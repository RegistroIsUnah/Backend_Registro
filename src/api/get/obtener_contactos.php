<?php
/**
 * API para obtener los contactos de un estudiante
 * 
 * Método: GET
 * Parámetros:
 * - numero_cuenta (string): Número de cuenta del estudiante
 * 
 * Ejemplo de URL 
 * servidor:puerto/api/get/obtener_contactos?numero_cuenta=20241000001
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

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Validar parámetros
if (empty($_GET['numero_cuenta'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Número de cuenta es requerido']);
    exit;
}

require_once __DIR__ . '/../../controllers/ContactoController.php';

try {
    $controller = new ContactoController();
    $resultado = $controller->obtenerContactos($_GET['numero_cuenta']);

    if (isset($resultado['error'])) {
        http_response_code(400);
    }

    echo json_encode($resultado);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>