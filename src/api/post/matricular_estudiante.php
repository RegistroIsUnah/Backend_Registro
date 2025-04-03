<?php
/**
 * API para matricular a un estudiante en la sección y, opcionalmente,
 * en el laboratorio seleccionado (si la clase lo tiene).
 *
 * Método: POST
 *
 * Parámetros (en form-data o JSON):
 *  - estudiante_id (int): ID del estudiante.
 *  - seccion_id (int): ID de la sección principal.
 *  - tipo_proceso (string): Tipo de proceso, e.g., "MATRICULA".
 *  - laboratorio_id (int, opcional): ID del laboratorio seleccionado.
 * 
 * Ejemplo de URL 
 * servidor:puerto/api/post/matricular_estudiante
 * 
 * Metodos soportados:
 *  POST
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.2
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

// Permitir recibir datos JSON
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

if (empty($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos no proporcionados']);
    exit;
}

require_once __DIR__ . '/../../controllers/MatriculaController.php';

$matriculaController = new MatriculaController();
$matriculaController->matricularEstudiante();
?>