<?php
/**
 * API para actualizar los detalles de un aspirante por su número de solicitud.
 *
 * Este endpoint recibe el parámetro numSolicitud y los datos a actualizar (nombre, apellido, etc.).
 * También recibe archivos como: foto, fotodni, certificado.
 *
 * Ejemplo de URL:
 * sevidor:puerto/api/put/actualizar_aspirante
 * 
 * Metodos soportados:
 *  POST
 * 
 * Body (Form Data):
 *
 * En Postman, selecciona form-data como el tipo de cuerpo. Luego, agrega los siguientes campos:
 *
 *  numSolicitud: El número de solicitud del aspirante.
 *  aspirante_nombre: Nuevo nombre (si se desea actualizar).
 *  aspirante_apellido: Nuevo apellido (si se desea actualizar).
 *  documento: Nuevo documento (si se desea actualizar).
 *  telefono: Nuevo teléfono (si se desea actualizar).
 *  correo: Nuevo correo (si se desea actualizar).
 *  foto: Selecciona un archivo de imagen (si se desea actualizar).
 *  fotodni: Selecciona un archivo de imagen (si se desea actualizar).
 *  certificado_url: Selecciona un archivo (si se desea actualizar).
 *
 * Respuestas HTTP:
 * - 200 OK: Si los datos fueron actualizados correctamente.
 * - 400 Bad Request: Si falta el parámetro numSolicitud o los datos para actualizar.
 * - 404 Not Found: Si no se encuentra el aspirante.
 * - 500 Internal Server Error: Si ocurre un error en la consulta.
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

 header("Content-Type: application/json");

 // Se acepta solo el método POST
 if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
     http_response_code(405);
     echo json_encode(['error' => 'Método no permitido']);
     exit;
 }
 
 if (empty($_POST) && empty($_FILES)) {
     http_response_code(400);
     echo json_encode(['error' => 'No se recibieron datos']);
     exit;
 }
 
 $data = $_POST;
 $data['foto'] = $_FILES['foto'] ?? null;
 $data['fotodni'] = $_FILES['fotodni'] ?? null;
 $data['certificado_url'] = $_FILES['certificado_url'] ?? null;
 
 require_once __DIR__ . '/../../controllers/AspiranteController.php';
 
 $aspiranteController = new AspiranteController();
 $aspiranteController->actualizarAspirante($data);
?>
