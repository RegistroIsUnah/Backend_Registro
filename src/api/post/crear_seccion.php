<?php
/**
 * API para crear una sección.
 *
 * Este endpoint recibe datos en formato JSON, valida la información y llama al controlador para crear la sección.
 * 
 * Ejemplo de URL:
 * servidor:puerto/api/post/crear_seccion.php
 * 
 * si se quiere mandar el video debe ser multipart/form-data
 * "video_url" : "video"
 *
 * Ejemplo de JSON de entrada:
 * {
 *   "clase_id": 1,
 *   "docente_id": 2,
 *   "periodo_academico_id": 3,
 *   "aula_id": 4,
 *   "hora_inicio": "08:00:00",
 *   "hora_fin": "10:00:00",
 *   "cupos": 30,
 *   "dias": "1,2,3"
 * }
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve el ID de la sección creada y un mensaje de éxito.
 * - 400 Bad Request: Datos faltantes o formato inválido.
 * - 500 Internal Server Error: Error durante la creación de la sección.
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

 header("Access-Control-Allow-Origin: *");
 header('Content-Type: application/json');
 header("Access-Control-Allow-Methods: POST, OPTIONS");
 header("Access-Control-Allow-Headers: Content-Type");
 
 if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
     http_response_code(405);
     echo json_encode(['error' => 'Método no permitido']);
     exit;
 }
 
 // Obtener datos de entrada (soporta tanto form-data como JSON)
 $inputData = [];
 if (!empty($_POST)) {
     $inputData = $_POST;
 } else {
     $json = file_get_contents('php://input');
     $inputData = json_decode($json, true);
     if (json_last_error() !== JSON_ERROR_NONE) {
         http_response_code(400);
         echo json_encode(['error' => 'JSON inválido']);
         exit;
     }
 }
 
 if (empty($inputData)) {
     http_response_code(400);
     echo json_encode(['error' => 'No se recibieron datos']);
     exit;
 }
 
 require_once __DIR__ . '/../../controllers/SeccionController.php';
 
 $seccionController = new SeccionController();
 $seccionController->crearSeccion($inputData, $_FILES);
 ?>