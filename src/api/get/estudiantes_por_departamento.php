<?php
/**
 * API para obtener estudiantes por departamento
 * 
 * Método: GET
 * 
 * Parámetros (query string):
 * - departamento_id (int): ID del departamento
 * 
 * Ejemplo de URL:
 * servidor:puerto/api/get/estudiantes_por_departamento.php?departamento_id=1
 * 
 * Métodos soportados:
 * GET
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

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

if (empty($_GET['departamento_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'departamento_id es requerido']);
    exit;
}

require_once __DIR__ . '/../../controllers/DepartamentoController.php';

try {
    $controller = new DepartamentoController();
    $result = $controller->obtenerEstudiantesPorDepartamento($_GET['departamento_id']);
    
    http_response_code(isset($result['error']) ? 400 : 200);
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}