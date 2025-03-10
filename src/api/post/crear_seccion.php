<?php

// Archivo: src/api/post/crear_seccion.php

/**
 * API para crear una sección de una clase usando el procedimiento almacenado 'SP_crearSeccion'.
 *
 * @author Ruben Diaz
 * @version 1.0
 * 
 * Recibe un JSON con los siguientes parámetros:
 * - clase_id (int, obligatorio): ID de la clase.
 * - docente_id (int, obligatorio): ID del docente asignado.
 * - periodo_academico_id (int, obligatorio): ID del período académico.
 * - aula_id (int, obligatorio): ID del aula.
 * - hora_inicio (string, obligatorio): Hora de inicio en formato "HH:MM:SS".
 * - hora_fin (string, obligatorio): Hora de fin en formato "HH:MM:SS".
 * - cupos (int, obligatorio): Número de cupos disponibles.
 * - dias (string, obligatorio): Cadena con los días separados por comas (ej.: "Lunes,Martes,Miércoles, Jueves").
 *
 * El procedimiento almacenado 'SP_crearSeccion' se encarga de:
 * - Validar que la hora de inicio sea menor a la de fin.
 * - Obtener los créditos de la clase y calcular la duración de la sesión.
 * - Validar que, si la sección se imparte en varios días, el número de días sea igual a los créditos
 *   y que cada sesión tenga una duración de 1 hora; o, si es en un solo día, la duración total sea igual a los créditos.
 * - Verificar que en cada día no haya traslapes en el aula para el mismo período académico.
 * - Insertar la sección y los días correspondientes.
 * - Retornar el ID de la sección creada.
 *
 * Respuestas HTTP:
 * - 200 OK: Sección creada exitosamente, retornando el 'seccion_id'.
 * - 400 Bad Request: Datos faltantes o inválidos.
 * - 405 Method Not Allowed: Método HTTP no permitido.
 * - 500 Internal Server Error: Error en la ejecución del procedimiento almacenado.
 *
 * Ejemplo de envio
 *
 * {
 *   "clase_id": 1,
 *   "docente_id": 2,
 *   "periodo_academico_id": 1,
 *   "aula_id": 3,
 *   "hora_inicio": "08:00:00",
 *   "hora_fin": "09:00:00",
 *   "cupos": 30,
 *   "dias": "Lunes,Martes,Miércoles, Jueves"
 * }
 * 
 * Ejemplo de respuesta
 * {
 *   "message": "Sección creada exitosamente"
 *   "seccion_id": "5"
 * }
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../modules/config/Environments.php';
require_once __DIR__ . '/../../modules/config/DataBase.php';

$db = new DataBase();
$conn = $db->getConnection();

// Validar que el método sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Decodificar el JSON recibido
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'No se recibieron datos']);
    exit;
}

/**
 * Campos obligatorios para crear una sección.
 *
 * @var array $requiredFields
 */
$requiredFields = ['clase_id', 'docente_id', 'periodo_academico_id', 'aula_id', 'hora_inicio', 'hora_fin', 'cupos', 'dias'];
$missingFields = [];
foreach ($requiredFields as $field) {
    if (!isset($input[$field]) || (is_string($input[$field]) && trim($input[$field]) === '')) {
        $missingFields[] = $field;
    }
}
if (count($missingFields) > 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan campos obligatorios: ' . implode(', ', $missingFields)]);
    exit;
}

$clase_id = intval($input['clase_id']);
$docente_id = intval($input['docente_id']);
$periodo_academico_id = intval($input['periodo_academico_id']);
$aula_id = intval($input['aula_id']);
$hora_inicio = trim($input['hora_inicio']);
$hora_fin = trim($input['hora_fin']);
$cupos = intval($input['cupos']);
$dias = trim($input['dias']);

// Preparar la llamada al procedimiento almacenado SP_crearSeccion'
$stmt = $conn->prepare("CALL SP_crearSeccion(?, ?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Error preparando la consulta: ' . $conn->error]);
    exit;
}

// Los parámetros deben coincidir en orden y tipo con el procedimiento almacenado.
// En este caso: int, int, int, int, TIME, TIME, int, VARCHAR.
$stmt->bind_param("iiiissis", 
    $clase_id, 
    $docente_id, 
    $periodo_academico_id, 
    $aula_id, 
    $hora_inicio, 
    $hora_fin, 
    $cupos, 
    $dias
);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    if ($result) {
        $row = $result->fetch_assoc();
        http_response_code(200);
        echo json_encode(['message' => 'Sección creada exitosamente', 'seccion_id' => $row['seccion_id']]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo obtener el resultado del procedimiento']);
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al ejecutar el procedimiento: ' . $stmt->error]);
}
$stmt->close();
?>
