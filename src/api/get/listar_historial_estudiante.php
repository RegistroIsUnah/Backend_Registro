<?php
/**
 * API para obtener el historial de un estudiante.
 *
 * Se espera recibir el parámetro GET 'estudiante_id'.
 * La respuesta incluye el historial de todas las clases que el estudiante ha tomado,
 * con su código, nombre, créditos, sección, hora de la clase, periodo, calificación y estado del curso.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/historial_estudiante.php?estudiante_id=3
 *
 * Responde en formato JSON.
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Validar parámetro 'estudiante_id'
if (!isset($_GET['estudiante_id']) || !is_numeric($_GET['estudiante_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro estudiante_id es inválido o faltante']);
    exit;
}

$estudiante_id = (int) $_GET['estudiante_id'];

require_once __DIR__ . '/../../controllers/EstudianteController.php';

$controller = new EstudianteController();
$controller->obtenerHistorialEstudiante($estudiante_id);
?>