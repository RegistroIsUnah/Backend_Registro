<?php

//Archivo: src/api/get/aulas_edificio.php

/**
 * API para obtener la lista de aulas asociadas a un edificio.
 *
 * @author Ruben Diaz
 * @version 1.0
 * 
 * Se espera recibir el ID del edificio mediante el parámetro "edificio_id" en la query string.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/aulas_edificio.php?edificio_id=2
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve la lista de aulas en formato JSON.
 * - 400 Bad Request: Falta el parámetro edificio_id o es inválido.
 * - 404 Not Found: No se encontraron aulas para el edificio.
 * - 500 Internal Server Error: Error en la consulta.
 *
 *Ejemplo Respuesta
 * {
 *   "aulas": [
 *       {
 *           "aula_id": 3,
 *           "nombre": "Aula 201",
 *           "capacidad": 60
 *       }
 *            ]
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../modules/config/Environments.php';
require_once __DIR__ . '/../../modules/config/DataBase.php';

// Instanciar la conexión
$db = new DataBase();
$conn = $db->getConnection();

// Validar que se reciba el parámetro "edificio_id"
if (!isset($_GET['edificio_id']) || empty($_GET['edificio_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el parámetro edificio_id']);
    exit;
}

$edificio_id = intval($_GET['edificio_id']);

// Consulta para obtener las aulas asociadas al edificio
$sql = "SELECT aula_id, nombre, capacidad FROM Aula WHERE edificio_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Error preparando la consulta: ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $edificio_id);
$stmt->execute();
$result = $stmt->get_result();

$aulas = [];
while ($row = $result->fetch_assoc()) {
    $aulas[] = $row;
}

$stmt->close();

if (empty($aulas)) {
    http_response_code(404);
    echo json_encode(['error' => 'No se encontraron aulas para el edificio especificado']);
    exit;
}

http_response_code(200);
echo json_encode(['aulas' => $aulas]);
?>
