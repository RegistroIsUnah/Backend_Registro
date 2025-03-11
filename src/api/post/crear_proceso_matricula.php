<?php

// Archivo: src/api/post/crear_proceso_matricula.php

/**
 * API para crear un proceso de matrícula o de adiciones/cancelaciones.
 *
 * @author Ruben Diaz
 * @version 1.0
 * 
 * Se espera recibir un JSON con:
 * - periodo_academico_id (int): ID del período académico al que pertenece el proceso.
 * - tipo_proceso (string): 'MATRICULA' o 'ADICIONES_CANCELACIONES'.
 * - fecha_inicio (string): Fecha y hora de inicio del proceso, formato "YYYY-MM-DD HH:MM:SS".
 * - fecha_fin (string): Fecha y hora de fin del proceso, formato "YYYY-MM-DD HH:MM:SS".
 *
 * Reglas:
 * - El período académico debe estar activo.
 * - fecha_fin debe ser mayor que fecha_inicio.
 * - El tipo_proceso debe ser 'MATRICULA' o 'ADICIONES_CANCELACIONES'.
 *
 * El campo estado se establece por defecto en 'ACTIVO'.
 * Cuando llegue la fecha_fin, mediante un Event Scheduler (o proceso similar) se actualizará el estado a 'INACTIVO'.
 *
 * Respuestas HTTP:
 * - 200 OK: Proceso creado exitosamente.
 * - 400 Bad Request: Datos faltantes, inválidos o el período académico no está activo.
 * - 405 Method Not Allowed: Método HTTP no permitido.
 * - 500 Internal Server Error: Error en la inserción en la base de datos.
 *
 * Ejemplo de Envio
 * {
 *   "periodo_academico_id": 1,
 *   "tipo_proceso": "MATRICULA",
 *   "fecha_inicio": "2025-03-15 08:00:00",
 *   "fecha_fin": "2025-03-15 17:00:00"
 * }
 * 
 * Ejemplo respuesta
 * {
 *   "message": "Proceso de matrícula creado exitosamente",
 *   "proceso_id": 1,
 *   "estado": "ACTIVO"
 * }
 *
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../modules/config/Environments.php';
require_once __DIR__ . '/../../modules/config/DataBase.php';

// Instanciar la conexión a la base de datos.
$db = new DataBase();
$conn = $db->getConnection();

// Validar que el método sea POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Obtener los datos del body (JSON).
$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'No se recibieron datos']);
    exit;
}

// Campos obligatorios.
$requiredFields = ['periodo_academico_id', 'tipo_proceso', 'fecha_inicio', 'fecha_fin'];
$missingFields = [];
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

$periodo_academico_id = intval($input['periodo_academico_id']);
$tipo_proceso = strtoupper(trim($input['tipo_proceso']));
$fecha_inicio = trim($input['fecha_inicio']);
$fecha_fin = trim($input['fecha_fin']);

// Validar que el tipo_proceso sea permitido.
$tiposPermitidos = ['MATRICULA', 'ADICIONES_CANCELACIONES'];
if (!in_array($tipo_proceso, $tiposPermitidos)) {
    http_response_code(400);
    echo json_encode(['error' => 'El tipo_proceso debe ser MATRÍCULA o ADICIONES_CANCELACIONES']);
    exit;
}

// Validar formato de fechas.
if (strtotime($fecha_inicio) === false || strtotime($fecha_fin) === false) {
    http_response_code(400);
    echo json_encode(['error' => 'Formato de fecha inválido. Use "YYYY-MM-DD HH:MM:SS"']);
    exit;
}

// Validar que fecha_fin sea mayor a fecha_inicio.
if (strtotime($fecha_fin) <= strtotime($fecha_inicio)) {
    http_response_code(400);
    echo json_encode(['error' => 'La fecha_fin debe ser mayor que la fecha_inicio']);
    exit;
}

// Verificar que el período académico esté activo.
$sqlPeriodo = "SELECT estado FROM PeriodoAcademico WHERE periodo_academico_id = ?";
$stmtPeriodo = $conn->prepare($sqlPeriodo);
if (!$stmtPeriodo) {
    http_response_code(500);
    echo json_encode(['error' => 'Error preparando la consulta del período: ' . $conn->error]);
    exit;
}
$stmtPeriodo->bind_param("i", $periodo_academico_id);
$stmtPeriodo->execute();
$resultPeriodo = $stmtPeriodo->get_result();
if ($resultPeriodo->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Período académico no encontrado']);
    exit;
}
$periodo = $resultPeriodo->fetch_assoc();
$stmtPeriodo->close();

if ($periodo['estado'] !== 'ACTIVO') {
    http_response_code(400);
    echo json_encode(['error' => 'El período académico no está activo']);
    exit;
}

// Insertar el proceso de matrícula.
$sqlInsert = "INSERT INTO ProcesoMatricula (periodo_academico_id, tipo_proceso, fecha_inicio, fecha_fin, estado) VALUES (?, ?, ?, ?, 'ACTIVO')";
$stmtInsert = $conn->prepare($sqlInsert);
if (!$stmtInsert) {
    http_response_code(500);
    echo json_encode(['error' => 'Error preparando la consulta de inserción: ' . $conn->error]);
    exit;
}
$stmtInsert->bind_param("isss", $periodo_academico_id, $tipo_proceso, $fecha_inicio, $fecha_fin);

if ($stmtInsert->execute()) {
    $proceso_id = $stmtInsert->insert_id;
    http_response_code(200);
    echo json_encode([
        'message' => 'Proceso de matrícula creado exitosamente',
        'proceso_id' => $proceso_id,
        'estado' => 'ACTIVO'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al crear el proceso: ' . $stmtInsert->error]);
}

$stmtInsert->close();
?>
