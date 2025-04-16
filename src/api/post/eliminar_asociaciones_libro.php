<?php
/**
 * Endpoint para eliminar (desasociar) tags y autores de un libro.
 *
 * Se espera recibir en la solicitud (multipart/form-data o application/x-www-form-urlencoded):
 *   - libro_id: int (requerido)
 *   - tags: (opcional) JSON string con array de tag IDs a eliminar. Ejemplo: [1,2,3]
 *   - autores: (opcional) JSON string con array de autor IDs a eliminar. Ejemplo: [4,5]
 *
 * Responde en formato JSON.
 *
 * Ejemplo de URL (si se usa POST, la URL puede ser):
 * servidor:puerto/api/post/eliminar_asociaciones_libro
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

require_once __DIR__ . '/../../controllers/LibroController.php';

$controller = new LibroController();
$controller->eliminarAsociacionesLibro($data);
?>
