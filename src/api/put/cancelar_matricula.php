<?php

// Archivo: src/api/put/cancelar_matricula.php

/**
 * API para cancelar la matrícula de un estudiante en una sección específica.
 *
 * @author Ruben Diaz
 * @version 1.0
 * 
 * No lo he probado
 * 
 * En este endpoint se espera recibir un JSON con:
 * - estudiante_id (int): ID del estudiante.
 * - seccion_id (int): ID de la sección en la que se matriculó el estudiante.
 *
 * El endpoint buscará la matrícula activa o en espera para ese estudiante y sección,
 * la actualizará a "CANCELADA" y llamará al procedimiento almacenado SP_actualizarListaEspera
 * para reasignar el cupo al siguiente estudiante en lista de espera (si existe).
 *
 * Respuestas HTTP:
 * - 200 OK: Matrícula cancelada y lista de espera actualizada exitosamente.
 * - 400 Bad Request: Faltan datos o no se encontró matrícula para ese estudiante en esa sección.
 * - 405 Method Not Allowed: Método HTTP no permitido.
 * - 500 Internal Server Error: Error en la operación de la base de datos.
 *
 * Ejemplo de Envio:
 * {
 *   "estudiante_id": 10,
 *   "seccion_id": 123
 * }
 * 
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../modules/config/Environments.php';
require_once __DIR__ . '/../../modules/config/DataBase.php';

$db   = new DataBase();
$conn = $db->getConnection();

// Verificar que el método sea PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Obtener datos del body (JSON)
$input = json_decode(file_get_contents("php://input"), true);
if (!$input || !isset($input['estudiante_id']) || !isset($input['seccion_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan campos obligatorios: estudiante_id y seccion_id']);
    exit;
}

$estudiante_id = intval($input['estudiante_id']);
$seccion_id    = intval($input['seccion_id']);

// Buscar la matrícula activa o en espera para ese estudiante en la sección
$sqlSelect = "SELECT matricula_id FROM Matricula 
              WHERE estudiante_id = ? 
                AND seccion_id = ? 
                AND estado IN ('MATRICULADO', 'EN ESPERA')
              LIMIT 1";
$stmtSelect = $conn->prepare($sqlSelect);
if (!$stmtSelect) {
    http_response_code(500);
    echo json_encode(['error' => 'Error preparando la consulta: ' . $conn->error]);
    exit;
}
$stmtSelect->bind_param("ii", $estudiante_id, $seccion_id);
$stmtSelect->execute();
$resultSelect = $stmtSelect->get_result();
if ($resultSelect->num_rows === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'No se encontró matrícula activa o en espera para el estudiante en esa sección']);
    exit;
}
$row = $resultSelect->fetch_assoc();
$matricula_id = intval($row['matricula_id']);
$stmtSelect->close();

// Cancelar la matrícula actualizando su estado a 'CANCELADA'
$sqlUpdate = "UPDATE Matricula SET estado = 'CANCELADA' WHERE matricula_id = ?";
$stmtUpdate = $conn->prepare($sqlUpdate);
if (!$stmtUpdate) {
    http_response_code(500);
    echo json_encode(['error' => 'Error preparando la actualización: ' . $conn->error]);
    exit;
}
$stmtUpdate->bind_param("i", $matricula_id);
if (!$stmtUpdate->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al cancelar la matrícula: ' . $stmtUpdate->error]);
    exit;
}
$stmtUpdate->close();

// Llamar al procedimiento almacenado para actualizar la lista de espera
$stmtEvento = $conn->prepare("CALL SP_actualizarListaEspera(?)");
if (!$stmtEvento) {
    http_response_code(500);
    echo json_encode(['error' => 'Error preparando la llamada al procedimiento SP_actualizarListaEspera: ' . $conn->error]);
    exit;
}
$stmtEvento->bind_param("i", $seccion_id);
if (!$stmtEvento->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al ejecutar actualizarListaEspera: ' . $stmtEvento->error]);
    exit;
}
$stmtEvento->close();

// Responder
http_response_code(200);
echo json_encode(['message' => 'Matrícula cancelada y lista de espera actualizada exitosamente']);
?>
