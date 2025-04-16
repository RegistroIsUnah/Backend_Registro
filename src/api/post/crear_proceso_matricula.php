<?php
/**
 * API para crear un proceso de matrícula.
 * 
 * Ejemplo de URL:
 * servidor:puerto/api/post/crear_proceso_matricula.php
 *
 * Este endpoint recibe datos en formato JSON:
 * {
 *   "periodo_academico_id": 3,
 *   "tipo_proceso": "MATRICULA",
 *   "fecha_inicio": "2025-03-10 00:00:00",
 *   "fecha_fin": "2025-06-10 23:59:59"
 * }
 *
 * Al crearse, el proceso se inserta con estado 'ACTIVO' si la fecha_fin es futura o 'INACTIVO' si ya pasó.
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve el ID del proceso de matrícula creado y un mensaje de éxito.
 * - 400 Bad Request: Datos faltantes o formato inválido.
 * - 500 Internal Server Error: Error durante la creación del proceso.
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

 // Obtener los datos de la solicitud
 $data = json_decode(file_get_contents("php://input"), true);
 
 // Verificar que los datos sean correctos
 if (empty($data)) {
     http_response_code(400);
     echo json_encode(['error' => 'No se enviaron datos.']);
     exit;
 }
 
 require_once __DIR__ . '/../../controllers/ProcesoMatriculaController.php';
 
 $procesoMatriculaController = new ProcesoMatriculaController();
 $procesoMatriculaController->crearProcesoMatricula($data);
?>
