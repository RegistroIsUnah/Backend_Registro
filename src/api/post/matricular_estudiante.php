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
 * Ejemplo de solicitud sin laboratorio:
 * {
 *    "estudiante_id": 1,
 *    "seccion_id": 10,
 *    "tipo_proceso": "MATRICULA"
 * }
 *
 * Ejemplo de solicitud con laboratorio:
 * {
 *    "estudiante_id": 1,
 *    "seccion_id": 10,
 *    "tipo_proceso": "MATRICULA",
 *    "laboratorio_id": 20
 * }
 *
 * Respuestas:
 * - 200 OK: Devuelve un JSON con el resultado de la matrícula principal.
 * - 400 Bad Request: Si faltan parámetros obligatorios.
 * - 500 Internal Server Error: Si ocurre un error en la ejecución del procedimiento.
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.1
 * 
 */

header('Content-Type: application/json');

// Validar que se hayan recibido los parámetros obligatorios
if (
    !isset($_POST['estudiante_id']) || 
    !isset($_POST['seccion_id']) || 
    !isset($_POST['tipo_proceso'])
) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Parámetros requeridos: estudiante_id, seccion_id, tipo_proceso'
    ]);
    exit;
}

$estudiante_id = intval($_POST['estudiante_id']);
$seccion_id    = intval($_POST['seccion_id']);
$tipo_proceso  = $_POST['tipo_proceso'];

// El parámetro laboratorio_id es opcional. Si no se envía, se asigna 0.
$laboratorio_id = isset($_POST['laboratorio_id']) ? intval($_POST['laboratorio_id']) : 0;

require_once __DIR__ . '/../../controllers/MatriculaController.php';

$matriculaController = new MatriculaController();
$matriculaController->matricularEstudiante($estudiante_id, $seccion_id, $tipo_proceso, $laboratorio_id);
?>
