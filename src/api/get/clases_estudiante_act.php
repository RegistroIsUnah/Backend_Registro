<?php
/*
 * API GET para obtener las clases actuales de un estudiante
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

 servidor:puerto/api/get/clases_estudiante_act.php?estudianteId=5


 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if (!isset($_GET['estudianteId']) || !is_numeric($_GET['estudianteId'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro estudianteId es inválido o faltante']);
    exit;
}

$estudianteId = (int) $_GET['estudianteId'];

require_once __DIR__ . '/../../controllers/EstudianteController.php';

$controller = new EstudianteController();
$controller->obtenerClasesActEstudiante($estudianteId);
?>
