<?php
/**
 * API para crear una sección.
 *
 * Este endpoint recibe datos en formato JSON, valida la información y llama al controlador para crear la sección.
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
 *   "dias": "Lunes,Miércoles"
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

 header('Content-Type: application/json');

 if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
     http_response_code(405);
     echo json_encode(['error' => 'Método no permitido']);
     exit;
 }
 
 // Si usas form-data, los datos de texto vendrán en $_POST y el archivo en $_FILES.
 if (empty($_POST)) {
     http_response_code(400);
     echo json_encode(['error' => 'No se recibieron datos']);
     exit;
 }
 
 require_once __DIR__ . '/../../controllers/SeccionController.php';
 
 $seccionController = new SeccionController();
 $seccionController->crearSeccion($_POST, $_FILES);
 ?>