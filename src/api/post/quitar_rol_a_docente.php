<?php
/**
 * API para quitar roles a un docente.
 * 
 * Ejemplo de URL 
 * servidor:puerto/api/post/quitar_rol_a_docente.php
 * 
 * Métodos soportados:
 *  POST
 *
 * Parámetros POST:
 * - docente_id: ID del docente.
 * - roles: Array de role nombres a quitar.
 * - departamento_id: (Opcional) ID del departamento (si se quita el rol 'Jefe de Departamento').
 * - carrera_id: (Opcional) ID de la carrera (si se quita el rol 'Coordinador').
 * 
 * Ejemplo envio
 * 
 * {
 * "docente_id": 123,
 * "roles": ["Jefe de Departamento"],
 * "departamento_id": 1
 * }
 *
 * {
 * "docente_id": 321,
 * "roles": ["Coordinador"],
 * "carrera_id": 2
 * }
 *
 * 
 * Respuestas:
 * - 200 OK: Si se quitaron los roles correctamente.
 * - 400 Bad Request: Si faltan parámetros.
 * - 500 Internal Server Error: Si ocurre un error.
 * 
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../controllers/UsuarioRolController.php';


try {
    // Obtener los datos del cuerpo de la solicitud
    $data = json_decode(file_get_contents("php://input"), true);

    // Validar que los parámetros requeridos están presentes
    if (!isset($data['docente_id']) || !isset($data['roles']) || !is_array($data['roles'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Faltan parámetros necesarios']);
        exit;
    }

    // Si no se proporcionan departamento_id ni carrera_id, podemos pasar null
    $docente_id = $data['docente_id'];
    $roles = $data['roles'];
    $departamento_id = isset($data['departamento_id']) ? $data['departamento_id'] : null;
    $carrera_id = isset($data['carrera_id']) ? $data['carrera_id'] : null;

    // Instanciamos el controlador y pasamos los parámetros
    $controller = new UsuarioRolController();
    $controller->quitarRolesDocente($docente_id, $roles, $departamento_id, $carrera_id);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor', 'mensaje' => $e->getMessage()]);
}
?>