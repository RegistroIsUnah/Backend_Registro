<?php
/**
 * API para validar si un estudiante puede matricular según el proceso activo.
 *
 * Parámetros GET:
 * - estudiante_id: ID del estudiante
 * 
 * Ejemplo de URL:
 * servidor:puerto/api/get/matricula_estudiante.php?estudiante_id=1
 *
 * Respuestas:
 * - 200 OK: Resultado de la validación (puede o no puede matricular)
 * - 400 Bad Request: Si falta el parámetro requerido
 * - 500 Internal Server Error: Si ocurre un error
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

require_once __DIR__ . '/../../controllers/EstudianteController.php';

try {
    if (!isset($_GET['estudiante_id']) || !is_numeric($_GET['estudiante_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Parámetro estudiante_id requerido y debe ser numérico']);
        exit;
    }

    $estudianteId = (int)$_GET['estudiante_id'];
    $controller = new EstudianteController();
    $resultado = $controller->validarDiaMatricula($estudianteId);

    http_response_code(200);
    echo json_encode($resultado);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error interno del servidor',
        'mensaje' => $e->getMessage()
    ]);
}
?>