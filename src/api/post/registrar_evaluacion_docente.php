<?php
/**
 * API para registrar evaluaciones de docentes
 * 
 * Ejemplo de URL: 
 * servidor:puerto/api/post/registrar_evaluacion_docentes.php
 *
 * Métodos soportados:
 *  POST
 *
 * Se espera recibir en el cuerpo de la solicitud (application/json):
 *   - docente_id: int (ID del docente evaluado)
 *   - periodo_id: int (ID del periodo académico)
 *   - respuestas: JSON (formato: {"1": "Respuesta 1", "2": "Respuesta 2"})
 *
 * Requiere autenticación:
 *   - Debe enviar el ID de estudiante válido en sesión
 *   - Rol requerido: 'estudiante'
 *
 * Respuestas:
 *   - 200 OK: Evaluación registrada correctamente
 *   - 400 Bad Request: Faltan parámetros o formato inválido
 *   - 401 Unauthorized: No autenticado
 *   - 403 Forbidden: Rol no autorizado
 *   - 500 Internal Server Error: Error en el servidor
 *
 * @package API
 * @author Jose Vargas
 * @version 1.0
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Obtener datos de entrada
$data = json_decode(file_get_contents('php://input'), true);

require_once __DIR__ . '/../../controllers/EstudianteController.php';

$controller = new EstudianteController();
$controller->registrarEvaluacionDocente($data);