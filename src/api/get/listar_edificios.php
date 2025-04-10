<?php
/**
 * API para obtener la lista básica de edificios.
 *
 * Retorna la lista de edificios con su ID y nombre en formato JSON.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/listar_edificios.php
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve la lista de edificios.
 * - 404 Not Found: No se encontraron edificios registrados.
 * - 500 Internal Server Error: Error en el servidor.
 *
 * Ejemplo respuesta:
 * 
 * [
 *   {
 *       "edificio_id": 1,
 *       "nombre": "Edificio de Ciencias"
 *   },
 *   {
 *       "edificio_id": 2,
 *       "nombre": "Edificio de Ingenierías"
 *   }
 * ]
 * 
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require_once __DIR__ . '/../../controllers/CentroController.php';

$edificioController = new CentroController();
$edificioController->getEdificios();
?>