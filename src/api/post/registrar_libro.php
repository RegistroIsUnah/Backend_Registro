<?php
/**
 * API para registrar un libro.
 * 
 * Ejemplo de URL 
 * servidor:puerto/api/post/registrar_libro
 *
 * Se espera recibir en la solicitud (multipart/form-data):
 *   - titulo: string
 *   - editorial: string 
 *   - fecha_publicacion: string (YYYY-MM-DD)
 *   - descripcion: string
 *   - tags: JSON (por ejemplo: '["Tag1","Tag2"]')
 *   - autores: JSON (por ejemplo: '[{"nombre":"Juan","apellido":"Pérez"},{"nombre":"Ana","apellido":"Gómez"}]')
 *   - clase_id: int (opcional)
 *   - libro: archivo (el documento del libro a subir)
 *   - rol: string (rol del usuario; en un sistema real se obtendría de la sesión/autenticación)
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


 header('Content-Type: application/json');

 $data = $_POST;
 $files = $_FILES;
 
 require_once __DIR__ . '/../../controllers/LibroController.php';
 
 $controller = new LibroController();
 $controller->registrarLibro($data, $files);
?>
