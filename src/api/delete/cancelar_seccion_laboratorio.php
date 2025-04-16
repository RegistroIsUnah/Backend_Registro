<?php
/**
 * API para cancelar la matrícula de un estudiante en una sección tambien cancela el laboratorio.
 *
 * Método: POST
 *
 * Parámetros (en JSON):
 *  - estudiante_id: ID del estudiante.
 *  - seccion_id: ID de la sección a cancelar.
 * 
 * Ejemplo de URL:
 *  servidor:puerto/api/delete/cancelar_seccion_laboratorio.php
 * 
 * Ejemplo envio
 * 
 * {
 *   "estudiante_id": 1,
 *   "seccion_id": 101
 * }
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

// Recibimos los datos en formato JSON
$data = json_decode(file_get_contents('php://input'), true);

require_once __DIR__ . '/../../controllers/MatriculaController.php';

$seccionController = new MatriculaController();
$seccionController->cancelarMatricula($data);
?>
