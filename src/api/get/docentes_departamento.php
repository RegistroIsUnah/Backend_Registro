<?php

// Archivo: src/api/get/docentes_departamento.php

/**
 * API para obtener la lista de docentes asociados a un departamento.
 *
 * @author Ruben Diaz
 * @version 1.0
 * 
 * Se espera recibir el ID del departamento mediante el parámetro "dept_id" en la query string.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/docentes_departamento.php?dept_id=3
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve la lista de docentes en formato JSON.
 * - 400 Bad Request: Falta el parámetro dept_id o es inválido.
 * - 404 Not Found: No se encontraron docentes para el departamento.
 * - 500 Internal Server Error: Error en la consulta.
 *
 * Ejemplo respuesta
 * 
 * {
 *   "docentes": [
 *       {
 *           "docente_id": 1,
 *           "nombre": "Juan",
 *           "apellido": "Pérez",
 *           "correo": "juan.perez@unah.hn",
 *           "numero_empleado": "EMP001",
 *           "foto": "juan.jpg"
 *       }
 *                ]
 * 
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../modules/config/Environments.php';
require_once __DIR__ . '/../../modules/config/DataBase.php';

// Instanciar la conexión
$db = new DataBase();
$conn = $db->getConnection();

// Validar que se reciba el parámetro "dept_id"
if (!isset($_GET['dept_id']) || empty($_GET['dept_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el parámetro dept_id']);
    exit;
}

$dept_id = intval($_GET['dept_id']);

// Consulta para obtener los docentes asociados al departamento
$sql = "SELECT docente_id, nombre, apellido, correo, numero_empleado, foto FROM Docente WHERE dept_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Error preparando la consulta: ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $dept_id);
$stmt->execute();
$result = $stmt->get_result();

$docentes = [];
while ($row = $result->fetch_assoc()) {
    $docentes[] = $row;
}

$stmt->close();

if (empty($docentes)) {
    http_response_code(404);
    echo json_encode(['error' => 'No se encontraron docentes para el departamento especificado']);
    exit;
}

http_response_code(200);
echo json_encode(['docentes' => $docentes]);
?>
