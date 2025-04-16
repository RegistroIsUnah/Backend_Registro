<?php
/**
 * API para obtener los laboratorios matriculados de una clase.
 *
 * Recibe el parámetro GET 'clase_id' y devuelve los detalles de los laboratorios relacionados.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/laboratorios.php?clase_id=1
 *
 * Responde en formato JSON.
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

if (!isset($_GET['clase_id']) || !is_numeric($_GET['clase_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro clase_id es inválido o faltante']);
    exit;
}

$clase_id = (int) $_GET['clase_id'];

require_once __DIR__ . '/../../controllers/LaboratorioController.php';

$controller = new LaboratorioController();
$controller->obtenerLaboratorios($clase_id);
?>