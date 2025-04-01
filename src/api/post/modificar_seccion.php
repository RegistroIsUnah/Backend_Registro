<?php
/**
 * API para modificar una sección.
 *
 * Este endpoint recibe datos en formato JSON, valida la información y llama al controlador para modificar la sección.
 *
 * Ejemplo de URL 
 * servidor:puerto/api/post/modificar_seccion
 * 
 * Metodos soportados:
 *  POST
 *
 * Ejemplo de JSON de entrada:
 * {
 *   "seccion_id": 15,
 *   "docente_id": 3,           // Opcional
 *   "aula_id": 5,              // Opcional
 *   "estado": "CANCELADA",     // Opcional; se espera 'ACTIVA' o 'CANCELADA'
 *   "motivo_cancelacion": "Cancelada por indisponibilidad" // Requerido si estado es 'CANCELADA'
 * }
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve un mensaje de éxito.
 * - 400 Bad Request: Datos faltantes o formato inválido.
 * - 500 Internal Server Error: Error durante la modificación.
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
 
 header("Access-Control-Allow-Origin: *");
 header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(["error" => "Datos JSON inválidos"]);
    exit;
}

require_once __DIR__ . '/../../controllers/SeccionController.php';

$seccionController = new SeccionController();
$seccionController->modificarSeccion($input);
 ?>