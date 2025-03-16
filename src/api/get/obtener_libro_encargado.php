<?php
/**
 * Endpoint para obtener los detalles completos de un libro para un encargado de biblioteca.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/obtener_libro_encargado?libro_id=5
 *
 * Respuestas:
 *  - 200 OK: Devuelve el libro en formato JSON.
 *  - 400 Bad Request: Si el parámetro libro_id es inválido o falta.
 *  - 404 Not Found: Si el libro no se encuentra.
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

header('Content-Type: application/json');

if (!isset($_GET['libro_id']) || !is_numeric($_GET['libro_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro libro_id es inválido o faltante']);
    exit;
}

$libro_id = (int) $_GET['libro_id'];

require_once __DIR__ . '/../../controllers/LibroController.php';

$controller = new LibroController();
$controller->obtenerLibroCompleto($libro_id);
?>
