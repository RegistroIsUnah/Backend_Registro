<?php
/**
 * Endpoint para actualizar un libro.
 * 
 * Ejemplo de URL 
 * servidor:puerto/api/post/modificar_libro
 *
 * Metodos soportados:
 *  POST
 * 
 * Se espera recibir (multipart/form-data) los siguientes parámetros:
 * 
 * Todos los campos son opcionales.
 * 
 *   - titulo: string
 *   - editorial: string 
 *   - fecha_publicacion: string (YYYY-MM-DD)
 *   - isbn_libro: string
 *   - descripcion: string
 *   - tags: JSON (por ejemplo: '["1","2", "Historia", "Educación"]') //se manda el id del tag o el nombre y se registra si no existe
 *   - autores: JSON (por ejemplo: '[{"nombre":"Juan","apellido":"Pérez"},{"nombre":"Ana","apellido":"Gómez"}]')
 *   - clase_id: int (opcional)
 *   - libro: archivo (el documento del libro a subir)
 *   - rol: string (rol del usuario; en un sistema real se obtendría de la sesión/autenticación)
 *
 * Responde en formato JSON.
 * 
 * Respuestas:
 *   - 200 OK: Libro registrado correctamente.
 *   - 400 Bad Request: Faltan parámetros o error de validación.
 *   - 403 Forbidden: Usuario no autorizado.
 *   - 500 Internal Server Error: Error interno.
 * 
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
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

 $data = $_POST;
 $files = $_FILES;
 
 require_once __DIR__ . '/../../controllers/LibroController.php';
 
 $controller = new LibroController();
 $controller->actualizarLibro($data, $files);
?>