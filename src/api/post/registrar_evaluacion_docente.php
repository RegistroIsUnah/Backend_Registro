<?php
/*
 * API para registrar evaluaciones de docentes
 * 
 * Ejemplo de URL: 
 * servidor:puerto/api/post/registrar_evaluacion_docentes.php
 *
 * Métodos soportados:
 *  POST
 *
 * Se espera recibir en el cuerpo de la solicitud (application/json):
 *   - docente_id: int (ID del docente evaluado)
 *   - periodo_id: int (ID del periodo académico)
 *   - respuestas: JSON (formato: {"1": "Respuesta 1", "2": "Respuesta 2"})
 *
 * Requiere autenticación:
 *   - Debe enviar el ID de estudiante válido en sesión
 *   - Rol requerido: 'estudiante'
 *
 * Respuestas:
 *   - 200 OK: Evaluación registrada correctamente
 *   - 400 Bad Request: Faltan parámetros o formato inválido
 *   - 401 Unauthorized: No autenticado
 *   - 403 Forbidden: Rol no autorizado
 *   - 500 Internal Server Error: Error en el servidor
 *
 * @package API
 * @author Jose Vargas
 * @version 2.0
 * 
 * Formato de entrada
 {
  "estudiante_id": 5,
  "docente_id": 1,
  "periodo_id": 2,
  "respuestas": {
    "1": "testing",
    "2": "testing",
    "3": "testing"
  }
}
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

// Obtener datos de entrada
$data = json_decode(file_get_contents('php://input'), true);

require_once __DIR__ . '/../../controllers/EstudianteController.php';

$controller = new EstudianteController();
$controller->registrarEvaluacionDocente($data);