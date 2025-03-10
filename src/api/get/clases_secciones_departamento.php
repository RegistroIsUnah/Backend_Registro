<?php

// Archivo: src/api/get/clases_secciones_departamento.php

/**
 * API para obtener la lista de clases asociadas a un departamento, incluyendo para cada clase las secciones creadas.
 *
 * @author Ruben Diaz
 * @version 1.0
 * 
 * Se espera recibir el ID del departamento mediante el parámetro "dept_id" en la query string.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/clases_secciones_departamento.php?dept_id=3
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve un JSON con un array de clases y sus secciones.
 * - 400 Bad Request: Falta el parámetro dept_id o es inválido.
 * - 404 Not Found: No se encontraron clases para el departamento.
 * - 500 Internal Server Error: Error en la consulta.
 *
 * Ejemplo respuesta
 * 
 * {
 *   "clases": [
 *       {
 *           "clase_id": 1,
 *           "nombre": "Introducción a la Programación",
 *           "creditos": 3,
 *           "secciones": [
 *               {
 *                   "seccion_id": 1,
 *                   "docente_id": 2,
 *                   "periodo_academico_id": 1,
 *                   "hora_inicio": "08:00:00",
 *                   "hora_fin": "09:00:00",
 *                   "aula_id": 3,
 *                   "estado": "CANCELADA",
 *                   "motivo_cancelacion": "Problemas administrativos",
 *                   "cupos": 30
 *               }
 *           ]
 *       }
 *   ]
 * }
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

// Consulta para obtener las clases del departamento
$sqlClases = "SELECT clase_id, nombre, creditos FROM Clase WHERE dept_id = ?";
$stmtClases = $conn->prepare($sqlClases);
if (!$stmtClases) {
    http_response_code(500);
    echo json_encode(['error' => 'Error preparando la consulta de clases: ' . $conn->error]);
    exit;
}
$stmtClases->bind_param("i", $dept_id);
$stmtClases->execute();
$resultClases = $stmtClases->get_result();

$clases = [];
while ($clase = $resultClases->fetch_assoc()) {
    $clase_id = $clase['clase_id'];
    
    // Para cada clase, obtener las secciones asociadas
    $sqlSecciones = "SELECT seccion_id, docente_id, periodo_academico_id, hora_inicio, hora_fin, aula_id, estado, motivo_cancelacion, cupos FROM Seccion WHERE clase_id = ?";
    $stmtSecciones = $conn->prepare($sqlSecciones);
    if (!$stmtSecciones) {
        http_response_code(500);
        echo json_encode(['error' => 'Error preparando la consulta de secciones: ' . $conn->error]);
        exit;
    }
    $stmtSecciones->bind_param("i", $clase_id);
    $stmtSecciones->execute();
    $resultSecciones = $stmtSecciones->get_result();
    
    $secciones = [];
    while ($seccion = $resultSecciones->fetch_assoc()) {
        $secciones[] = $seccion;
    }
    $stmtSecciones->close();
    
    // Agregar el array de secciones a los datos de la clase
    $clase['secciones'] = $secciones;
    $clases[] = $clase;
}

$stmtClases->close();

if (empty($clases)) {
    http_response_code(404);
    echo json_encode(['error' => 'No se encontraron clases para el departamento especificado']);
    exit;
}

http_response_code(200);
echo json_encode(['clases' => $clases]);
?>
