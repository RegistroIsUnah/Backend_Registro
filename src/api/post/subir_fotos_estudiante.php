<?php
/**
 * API para subir fotos de estudiantes
 *
 * Método: POST (multipart/form-data)
 *
 * Parámetros:
 *  - estudiante_id (int): ID del estudiante
 *  - foto (file): Archivo de imagen (JPEG, PNG o GIF, máx. 2MB)
 * 
 * Ejemplo de URL: 
 * servidor:puerto/api/post/subir_fotos_estudiante.php
 *
 * @package API
 * @version 1.0
 * @author Ruben Diaz
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
$controller->subirFotos();