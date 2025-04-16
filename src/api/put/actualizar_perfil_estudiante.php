<?php
/**
 * API para actualizar perfil de estudiante
 * 
 * Métodos aceptados: PUT, POST, GET
 * Autenticación requerida: Sí (rol estudiante)
 * Content-Type: application/json (para PUT/POST)
 * 
 * Parámetros permitidos:
 * - telefono (string)
 * - direccion (string)
 * - correo_personal (string)
 * 
 * Respuestas:
 * - 200 OK: Perfil actualizado exitosamente
 * - 400 Bad Request: Datos inválidos
 * - 401 Unauthorized: No autenticado
 * - 500 Internal Server Error: Error en el servidor
 * 
 * @package API
 * @author Jose Vargas
 * @version 1.0
 */

/*
{
    "success": true,
    "message": "Perfil actualizado exitosamente"
}
{
    "success": false,
    "error": "No hay campos válidos para actualizar"
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

$controller = new EstudianteController();
$controller->actualizarPerfil();
?>