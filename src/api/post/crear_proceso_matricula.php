<?php
/**
 * API para crear un proceso de matrícula.
 * 
 * Ejemplo de URL:
 * servidor:puerto/api/post/crear_proceso_matricula
 *
 * Este endpoint recibe datos en formato JSON:
 * {
 *   "periodo_academico_id": 3,
 *   "tipo_proceso": "MATRICULA",
 *   "fecha_inicio": "2025-03-10 00:00:00",
 *   "fecha_fin": "2025-06-10 23:59:59"
 * }
 *
 * Al crearse, el proceso se inserta con estado 'ACTIVO' si la fecha_fin es futura o 'INACTIVO' si ya pasó.
 * Se recomienda configurar un evento en MySQL para actualizar automáticamente el estado a 'INACTIVO'
 * cuando se cumpla la fecha_fin.
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve el ID del proceso de matrícula creado y un mensaje de éxito.
 * - 400 Bad Request: Datos faltantes o formato inválido.
 * - 500 Internal Server Error: Error durante la creación del proceso.
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos JSON inválidos']);
    exit;
}

require_once __DIR__ . '/../../controllers/ProcesoMatriculaController.php';

$procesoController = new ProcesoMatriculaController();
$procesoController->crearProcesoMatricula($input);
?>
