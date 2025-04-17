<?php
/**
 * API para eliminar un contacto
 * 
 * Método: POST
 * Parámetros (JSON):
 * 
 * {
 *   "numero_cuenta": "20241000001",
 *   "contacto_numero_cuenta": "20241000002"
 * }
 * 
 *  Ejemplo de URL 
 * servidor:puerto/api/post/eliminar_contactos.php
 * 
 * Métodos soportados:
 *  POST
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
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Obtener datos del cuerpo
$data = json_decode(file_get_contents('php://input'), true);

// Validar parámetros
if (empty($data['numero_cuenta']) || empty($data['contacto_numero_cuenta'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

require_once __DIR__ . '/../../controllers/ContactoController.php';

try {
    $controller = new ContactoController();
    $resultado = $controller->eliminarContacto(
        $data['numero_cuenta'],
        $data['contacto_numero_cuenta']
    );

    http_response_code($resultado['success'] ? 200 : 400);
    echo json_encode($resultado);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>