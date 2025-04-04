<?php
/**
 * API para listar las carreras con los exámenes y sus puntajes.
 *
 * Este endpoint devuelve la lista de todas las carreras con los exámenes asociados y sus puntajes correspondientes.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/listar_carreras_examenes.php
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve la lista de carreras con exámenes y puntajes.
 * - 500 Internal Server Error: Si ocurre un error en la consulta.
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once __DIR__ . '/../../controllers/CarreraExamenController.php';

$controller = new CarreraExamenController();
$controller->listarCarrerasConExamenesYPuntajes();
?>
