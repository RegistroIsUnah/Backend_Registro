<?php
/**
 * API POST para evaluar aspirantes
 * 
 * Método: POST
 * Autenticación requerida: Sí (rol admin)
 * Content-Type: application/json
 * 
 * Parámetros (JSON):
 * - aspirante_id (int) - ID del aspirante a evaluar
 * 
 * Respuestas:
 * - 200 OK: Resultado de la evaluación
 * - 400 Bad Request: Datos faltantes
 * - 500 Internal Server Error: Error en el servidor
 * 
 * Ejemplo de respuesta:
 * {
 *   "success": true,
 *   "resultado": {
 *     "decision": "ADMITIDO",
 *     "carrera_asignada": "Ingeniería en Sistemas",
 *     "detalles": [...] 
 *   }
 * }
 * @author Jose Vargas
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
 
require_once __DIR__ . '/../../controllers/AspiranteController.php';

$controller = new AspiranteController();
$controller->evaluarAspirante();