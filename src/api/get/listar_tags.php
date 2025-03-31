<?php
/**
 * API para listar todos los tags.
 *
 * Este endpoint devuelve la lista de tags con su información en formato JSON.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/listar_tags
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve la lista de tags.
 * - 500 Internal Server Error: Si ocurre un error en la consulta.
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once __DIR__ . '/../../controllers/TagController.php';

$controller = new TagController();
$controller->listarTags();
?>
