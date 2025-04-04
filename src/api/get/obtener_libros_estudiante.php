<?php
/**
 * Endpoint para obtener los libros asociados a las clases en las que el estudiante está matriculado o que ya cursó.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/obtener_libros_estudiante.php?estudiante_id=7
 *
 * Responde en formato JSON.
 *
 * Respuestas:
 *  - 200 OK: Devuelve un JSON con las clases y sus libros.
 *  - 400 Bad Request: Si falta el parámetro o es inválido.
 *  - 500 Internal Server Error: Si ocurre un error en la consulta.
 * 
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

if (!isset($_GET['estudiante_id']) || !is_numeric($_GET['estudiante_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro estudiante_id es inválido o faltante']);
    exit;
}

$estudiante_id = (int) $_GET['estudiante_id'];

require_once __DIR__ . '/../../controllers/LibroController.php';

$controller = new LibroController();
$controller->obtenerLibrosPorEstudiante($estudiante_id);
?>
