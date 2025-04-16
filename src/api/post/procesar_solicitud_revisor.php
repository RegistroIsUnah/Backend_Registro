<?php
/**
 * API POST para procesar solicitud de revisor de admisiones
 * 
 * Método: POST
 * Autenticación requerida: Sí (rol estudiante)
 * Content-Type: application/json
 * 
 * Parámetros (JSON):
 * - carrera_id (int) - ID de la carrera a revisar
 * 
 * Respuestas:
 * - 200 OK: Permisos otorgados exitosamente
 * - 400 Bad Request: Datos faltantes o inválidos
 * - 403 Forbidden: Acceso no autorizado
 * - 500 Internal Server Error: Error en el servidor
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

 require_once __DIR__ . '/../../controllers/RevisorController.php';

 $controller = new RevisorController();
 $controller->procesarSolicitudRevisor();
?>