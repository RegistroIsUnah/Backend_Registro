<?php

// Archivo: src/api/post/aspirante.php

/**
 * API para ingresar un aspirante.
 *
 * Recibe un JSON con los datos del aspirante:
 * - nombre (obligatorio)
 * - apellido (obligatorio)
 * - identidad (obligatorio)
 * - telefono (opcional)
 * - correo (obligatorio)
 * - foto (opcional)
 * - carrera_principal_id (obligatorio)
 * - carrera_secundaria_id (opcional)
 * - centro_id (obligatorio)
 * - certificado_url (opcional)
 *
 * Genera automáticamente:
 * - numSolicitud: se genera usando un prefijo y la marca de tiempo.
 * - estado: se asigna por defecto 'PENDIENTE'.
 * - revisor_usuario_id y motivo_rechazo se insertan como NULL.
 * 
 * @author Ruben Diaz
 * @version 1.0
 *
 * Métodos soportados:
 * - POST: Requiere un JSON con los datos del aspirante.
 *
 * Respuestas HTTP:
 * - 200 OK: Aspirante ingresado exitosamente.
 * - 400 Bad Request: Datos insuficientes o inválidos.
 * - 405 Method Not Allowed: Método HTTP no permitido.
 * - 500 Internal Server Error: Error en la inserción en la base de datos.
 *
 * Ejemplo envio de datos
 * 
 * {
 *   "nombre": "Estiven",
 *   "apellido": "Mejia",
 *   "identidad": "0801199901234",
 *   "telefono": "5551234",
 *   "correo": "juan.perez@example.com",
 *   "foto": "http://ejemplo.com/foto.jpg",
 *   "carrera_principal_id": 1,
 *   "carrera_secundaria_id": 2,
 *   "centro_id": 2,
 *   "certificado_url": "http://ejemplo.com/certificado.pdf"
 * } 
 * 
 * Ejemplo de resuesta
 * 
 * {
 *   "message": "Aspirante ingresado exitosamente",
 *   "aspirante_id": 1
 *  }
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
 
 $input = json_decode(file_get_contents('php://input'), true);
 if (!$input) {
     http_response_code(400);
     echo json_encode(['error' => 'No se recibieron datos']);
     exit;
 }
 
 $requiredFields = ['nombre', 'apellido', 'identidad', 'correo', 'carrera_principal_id', 'centro_id'];
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
 
 // Asignar variables de entrada
 $nombre               = trim($input['nombre']);
 $apellido             = trim($input['apellido']);
 $identidad            = trim($input['identidad']);
 $telefono             = isset($input['telefono']) ? trim($input['telefono']) : null;
 $correo               = trim($input['correo']);
 $foto                 = isset($input['foto']) ? trim($input['foto']) : null;
 $carrera_principal_id = intval($input['carrera_principal_id']);
 $carrera_secundaria_id= isset($input['carrera_secundaria_id']) ? intval($input['carrera_secundaria_id']) : null;
 $centro_id            = intval($input['centro_id']);
 $certificado_url      = isset($input['certificado_url']) ? trim($input['certificado_url']) : null;
 
 // Preparar la llamada al procedimiento almacenado
 $stmt = $conn->prepare("CALL insertarAspirante(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
 $stmt->bind_param(
     "ssssssiiis", 
     $nombre,
     $apellido,
     $identidad,
     $telefono,
     $correo,
     $foto,
     $carrera_principal_id,
     $carrera_secundaria_id,
     $centro_id,
     $certificado_url
 );
 
 if ($stmt->execute()) {
     $result = $stmt->get_result();
     $row = $result->fetch_assoc();
     http_response_code(200);
     echo json_encode([
         'message' => 'Aspirante ingresado exitosamente',
         'aspirante_id' => $row['aspirante_id']
     ]);
 } else {
     http_response_code(500);
     echo json_encode(['error' => 'Error al ingresar el aspirante: ' . $stmt->error]);
 }
 
 $stmt->close();
 ?>