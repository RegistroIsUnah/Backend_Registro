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
 
 {
    "success": true,
    "data": {
        "informacion_personal": {
            "nombre_completo": "Juan Pérez",
            "identidad": "0801-1990-12345",
            "correo": "juan@example.com",
            "telefono": "+504 1234-5678",
            "direccion": "Tegucigalpa, Honduras"
        },
        "academico": {
            "indice_global": 85.50,
            "indice_periodo": 90.00,
            "centro": "Centro Universitario Tegucigalpa",
            "carreras": [
                "Ingeniería en Sistemas",
                "Administración de Empresas"
            ]
        },
        "cuenta": {
            "username": "jperez2023"
        }
    }
}
 */

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
