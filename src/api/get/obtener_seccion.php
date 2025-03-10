<?php

// Archivo: src/api/get/obtener_seccion.php

/**
 * API para obtener la información de una sección.
 * 
 * @author Ruben Diaz
 * @version 1.0
 *
 * Se espera recibir el ID de la sección mediante el parámetro "id" en la query string.
 *
 * La respuesta incluye los datos de la sección, junto con información del docente que la imparte,
 * el aula asignada y los días en que se dicta.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/obtener_seccion.php?id=123
 *
 * Respuestas HTTP:
 * - 200 OK: Sección encontrada y se retorna la información en formato JSON.
 * - 400 Bad Request: Falta el parámetro id o es inválido.
 * - 404 Not Found: No se encontró la sección.
 * - 500 Internal Server Error: Error en la consulta.

 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../modules/config/Environments.php';
require_once __DIR__ . '/../../modules/config/DataBase.php';

// Instanciar la conexión a la base de datos
$db = new DataBase();
$conn = $db->getConnection();

// Verificar que se haya pasado el parámetro "id"
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el parámetro id']);
    exit;
}

$seccion_id = intval($_GET['id']);

// Consulta principal para obtener los datos de la sección con información del docente y aula.
$sql = "
    SELECT 
        s.seccion_id,
        s.clase_id,
        c.nombre AS clase_nombre,
        s.docente_id,
        d.nombre AS docente_nombre,
        d.apellido AS docente_apellido,
        d.correo AS docente_correo,
        d.foto AS docente_foto,
        s.periodo_academico_id,
        p.anio AS periodo_anio,
        p.numero_periodo,
        s.hora_inicio,
        s.hora_fin,
        s.aula_id,
        a.nombre AS aula_nombre,
        a.capacidad AS aula_capacidad,
        e.edificio_id,
        e.nombre AS edificio_nombre,
        s.estado,
        s.motivo_cancelacion,
        s.cupos
    FROM Seccion s
    INNER JOIN Clase c ON s.clase_id = c.clase_id
    INNER JOIN Docente d ON s.docente_id = d.docente_id
    INNER JOIN PeriodoAcademico p ON s.periodo_academico_id = p.periodo_academico_id
    INNER JOIN Aula a ON s.aula_id = a.aula_id
    INNER JOIN Edificio e ON a.edificio_id = e.edificio_id
    WHERE s.seccion_id = ?
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Error preparando la consulta: ' . $conn->error]);
    exit;
}
$stmt->bind_param("i", $seccion_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Sección no encontrada']);
    exit;
}

$seccion = $result->fetch_assoc();
$stmt->close();

// Consulta para obtener los días en que se dicta la sección.
$sqlDias = "SELECT dia FROM SeccionDia WHERE seccion_id = ?";
$stmtDias = $conn->prepare($sqlDias);
if (!$stmtDias) {
    http_response_code(500);
    echo json_encode(['error' => 'Error preparando la consulta de días: ' . $conn->error]);
    exit;
}
$stmtDias->bind_param("i", $seccion_id);
$stmtDias->execute();
$resultDias = $stmtDias->get_result();

$dias = [];
while ($row = $resultDias->fetch_assoc()) {
    $dias[] = $row['dia'];
}
$stmtDias->close();

// Agregar el array de días al resultado de la sección
$seccion['dias'] = $dias;

// Devolver la información en formato JSON
http_response_code(200);
echo json_encode(['seccion' => $seccion]);
?>
