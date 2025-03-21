<?php
/**
 * Endpoint para actualizar un libro.
 * 
 * Ejemplo de URL 
 * servidor:puerto/api/put o patch/modificar_libro
 *
 * Ejemplo de URL 
 * servidor:puerto/api/put o patch/modificar_libro
 *
 * Se espera recibir (multipart/form-data) los siguientes parámetros:
 *   - libro_id: int (requerido)
 *   - titulo: string (opcional)
 *   - fecha_publicacion: string (YYYY-MM-DD, opcional)
 *   - descripcion: string (opcional)
 *   - tags: JSON string (opcional)
 *   - autores: JSON string (opcional)
 *   - clase_id: int (opcional)
 *   - libro: archivo (opcional, nuevo archivo para actualizar)
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

 header('Content-Type: application/json');

 $data = $_POST;
 $files = $_FILES;
 
 require_once __DIR__ . '/../../controllers/LibroController.php';
 
 $controller = new LibroController();
 $controller->actualizarLibro($data, $files);
 ?>
?>
