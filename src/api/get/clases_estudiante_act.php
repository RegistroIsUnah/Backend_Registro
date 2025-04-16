<?php
/*
 * API GET para obtener las clases actuales de un estudiante
 * 
 * Método: GET
 * Autenticación requerida: Sí (mismo estudiante o admin)
 * 
 * Respuestas:
 * - 200 OK: Devuelve datos del perfil
 * - 401 Unauthorized: No autenticado
 * - 403 Forbidden: No autorizado
 * - 404 Not Found: Estudiante no existe
 * - 500 Internal Server Error: Error en el servidor
 * 
 * @package API
 * @author Jose Vargas
 * @version 1.0

 servidor:puerto/api/get/clases_estudiante_act.php?estudianteId=5


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

if (!isset($_GET['estudianteId']) || !is_numeric($_GET['estudianteId'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro estudianteId es inválido o faltante']);
    exit;
}

$estudianteId = (int) $_GET['estudianteId'];

require_once __DIR__ . '/../../controllers/EstudianteController.php';

$controller = new EstudianteController();
$controller->obtenerClasesActEstudiante($estudianteId);
?>
