<?php
/**
 * API para procesar el archivo CSV de estudiantes.
 * 
 * Recibe un archivo CSV y lo procesa para registrar estudiantes en la base de datos, crear sus usuarios y enviarles un correo de bienvenida.
 * 
 * Ejemplo de URL:
 * servidor:puerto/api/procesar_estudiantes.php
 * 
 * Se espera recibir en la solicitud (multipart/form-data)
 * -Llave estudiantes_csv
 * -Valor Archivo.csv
 * 
 * Respuestas:
 *  - 200 OK: Procesado con éxito.
 *  - 400 Bad Request: Si el archivo no está presente o no es un CSV válido.
 *  - 500 Internal Server Error: Si ocurre un error en el procesamiento.
 * 
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 */


 header("Access-Control-Allow-Origin: *");
 header('Content-Type: application/json');
 
 require_once __DIR__ . '/../../controllers/EstudianteController.php';
 
 $controller = new EstudianteController();
 $controller->procesarCSVEstudiantes();
 ?>
