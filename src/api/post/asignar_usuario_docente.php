<?php

// Archivo: src/api/post/asignar_usuario_docente.php

/**
 * API para asignar credenciales a un docente existente mediante un procedimiento almacenado.
 *
 * Recibe un JSON con los siguientes datos:
 * - docente_id (obligatorio): ID del docente al que se le asignarán las credenciales.
 * - username (obligatorio): Nombre de usuario a asignar.
 * - password (obligatorio): Contraseña en texto plano (se recomienda hashearla en producción).
 *
 * El procedimiento almacenado 'SP_asignarUsuarioDocente' se encarga de:
 * 1. Verificar que el docente no tenga ya un usuario asignado.
 * 2. Obtener el rol "docente" desde la tabla Rol y crear un registro en la tabla Usuario.
 * 3. Actualizar el registro del docente asignándole el usuario_id recién generado.
 *
 * @author Ruben Diaz
 * @version 1.0
 * 
 * Métodos soportados:
 * - POST: Requiere un JSON con los datos del docente.
 *
 * Respuestas HTTP:
 * - 200 OK: Credenciales asignadas exitosamente.
 * - 400 Bad Request: Datos insuficientes o inválidos.
 * - 405 Method Not Allowed: Método HTTP no permitido.
 * - 500 Internal Server Error: Error en la ejecución del procedimiento almacenado.
 *
 * Ejemplo de envio
 * 
 * {
 * "docente_id": 3,
 * "username": "docente3",
 * "password": "docente789"
 * }
 * 
 * Ejemplo de respuesta
 * 
 * {
 * "message": "Credenciales asignadas exitosamente"
 * "docente_id" "3"
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
 * Campos obligatorios para asignar credenciales a un docente.
 *
 * @var array $requiredFields
 */
$requiredFields = ['docente_id', 'username', 'password'];

/**
 * Verifica que se hayan enviado todos los campos obligatorios.
 *
 * @var array $missingFields Almacena los nombres de los campos faltantes.
 */
$missingFields = [];
foreach ($requiredFields as $field) {
    if (!isset($input[$field]) || empty(trim($input[$field]))) {
        $missingFields[] = $field;
    }
}

if (count($missingFields) > 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan datos obligatorios: ' . implode(', ', $missingFields)]);
    exit;
}

$docente_id = intval($input['docente_id']);
$username   = trim($input['username']);
$password   = trim($input['password']);

/**
 * Llama al procedimiento almacenado 'SP_asignarUsuarioDocente' para asignar las credenciales al docente.
 *
 * @param int $docente_id ID del docente.
 * @param string $username Nombre de usuario.
 * @param string $password Contraseña.
 */
$stmt = $conn->prepare("CALL SP_asignarUsuarioDocente(?, ?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Error preparando la consulta: ' . $conn->error]);
    exit;
}

$stmt->bind_param("iss", $docente_id, $username, $password);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode([
        'message'    => 'Credenciales asignadas exitosamente',
        'docente_id' => $docente_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al asignar credenciales: ' . $stmt->error]);
}
$stmt->close();
?>
