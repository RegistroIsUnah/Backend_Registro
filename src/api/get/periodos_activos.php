<?php

// Archivo: src/api/get/periodos_activos.php

/**
 * API para obtener la lista de periodos académicos activos.
 *
 * @author Ruben Diaz
 * @version 1.0
 * 
 * Se consultan los registros en la tabla PeriodoAcademico cuyo estado sea 'ACTIVO'.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/periodos_activos.php
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve la lista de periodos académicos activos en formato JSON.
 * - 404 Not Found: No se encontraron periodos activos.
 * - 500 Internal Server Error: Error en la consulta.
 *
 * Ejemplo Respuesta
 * 
 * {
 *   "periodos": [
 *       {
 *           "periodo_academico_id": "1",
 *           "anio": "2025",
 *           "numero_periodo": "1",
 *           "estado": "ACTIVO"
 *       }
 *               ]
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../modules/config/Environments.php';
require_once __DIR__ . '/../../modules/config/DataBase.php';

// Instanciar la conexión
$db = new DataBase();
$conn = $db->getConnection();

// Consulta para obtener los periodos académicos activos
$sql = "SELECT periodo_academico_id, anio, numero_periodo, estado FROM PeriodoAcademico WHERE estado = 'ACTIVO'";
$result = $conn->query($sql);

$periodos = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $periodos[] = $row;
    }
}

if (empty($periodos)) {
    http_response_code(404);
    echo json_encode(['error' => 'No se encontraron periodos académicos activos']);
    exit;
}

http_response_code(200);
echo json_encode(['periodos' => $periodos]);
?>
