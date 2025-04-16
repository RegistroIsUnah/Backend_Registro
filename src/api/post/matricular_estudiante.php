<?php
/**
 * API para matricular a un estudiante en la sección y, opcionalmente,
 * en el laboratorio seleccionado (si la clase lo tiene).
 *
 * Método: POST
 *
 * Parámetros (en form-data o JSON):
 *  - estudiante_id (int): ID del estudiante.
 *  - seccion_id (int): ID de la sección principal.
 *  - tipo_proceso (string): Tipo de proceso, e.g., "MATRICULA".
 *  - laboratorio_id (int, opcional): ID del laboratorio seleccionado.
 * 
 * Ejemplo de URL 
 * servidor:puerto/api/post/matricular_estudiante.php
 * 
 * Metodos soportados:
 *  POST
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.2
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
 
 if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
     http_response_code(405);
     echo json_encode(['error' => 'Método no permitido']);
     exit;
 }
 
 $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
 
 if (empty($input)) {
     http_response_code(400);
     echo json_encode(['error' => 'No se recibieron datos']);
     exit;
 }

require_once __DIR__ . '/../../controllers/MatriculaController.php';

$matriculaController = new MatriculaController();
$matriculaController->matricularEstudiante();
?>