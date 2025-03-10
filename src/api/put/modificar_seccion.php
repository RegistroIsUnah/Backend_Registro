<?php

// Archivo: src/api/put/modificar_seccion.php

/**
 * API para modificar una sección existente usando el procedimiento almacenado SP_modificar_seccion.
 *
 * @author Ruben Diaz
 * @version 1.0
 * 
 * Se espera una solicitud PUT a la URL: api/put/modificar_seccion.php?id=123
 * (En este ejemplo, el id se obtiene de id=123.
 *
 * Los campos a modificar se envían en el body JSON; los que se quieran dejar sin modificar se pueden enviar como null.
 *
 * Ejemplo de envio
 * 
 * {
 *   "estado": "CANCELADA",
 *   "motivo_cancelacion": "Problemas administrativos"
 * }
 *
 * Ejemplo de envio
 * {
 *   "docente_id": 5,
 *   "aula_id": 10
 * }
 *
 * Respuestas HTTP:
 * - 200 OK: Sección modificada exitosamente.
 * - 400 Bad Request: Datos faltantes o inválidos.
 * - 405 Method Not Allowed: Método HTTP no permitido.
 * - 500 Internal Server Error: Error al ejecutar el procedimiento almacenado.
 *
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../modules/config/Environments.php';
require_once __DIR__ . '/../../modules/config/DataBase.php';

// Instanciar la conexión
$db = new DataBase();
$conn = $db->getConnection();

// Verificar que el método sea PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Obtener el ID de la sección desde la URL (por ejemplo, api/put/modificar_seccion.php?id=123)
// En este ejemplo suponemos que el ID se pasa como parámetro "id" en la query string.
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el ID de la sección en la URL']);
    exit;
}

$seccion_id = intval($_GET['id']);

// Obtener los datos enviados en el body (JSON)
$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'No se recibieron datos en el body']);
    exit;
}

// Los parámetros opcionales a modificar:
$docente_id         = isset($input['docente_id']) ? intval($input['docente_id']) : null;
$aula_id            = isset($input['aula_id']) ? intval($input['aula_id']) : null;
$estado             = isset($input['estado']) ? trim($input['estado']) : null;
$motivo_cancelacion = isset($input['motivo_cancelacion']) ? trim($input['motivo_cancelacion']) : null;

// Preparar la llamada al procedimiento almacenado SP_modificar_seccion
$stmt = $conn->prepare("CALL SP_modificar_seccion(?, ?, ?, ?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Error preparando la consulta: ' . $conn->error]);
    exit;
}

// Bind parameters: 
// p_seccion_id -> i, p_docente_id -> i, p_aula_id -> i, p_estado -> s, p_motivo_cancelacion -> s.
$stmt->bind_param("iiiss", $seccion_id, $docente_id, $aula_id, $estado, $motivo_cancelacion);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(['message' => 'Sección modificada exitosamente']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar la sección: ' . $stmt->error]);
}

$stmt->close();
?>