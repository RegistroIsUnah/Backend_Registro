<?php
/**
 * API para obtener los docentes de las clases en las que está matriculado el estudiante
 * 
 * Método: GET
 * Autenticación requerida: Sí (rol estudiante)
 * 
 * Respuestas:
 * - 200 OK: Devuelve lista de docentes y clases en formato JSON
 * - 403 Forbidden: Si no hay sesión activa o el rol no es estudiante
 * - 404 Not Found: Si el estudiante no está matriculado en ninguna clase
 * - 500 Internal Server Error: En caso de error en el servidor
 * 
 * Ejemplo de éxito:
 * {
 *   "success": true,
 *   "data": [
 *     {
 *       "clase_id": 15,
 *       "codigo_clase": "MAT-101",
 *       "nombre_clase": "Matemáticas Básicas",
 *       "docente_id": 23,
 *       "nombre_docente": "María",
 *       "apellido_docente": "González",
 *       "correo_docente": "maria.gonzalez@universidad.edu"
 *     },
 *     {
 *       "clase_id": 18,
 *       "codigo_clase": "FIS-201",
 *       "nombre_clase": "Física Moderna",
 *       "docente_id": 45,
 *       "nombre_docente": "Carlos",
 *       "apellido_docente": "Martínez",
 *       "correo_docente": "carlos.martinez@universidad.edu"
 *     }
 *   ]
 * }
 * @package API
 * @author Jose Vargas
 * @version 1.0
 * @param int $estudianteId
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

require_once __DIR__ . '/../../controllers/EstudianteController.php';


$controller = new EstudianteController();
$controller->obtenerDocentesClases();

?>