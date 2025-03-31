<?php
/**
 * API para obtener la lista de aulas asociadas a un edificio.
 *
 * Se espera recibir el ID del edificio mediante el parámetro "edificio_id" en la query string.
 *
 * Ejemplo de URL:
 *   servidor:puerto/api/get/aulas_edificio?edificio_id=2
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve la lista de aulas en formato JSON.
 * - 400 Bad Request: Falta el parámetro edificio_id o es inválido.
 * - 404 Not Found: No se encontraron aulas para el edificio.
 * - 500 Internal Server Error: Error en la consulta.
 *
 * Ejemplo de respuesta:
 * {
 *   "aulas": [
 *       {
 *           "aula_id": 3,
 *           "nombre": "Aula 201",
 *           "capacidad": 60
 *       }
 *   ]
 * }
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

// Validar que se reciba el parámetro "edificio_id"
if (!isset($_GET['edificio_id']) || empty($_GET['edificio_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el parámetro edificio_id']);
    exit;
}

$edificio_id = intval($_GET['edificio_id']);

require_once __DIR__ . '/../../controllers/AulaController.php';

$aulaController = new AulaController();
$aulaController->getAulasPorEdificio($edificio_id);
?>
