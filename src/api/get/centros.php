<?php
/**
 * API para obtener la lista de centros.
 *
 * Retorna la lista de centros en formato JSON.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/centros
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve la lista de centros.
 * - 500 Internal Server Error: En caso de error al obtener los datos.
 *
 *  Ejemplo respuesta
 * 
 * [
 *   {
 *       "centro_id": "1",
 *       "nombre": "Centro Universitario Regional Tegucigalpa"
 *   },
 *   {
 *       "centro_id": "2",
 *       "nombre": "Centro Universitario Regional San Pedro Sula"
 *   }
 * ]
 * 
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../controllers/CentroController.php';

$centroController = new CentroController();
$centroController->getCentros();
?>
