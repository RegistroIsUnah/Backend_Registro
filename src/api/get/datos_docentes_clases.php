<?php
/**
 * API para obtener los docentes de las clases en las que está matriculado el estudiante
 * 
 * Método: GET
 * Autenticación requerida: Sí (rol estudiante)
 * 
 * Respuestas:
 * - 200 OK: Devuelve lista de docentes y clases en formato JSON
 * - 403 Forbidden: Si no hay sesión activa o el rol no es estudiante
 * - 404 Not Found: Si el estudiante no está matriculado en ninguna clase
 * - 500 Internal Server Error: En caso de error en el servidor
 * 
 * Ejemplo de éxito:
 * {
 *   "success": true,
 *   "data": [
 *     {
 *       "clase_id": 15,
 *       "codigo_clase": "MAT-101",
 *       "nombre_clase": "Matemáticas Básicas",
 *       "docente_id": 23,
 *       "nombre_docente": "María",
 *       "apellido_docente": "González",
 *       "correo_docente": "maria.gonzalez@universidad.edu"
 *     },
 *     {
 *       "clase_id": 18,
 *       "codigo_clase": "FIS-201",
 *       "nombre_clase": "Física Moderna",
 *       "docente_id": 45,
 *       "nombre_docente": "Carlos",
 *       "apellido_docente": "Martínez",
 *       "correo_docente": "carlos.martinez@universidad.edu"
 *     }
 *   ]
 * }
 * @package API
 * @author Jose Vargas
 * @version 1.0
 * @param int $estudianteId
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require_once __DIR__ . '/../../controllers/EstudianteController.php';


$controller = new EstudianteController();
$controller->obtenerDocentesClases();

?>