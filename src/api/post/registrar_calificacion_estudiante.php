<?php
/*
     {
        "estudiante_id": 2023001,
        "seccion_id": 15,
        "calificacion": 85.5,
        "observacion": "Excelente participación en prácticas",
        "estado_curso_id": 2
    }

    {
    "success": true,
    "data": {
        "historial_id": 89,
        "fecha": "2023-10-05 14:30:00"
    }
}
 * @package API
 * @author Jose Vargas
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

// Obtener datos de entrada
$data = json_decode(file_get_contents('php://input'), true);

require_once __DIR__ . '/../../controllers/DocenteController.php';

$controller = new DocenteController();
$controller->calificarEstudiante($data);
?>