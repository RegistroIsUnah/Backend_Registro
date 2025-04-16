<?php
/**
 * API para modificar detalles de un tipo de examen (nombre y nota mínima).
 *
 * Método: POST
 *
 * Parámetros (en form-data o JSON):
 *  - examen_id (int): ID del examen a modificar.
 *  - nombre (string, opcional): Nuevo nombre del examen.
 *  - nota_minima (float, opcional): Nueva nota mínima del examen.
 * 
 * Ejemplo
 * {
 *   "examen_id": 1,
 *   "nombre": "Examen Final de Matemáticas",
 *   "nota_minima": 7.5
 * }
 *
 * Ejemplo de URL 
 * servidor:puerto/api/post/modificar_examen.php
 * 
 * Métodos soportados:
 *  POST
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.0
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

// Permitir recibir datos JSON
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

if (empty($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos no proporcionados']);
    exit;
}

if (empty($input['examen_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El examen_id es requerido.']);
    exit;
}

require_once __DIR__ . '/../../controllers/TipoExamenController.php';

$examenController = new TipoExamenController();
$examenController->modificarExamen();
?>
