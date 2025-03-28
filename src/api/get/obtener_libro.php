<?php
/**
 * Endpoint para obtener los detalles de un libro para un estudiante solo obtendra los libros que estan activos.
 *
 * Permite obtener la información completa de un libro (datos principales, autores y tags).
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/obtener_libro?libro_id=5
 *
 * Respuestas:
 *  - 200 OK: Devuelve el libro en formato JSON.
 *  - 400 Bad Request: Si falta el parámetro o es inválido.
 *  - 404 Not Found: Si el libro no se encuentra o está inactivo.
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

if (!isset($_GET['libro_id']) || !is_numeric($_GET['libro_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro libro_id es inválido o faltante']);
    exit;
}

$libro_id = (int) $_GET['libro_id'];

require_once __DIR__ . '/../../controllers/LibroController.php';

$controller = new LibroController();
$controller->obtenerLibro($libro_id);
?>
