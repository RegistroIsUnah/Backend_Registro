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

 $allowedOrigins = [
    'https://www.registroisunah.xyz',
    'https://registroisunah.xyz'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: https://www.registroisunah.xyz");
}

header('Content-Type: application/json');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Credentials: true");

// Manejar solicitud OPTIONS para CORS preflight
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
 
 require_once __DIR__ . '/../../controllers/EstudianteController.php';
 
 $controller = new EstudianteController();
 $controller->procesarCSVEstudiantes();
 ?>
