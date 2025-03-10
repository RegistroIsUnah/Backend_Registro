<?php

// Archivo: src/api/put/modificar_periodo.php

/**
 * API para modificar el estado de un período académico.
 *
 * @author Ruben Diaz
 * @version 1.0
 * 
 * Este endpoint permite actualizar el estado de un período académico a 'ACTIVO' o 'INACTIVO'.
 * Se espera que el ID del período se reciba en la URL (por ejemplo, como parámetro "id") y el nuevo estado en el body JSON.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/put/modificar_periodo.php?id=1
 *
 * Reglas:
 * - El nuevo estado debe ser 'ACTIVO' o 'INACTIVO'.
 *
 * Respuestas HTTP:
 * - 200 OK: El período se actualizó exitosamente.
 * - 400 Bad Request: Falta el parámetro id o el estado, o el estado enviado no es válido.
 * - 405 Method Not Allowed: Método HTTP no permitido.
 * - 500 Internal Server Error: Error al actualizar el período.
 *
 *  Ejemplo de envio
 * {
 *   "estado": "ACTIVO"
 * }
 * 
 * Ejemplo de respuesta
 * 
 * {
 *   "message": "El período académico se actualizó exitosamente",
 *   "periodo_academico_id": 1,
 *   "nuevo_estado": "INACTIVO"
 * }
 * 
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../modules/config/Environments.php';
require_once __DIR__ . '/../../modules/config/DataBase.php';

// Instanciar la conexión a la base de datos.
$db = new DataBase();
$conn = $db->getConnection();

// Validar que el método sea PUT.
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Obtener el ID del período desde la URL (por ejemplo, como parámetro "id").
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el parámetro id']);
    exit;
}

$periodo_id = intval($_GET['id']);

// Obtener el nuevo estado del body (JSON).
$input = json_decode(file_get_contents("php://input"), true);
if (!$input || !isset($input['estado']) || empty(trim($input['estado']))) {
    http_response_code(400);
    echo json_encode(['error' => 'Debe proporcionar el nuevo estado en el body']);
    exit;
}

$nuevo_estado = strtoupper(trim($input['estado']));

// Validar que el nuevo estado sea permitido.
$estados_permitidos = ['ACTIVO', 'INACTIVO'];
if (!in_array($nuevo_estado, $estados_permitidos)) {
    http_response_code(400);
    echo json_encode(['error' => 'El estado proporcionado no es válido. Use ACTIVO o INACTIVO']);
    exit;
}

// Preparar la consulta de actualización.
$sql = "UPDATE PeriodoAcademico SET estado = ? WHERE periodo_academico_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Error preparando la consulta: ' . $conn->error]);
    exit;
}

$stmt->bind_param("si", $nuevo_estado, $periodo_id);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(['message' => 'El período académico se actualizó exitosamente', 'periodo_academico_id' => $periodo_id, 'nuevo_estado' => $nuevo_estado]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar el período: ' . $stmt->error]);
}

$stmt->close();
?>