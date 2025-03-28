<?php
/**
 * API para asignar un usuario a un docente.
 *
 * Recibe los datos en formato JSON:
 * {
 *   "docente_id": 1,
 *   "username": "docente1",
 *   "password": "clave123"
 * }
 *
 * Respuestas:
 * - 200 OK: Retorna { "mensaje": "Credenciales correctamente asignadas" }
 * - 400/500: Error en la asignación.
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['docente_id']) || !isset($input['username']) || !isset($input['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan datos requeridos']);
    exit;
}

require_once __DIR__ . '/../../controllers/DocenteController.php';

$docenteController = new DocenteController();
$docenteController->asignarUsuarioDocente($input['docente_id'], $input['username'], $input['password']);
?>
