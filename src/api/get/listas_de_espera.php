<?php
/**
 * API para obtener las listas de espera de secciones por departamento.
 *
 * Permite obtener las listas de espera de estudiantes para las secciones de un departamento específico.
 * Se requiere el parámetro GET 'seccionId' para filtrar por departamento.
 * 
 * Ejemplo de URL:
 * servidor:puerto/api/get/listas_de_espera.php?seccionId=5
 * 
 * Respuestas HTTP:
 * - 200 OK: Devuelve las listas de espera en formato JSON.
 * - 400 Bad Request: Si el parámetro 'seccionId' es inválido o faltante.
 * - 404 Not Found: Si no hay listas de espera para el departamento especificado.
 * - 500 Internal Server Error: En caso de error al obtener los datos.
 *
 * Ejemplo de respuesta:
 * 
 *   {
 *       "seccion_id": 15,
 *       "lista_espera": [
 *           {
 *               "estudiante_id": 45,
 *               "nombre": "Ana",
 *               "apellido": "García",
 *               "correo_personal": "ana.garcia@mail.com",
 *               "fecha_solicitud": "2025-03-10 09:30:00"
 *           },
 *           {
 *               "estudiante_id": 78,
 *               "nombre": "Carlos",
 *               "apellido": "Martínez",
 *               "correo_personal": "carlos.mtz@mail.com",
 *               "fecha_solicitud": "2025-03-10 10:15:00"
 *           }
 *       ]
 *   }
 * 
 *
 * @package API
 * @author Jose Vargas
 * @version 1.1
 * GET /api/get/listas_de_espera.php?seccionId=15
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../controllers/MatriculaController.php';

$controller = new MatriculaController();
$controller->obtenerListaPorSeccion();
?>