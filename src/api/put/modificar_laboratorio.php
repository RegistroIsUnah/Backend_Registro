<?php
/**
 * API para modificar un laboratorio.
 *
 * Método: POST
 *
 * Parámetros (en form-data o JSON):
 * - laboratorio_id (int): ID del laboratorio a modificar (requerido)
 * - aula_id (int): ID del aula (opcional)
 * - estado (string): 'ACTIVA' o 'CANCELADA' (opcional)
 * - motivo_cancelacion (string): Requerido si estado es 'CANCELADA'
 * - cupos (int): Número de cupos (opcional)
 * - hora_inicio (string): Hora de inicio en formato HH:MM:SS (opcional)
 * - hora_fin (string): Hora de fin en formato HH:MM:SS (opcional)
 * - dias (array): Array de IDs de días (opcional, ej: [1,3] para Lunes y Miércoles)
 * 
 * Ejemplo de URL 
 * servidor:puerto/api/post/modificar_laboratorio.php
 * 
 * Ejemplo envio
 * 
 * {
 *   "laboratorio_id": 1,
 *   "clase_id": 2,
 *   "periodo_academico_id": 1,
 *   "hora_inicio": "09:00:00",
 *   "hora_fin": "11:00:00",
 *   "aula_id": 2,
 *   "cupos": 30
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
$laboratorioController->modificarLaboratorio($input);
?>