<?php
/**
 * Endpoint para obtener los libros de las clases que pertenecen a un departamento.
 *
 * La solicitud debe incluir el parámetro GET 'departamentoId'.
 *
 * Ejemplo de URL:
 *    servidor:puerto/api/get/obtener_libros_por_departamento.php?departamentoId=2
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
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if (!isset($_GET['departamentoId']) || !is_numeric($_GET['departamentoId'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro departamentoId es inválido o faltante']);
    exit;
}

$departamentoId = (int) $_GET['departamentoId'];

require_once __DIR__ . '/../../controllers/LibroController.php';

$controller = new LibroController();
$controller->obtenerLibrosPorDepartamento($departamentoId);
?>
