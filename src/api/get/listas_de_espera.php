<?php

// Archivo: src/api/get/listas_de_espera.php

/**
 * API para obtener listas de espera por departamento
 *
 * @author Jose Vargas
 * @version 1.1
 * 
 * Parámetro requerido: departamentoId
 * Devuelve: clase, sección, departamento, estudiantes en espera con su correo
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/listas_de_espera.php?departamentoId=5
 *
 * Respuestas HTTP:
 * - 200 OK: Listas de espera encontradas
 * - 400 Bad Request: Parámetro inválido
 * - 404 Not Found: Sin listas de espera
 * - 500 Internal Server Error: Error en servidor
 * 
 * Parametros de Entrada DepartamentoId del Jefe del Departamento
 * 
 * Ejemplo de Salida
 * [
 *   {
 *       "seccion_id": 15,
 *       "clase": "Cálculo Avanzado",
 *       "departamento": "Matemáticas",
 *       "lista_espera": [
 *           {
 *               "estudiante_id": 45,
 *              "nombre": "Ana",
 *               "apellido": "García",
 *               "correo_personal": "ana.garcia@mail.com",
 *               "fecha_solicitud": "2025-03-10 09:30:00"
 *           },
 *           {
 *               "estudiante_id": 78,
 *               "nombre": "Carlos",
 *               "apellido": "Martínez",
 *               "correo_personal": "carlos.mtz@mail.com",
 *               "fecha_solicitud": "2025-03-10 10:15:00"
 *           }
 *       ]
 *   }
 *]
 * 
 * 
 *
 * 
 * 
 * 
 * 
 * 
 * 
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../modules/config/Environments.php';
require_once __DIR__ . '/../../modules/config/DataBase.php';

// Validar parámetro
if (!isset($_GET['departamentoId']) || !is_numeric($_GET['departamentoId'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetro departamentoId inválido o faltante']);
    exit;
}

$departamentoId = (int)$_GET['departamentoId'];

// Establecer conexión
$db = new DataBase();
$conn = $db->getConnection();

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

// Consulta SQL actualizada
/*
*Consulta SQL sujeta a cambios 
*/
$sql = "
    SELECT 
        c.nombre AS clase,
        s.seccion_id,
        d.nombre AS departamento,
        e.estudiante_id,
        e.correo_personal,
        e.nombre,
        e.apellido,
        m.fecha AS fecha_solicitud
    FROM Departamento d
    INNER JOIN Clase c ON d.dept_id = c.dept_id
    INNER JOIN Seccion s ON c.clase_id = s.clase_id
    INNER JOIN Matricula m ON s.seccion_id = m.seccion_id
    INNER JOIN Estudiante e ON m.estudiante_id = e.estudiante_id
    WHERE d.dept_id = ?
    AND m.estado = 'EN_ESPERA'
    ORDER BY s.seccion_id, m.fecha
";

// Preparar consulta
$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en preparación de consulta: ' . $conn->error]);
    exit;
}

// Vincular y ejecutar
$stmt->bind_param('i', $departamentoId);
$executed = $stmt->execute();

if (!$executed) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al ejecutar consulta: ' . $stmt->error]);
    exit;
}

$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

if (empty($data)) {
    http_response_code(404);
    echo json_encode(['error' => 'No hay estudiantes en lista de espera para este departamento']);
    exit;
}

// Estructurar respuesta
$response = [];
$currentSection = null;

foreach ($data as $row) {
    if ($currentSection != $row['seccion_id']) {
        $currentSection = $row['seccion_id'];
        $response[] = [
            'seccion_id' => $currentSection,
            'clase' => $row['clase'],
            'departamento' => $row['departamento'],
            'lista_espera' => []
        ];
    }
    
    $response[count($response)-1]['lista_espera'][] = [
        'estudiante_id' => $row['estudiante_id'],
        'nombre' => $row['nombre'],
        'apellido' => $row['apellido'],
        'correo_personal' => $row['correo_personal'],
        'fecha_solicitud' => $row['fecha_solicitud']
    ];
}

http_response_code(200);
echo json_encode($response);


?>
