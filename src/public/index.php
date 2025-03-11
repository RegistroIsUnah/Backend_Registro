<?php
/**
 * Punto de entrada para la API
 *
 * Este archivo maneja las peticiones y enruta las solicitudes API.
 * 
 * @author Ruben Diaz
 * @version 1.0
 * @package public
 * 
 * Cabeceras incluidas:
 * - Access-Control-Allow-Origin: * → Permite acceso desde cualquier origen (CORS).
 * - Access-Control-Allow-Methods: GET, POST, PUT, DELETE → Define los métodos HTTP permitidos.
 * - Access-Control-Allow-Headers: Content-Type → Permite JSON en las peticiones.
 *
 * Rutas manejadas:
 * - /api/post/login → Maneja el inicio de sesión de usuarios.
 * - /api/post/logout → Maneja el cierre de sesión.
 * - /api/get/aulas_edificio → Obtiene la lista de aulas asociadas a un edificio.
 *
 * Respuestas HTTP:
 * - 200 OK: Si la ruta existe y se ejecuta correctamente.
 * - 404 Not Found: Si la ruta no está definida.
 * - 500 Internal Server Error: Si hay errores internos en la API.
 */

// Configurar CORS y métodos permitidos
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Obtener la ruta de la petición
$request_uri = $_SERVER['REQUEST_URI'];

// Enrutamiento de la API
switch (true) {
    case preg_match('/\/api\/post\/login/', $request_uri):
        require_once __DIR__ . '/../api/post/login.php';
        break;

    case preg_match('/\/api\/post\/logout/', $request_uri):
        require_once __DIR__ . '/../api/post/logout.php';
        break;
    
    case preg_match('/\/api\/get\/aulas_edificio/', $request_uri):
        require_once __DIR__ . '/../api/get/aulas_edificio.php';
        break;
        
        
    default:
        http_response_code(404);
        echo json_encode(["mensaje" => "Ruta no encontrada"]);
}
?>
