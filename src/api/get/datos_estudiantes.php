<?php
/*
 * API GET para obtener datos del perfil del estudiante
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

 servidor:puerto/api/get/datos_estudiante.php?estudianteId=5

{
    "success": true,
    "data": {
        "informacion_personal": {
            "nombre_completo": "Juan Pérez",
            "identidad": "0801199901234",
            "correo": "juan@example.com",
            "telefono": "+504 9876-5432",
            "direccion": "Tegucigalpa, Honduras",
            "numero_cuenta": "20230001"
        },
        "academico": {
            "indice_global": 85.5,
            "indice_periodo": 90.0,
            "centro": "Centro Universitario Tegucigalpa",
            "carreras": ["Ingeniería en Sistemas", "Administración"],
            "año_ingreso": 2023,
            "solicitudes_pendientes": 2
        },
        "cuenta": {
            "username": "juan.perez"
        },
        "fotos": [
            "/uploads/fotos/20230001_1.jpg",
            "/uploads/fotos/20230001_2.jpg"
        ]
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


if (!isset($_GET['estudianteid']) || !is_numeric($_GET['estudianteid'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro estudianteid es inválido o faltante']);
    exit;
}

$estudianteid = (int) $_GET['estudianteid'];

require_once __DIR__ . '/../../controllers/EstudianteController.php';

$controller = new EstudianteController();
$controller->obtenerPerfilEstudiante($estudianteid);
?>
