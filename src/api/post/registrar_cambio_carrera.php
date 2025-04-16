<?php
/*
 * API para solicitud de cambio de carrera
 * 
 * Método: POST
 * 
 * Campos requeridos:
 * - carrera_solicitada_id: int (ID de la nueva carrera)
 * - motivo: string (opcional)
 *
 * @package API
 * @author Jose Vargas
 * @version 1.0
 * 
    POST /api/post/registrar_cambio_carrera.php
    Content-Type: application/json

    {
        "carrera_solicitada_id": 5,
        "motivo": "Interés en nueva área de estudio"
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

require_once __DIR__ . '/../../controllers/EstudianteController.php';
$data = $_POST;
$controller = new EstudianteController();
$controller->solicitarCambioCarrera($data);