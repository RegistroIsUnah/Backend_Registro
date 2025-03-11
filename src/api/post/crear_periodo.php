<?php

// Archivo: src/api/post/crear_periodo.php

/**
 * API para crear un período académico.
 * 
 * @author Ruben Diaz
 * @version 1.0
 *
 * Este endpoint permite crear un nuevo período académico. Se espera recibir un JSON con:
 * - anio (int): Año del período.
 * - numero_periodo (string): Número del período, debe ser '1', '2' o '3'.
 * - fecha_inicio (string): Fecha y hora de inicio, en formato "YYYY-MM-DD HH:MM:SS".
 * - fecha_fin (string): Fecha y hora de fin, en formato "YYYY-MM-DD HH:MM:SS".
 *
 * El estado se establece por defecto en 'ACTIVO'.
 * Se valida que la fecha_fin sea mayor a la fecha_inicio.
 *
 * Respuestas HTTP:
 * - 200 OK: Período académico creado exitosamente.
 * - 400 Bad Request: Datos faltantes, inválidos o fecha_fin anterior a fecha_inicio.
 * - 405 Method Not Allowed: Método HTTP no permitido.
 * - 500 Internal Server Error: Error en la inserción en la base de datos.
 *
 * Ejemplo de envio
 * {
 *   "anio": 2025,
 *   "numero_periodo": "1",
 *   "fecha_inicio": "2025-03-10 00:00:00",
 *   "fecha_fin": "2025-06-10 23:59:59"
 * }
 * 
 * Ejemplo de respuesta
 * {
 *   "message": "Período académico creado exitosamente",
 *  "periodo_academico_id": 4,
 *   "estado": "ACTIVO"
 * }
 * 
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

// Obtener datos del body (JSON)
$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'No se recibieron datos']);
    exit;
}

// Validar campos obligatorios
$requiredFields = ['anio', 'numero_periodo', 'fecha_inicio', 'fecha_fin'];
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

$anio = intval($input['anio']);
$numero_periodo = trim($input['numero_periodo']);
$fecha_inicio = trim($input['fecha_inicio']);
$fecha_fin = trim($input['fecha_fin']);

// Validar que el número de período sea uno de los permitidos
$permitidos = ['1', '2', '3'];
if (!in_array($numero_periodo, $permitidos)) {
    http_response_code(400);
    echo json_encode(['error' => 'El número de período debe ser 1, 2 o 3']);
    exit;
}

// Validar que las fechas tengan formato válido y que fecha_fin sea mayor a fecha_inicio
if (strtotime($fecha_inicio) === false || strtotime($fecha_fin) === false) {
    http_response_code(400);
    echo json_encode(['error' => 'Formato de fecha inválido. Use "YYYY-MM-DD HH:MM:SS"']);
    exit;
}

if (strtotime($fecha_fin) <= strtotime($fecha_inicio)) {
    http_response_code(400);
    echo json_encode(['error' => 'La fecha_fin debe ser mayor que la fecha_inicio']);
    exit;
}

// Preparar la consulta de inserción
$sql = "INSERT INTO PeriodoAcademico (anio, numero_periodo, fecha_inicio, fecha_fin, estado) VALUES (?, ?, ?, ?, 'ACTIVO')";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Error preparando la consulta: ' . $conn->error]);
    exit;
}

// Enlazar parámetros: anio (i), numero_periodo (s), fecha_inicio (s), fecha_fin (s)
$stmt->bind_param("isss", $anio, $numero_periodo, $fecha_inicio, $fecha_fin);

if ($stmt->execute()) {
    $periodo_id = $stmt->insert_id;
    http_response_code(200);
    echo json_encode([
        'message' => 'Período académico creado exitosamente',
        'periodo_academico_id' => $periodo_id,
        'estado' => 'ACTIVO'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al crear el período académico: ' . $stmt->error]);
}

$stmt->close();
?>