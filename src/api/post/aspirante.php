<?php
/**
 * API para ingresar un aspirante.
 *
 * Este endpoint recibe datos vía multipart/form-data, incluidos archivos para:
 * - foto (la imagen del aspirante)
 * - fotodni (la foto del DNI)
 * - certificado (el archivo del certificado)
 *
 * Además, recibe campos de texto: nombre, apellido, identidad, telefono, correo, carrera_principal_id,
 * carrera_secundaria_id y centro_id.
 * 
 * Ejemplo de URL:
 * servidor:puerto/api/post/aspirante
 *
 * Método:
 *   POST
 *
 * Ejemplo de cuerpo (multipart/form-data):
 * - campos de texto: nombre, apellido, identidad, telefono, correo, carrera_principal_id, carrera_secundaria_id, centro_id
 * - archivos: foto (la foto del aspirante), certificado (el certificado)
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve el número de solicitud y mensaje de éxito.
 * - 400 Bad Request: Datos inválidos o error en la carga de archivos.
 * - 500 Internal Server Error: Error durante la inserción.
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.1
 * 
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

if (empty($_POST)) {
    http_response_code(400);
    echo json_encode(['error' => 'No se recibieron datos']);
    exit;
}

require_once __DIR__ . '/../../controllers/AspiranteController.php';

$aspiranteController = new AspiranteController();
$aspiranteController->insertarAspirante($_POST);
?>
