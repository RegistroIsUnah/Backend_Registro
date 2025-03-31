<?php
/**
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
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../../controllers/EstudianteController.php';
    $controller = new EstudianteController();
    $controller->obtenerPerfilEstudiante();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno: ' . $e->getMessage()
    ]);
}
?>