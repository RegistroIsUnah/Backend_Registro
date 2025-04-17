<?php
/**
 * API para obtener las evaluaciones de docentes.
 *
 * Permite filtrar las evaluaciones por sección a través del parámetro GET 'seccion_id'.
 * Ejemplo de URL:
 * servidor:puerto/api/get/evaluaciones.php?seccion_id=1
 * Si no se especifica, retorna todas las evaluaciones.
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve la lista de evaluaciones de docentes en formato JSON.
 * - 500 Internal Server Error: En caso de error al obtener los datos.
 *
 * Ejemplo respuesta:
 * [
 *   {
 *       "docente_id": "1",
 *       "seccion_id": "3",
 *       "resumen_respuestas": [
 *           {
 *               "pregunta_id": 1,
 *               "respuestas": ["Sí", "No", "Sí"]
 *           },
 *           {
 *               "pregunta_id": 2,
 *               "respuestas": ["Regular", "Excelente"]
 *           }
 *       ]
 *   },
 *   {
 *       "docente_id": "2",
 *       "seccion_id": "4",
 *       "resumen_respuestas": [
 *           {
 *               "pregunta_id": 1,
 *               "respuestas": ["No", "Sí"]
 *           },
 *           {
 *               "pregunta_id": 2,
 *               "respuestas": ["Bueno"]
 *           }
 *       ]
 *   }
 * ]
 * 
 * @package API
 * @author Jose Vargas
 * @version 1.0
 * 
 */


$origin = $_SERVER['HTTP_ORIGIN'] ?? '';


header('Content-Type: application/json');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Credentials: true");

// Manejar solicitud OPTIONS para CORS preflight
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Obtener el parámetro seccion_id si se envía; si no, será null.
$seccion_id = isset($_GET['seccion_id']) ? intval($_GET['seccion_id']) : null;

require_once __DIR__ . '/../../controllers/DocenteController.php';

$docenteController = new DocenteController();
$docenteController->resumenEvaluacionesPorSeccion($seccion_id);
?>
