<?php

// Archivo: src/api/post/matricula_estudiante.php

/**
 * API para matricular a un estudiante en una sección (y, si aplica, en el laboratorio asociado)
 * mediante el procedimiento almacenado sp_matricular_estudiante.
 *
 * @author Ruben Diaz
 * @version 1.0
 * 
 * No lo he probado aun.
 * 
 * Se espera recibir en el body JSON:
 * - estudiante_id (int)
 * - seccion_id (int)
 * - tipo_proceso (string): 'MATRICULA' o 'ADICIONES_CANCELACIONES'
 *
 * Ejemplo de Envio:
 * {
 *   "estudiante_id": 10,
 *   "seccion_id": 123,
 *   "tipo_proceso": "MATRICULA"
 * }
 *
 * La respuesta incluye el ID de matrícula para la sección principal, el estado asignado y, si corresponde,
 * el orden de inscripción. Si la clase tiene laboratorio, también se realiza la matrícula en la sección de laboratorio.
 *
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../modules/config/Environments.php';
require_once __DIR__ . '/../../modules/config/DataBase.php';

$db   = new DataBase();
$conn = $db->getConnection();

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Obtener datos del body (JSON)
$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'No se recibieron datos']);
    exit;
}

// Validar campos obligatorios
$requiredFields = ['estudiante_id', 'seccion_id', 'tipo_proceso'];
$missingFields  = [];
foreach ($requiredFields as $field) {
    if (!isset($input[$field]) || trim($input[$field]) === '') {
        $missingFields[] = $field;
    }
}
if (count($missingFields) > 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan campos obligatorios: ' . implode(', ', $missingFields)]);
    exit;
}

$estudiante_id = intval($input['estudiante_id']);
$seccion_id    = intval($input['seccion_id']);
$tipo_proceso  = strtoupper(trim($input['tipo_proceso']));

// Validar tipo_proceso
$tiposPermitidos = ['MATRICULA', 'ADICIONES_CANCELACIONES'];
if (!in_array($tipo_proceso, $tiposPermitidos)) {
    http_response_code(400);
    echo json_encode(['error' => 'El tipo_proceso debe ser MATRICULA o ADICIONES_CANCELACIONES']);
    exit;
}

// Preparar la llamada al procedimiento almacenado SP_matricular_estudiante
$stmt = $conn->prepare("CALL SP_matricular_estudiante(?, ?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Error preparando la consulta: ' . $conn->error]);
    exit;
}

$stmt->bind_param("iis", $estudiante_id, $seccion_id, $tipo_proceso);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    if ($result) {
        $row = $result->fetch_assoc();
        http_response_code(200);
        echo json_encode($row);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'No se obtuvo resultado del procedimiento']);
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al ejecutar el procedimiento: ' . $stmt->error]);
}

$stmt->close();
?>
