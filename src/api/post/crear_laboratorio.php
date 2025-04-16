<?php
/**
 * API para crear un laboratorio.
 *
 * Método: POST
 *
 * Parámetros (en form-data o JSON):
 *  - clase_id (int): ID de la clase asociada.
 *  - periodo_academico_id (int): ID del periodo académico.
 *  - hora_inicio (string): Hora de inicio del laboratorio (formato HH:MM:SS).
 *  - hora_fin (string): Hora de fin del laboratorio (formato HH:MM:SS).
 *  - aula_id (int): ID del aula donde se llevará a cabo el laboratorio.
 *  - cupos (int): Número de cupos disponibles.
 * 
 * Ejemplo de URL 
 * servidor:puerto/api/post/crear_laboratorio.php
 * 
 * Ejemplo de envio
 * 
 * {
 *   "clase_id": 1,
 *   "codigo_laboratorio": "LAB-2023-01",
 *   "periodo_academico_id": 1,
 *   "hora_inicio": "08:00:00",
 *   "hora_fin": "10:00:00",
 *   "aula_id": 2,
 *   "cupos": 30,
 *   "dias": [1, 3] // Lunes y Miércoles
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

// Permitir recibir datos JSON
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

if (empty($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos no proporcionados']);
    exit;
}

require_once __DIR__ . '/../../controllers/LaboratorioController.php';

$laboratorioController = new LaboratorioController();
$laboratorioController->crearLaboratorio($input);
?>