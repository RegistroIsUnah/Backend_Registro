<?php
/**
 * API para listar todos los departamentos.
 *
 * Este endpoint devuelve la lista de departamentos en formato JSON.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/departamentos
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve la lista de departamentos.
 * - 500 Internal Server Error: Si ocurre un error en la consulta.
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once __DIR__ . '/../../controllers/DepartamentoController.php';

$controller = new DepartamentoController();
$controller->listarDepartamentos();
?>
