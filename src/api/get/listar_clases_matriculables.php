<?php
/**
 * API para listar las clases matriculables para un estudiante.
 *
 * Este endpoint devuelve la lista de clases que el estudiante puede matricular, filtradas por:
 * - Departamento
 * - Carreras a las que pertenece el estudiante (vía EstudianteCarrera)
 * - Requisitos (si la clase tiene requisito, el estudiante debe haber aprobado alguna sección de esa clase)
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/listar_clases_matriculables?departamento_id=3&estudiante_id=10
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve un arreglo de clases.
 * - 400 Bad Request: Si faltan datos.
 * - 500 Internal Server Error: Si ocurre un error.
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.1
 * 
 */

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = [
        'departamento_id' => isset($_GET['departamento_id']) ? $_GET['departamento_id'] : null,
        'estudiante_id'   => isset($_GET['estudiante_id']) ? $_GET['estudiante_id'] : null,
    ];
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

if (!$data || !isset($data['departamento_id'], $data['estudiante_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan datos: departamento_id y estudiante_id son requeridos']);
    exit;
}

require_once __DIR__ . '/../../controllers/ClaseMatriculableController.php';

$controller = new ClaseController();
$controller->listarClasesMatriculables($data);
?>
