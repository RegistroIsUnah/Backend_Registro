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
 * @author Jose Vargas
 * @version 1.1
 * @package public
 * Cabeceras incluidas:
 * - Access-Control-Allow-Origin: * → Permite acceso desde cualquier origen (CORS).
 * - Access-Control-Allow-Methods: GET, POST, PUT, DELETE → Define los métodos HTTP permitidos.
 * - Access-Control-Allow-Headers: Content-Type → Permite JSON en las peticiones.
 *
 * Rutas manejadas:
 * - /api/post/login → Maneja el inicio de sesión de usuarios.
 * - /api/post/logout → Maneja el cierre de sesión.
 * - /api/get/aulas_edificio → Obtiene la lista de aulas asociadas a un edificio.
 * - /api/get/carreras → Obtiene una lista de las carreras
 * - /api/get/centros → Obtiene una lista de los centros
 * 
 * Version 1.1
 * - /api/get/listas_de_espera → Obtiene una lista de los estudiantes en espera
 * 
 * Respuestas HTTP:
 * - 200 OK: Si la ruta existe y se ejecuta correctamente.
 * - 404 Not Found: Si la ruta no está definida.
 * - 500 Internal Server Error: Si hay errores internos en la API.
 * 
 * 
 * 
 */

// Configurar CORS y métodos permitidos
//header("Access-Control-Allow-Origin: *");
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

    case preg_match('/\/api\/get\/carreras/', $request_uri):
        require_once __DIR__ . '/../api/get/carreras.php';
        break;  
        
    case preg_match('/\/api\/get\/centros/', $request_uri):
        require_once __DIR__ . '/../api/get/centros.php';
        break;
    
    case preg_match('/\/api\/post\/aspirante/', $request_uri):
        require_once __DIR__ . '/../api/post/aspirante.php';
        break;    

    case preg_match('/\/api\/post\/asignar_usuario_docente/', $request_uri):
        require_once __DIR__ . '/../api/post/asignar_usuario_docente.php';
        break;   

    case preg_match('/\/api\/post\/crear_seccion/', $request_uri):
        require_once __DIR__ . '/../api/post/crear_seccion.php';
        break;     

    case preg_match('/\/api\/post\/modificar_seccion/', $request_uri):
        require_once __DIR__ . '/../api/post/modificar_seccion.php';
        break; 
            
    case preg_match('/\/api\/post\/crear_periodo/', $request_uri):
        require_once __DIR__ . '/../api/post/crear_periodo.php';
        break; 

    case preg_match('/\/api\/post\/crear_proceso_matricula/', $request_uri):
        require_once __DIR__ . '/../api/post/crear_proceso_matricula.php';
        break; 
    
    case preg_match('/\/api\/get\/listas_de_espera/', $request_uri):
        require_once __DIR__ . '/../api/get/listas_de_espera.phpphp';
        break; 

    case preg_match('/\/api\/get\/listas_de_espera/', $request_uri):
        require_once __DIR__ . '/../api/get/listas_de_espera.php';
        break; 
        
    case preg_match('/\/api\/get\/clases_depto/', $request_uri):
        require_once __DIR__ . '/../api/get/clases_depto.php';
        break;

    case preg_match('/\/api\/get\/seccion_detalles/', $request_uri):
        require_once __DIR__ . '/../api/get/seccion_detalles.php';
        break;
        
    case preg_match('/\/api\/post\/matricular_estudiante/', $request_uri):
        require_once __DIR__ . '/../api/post/matricular_estudiante.php';
        break; 

    case preg_match('/\/api\/post\/registrar_libro/', $request_uri):
        require_once __DIR__ . '/../api/post/registrar_libro.php';
        break; 
    
    case preg_match('/\/api\/put\/modificar_libro/', $request_uri):
        require_once __DIR__ . '/../api/put/modificar_libro.php';
        break; 
    
    case preg_match('/\/api\/get\/listar_usuarios_con_roles/', $request_uri):
        require_once __DIR__ . '/../api/get/listar_usuarios_con_roles.php';
        break;
    
    case preg_match('/\/api\/get\/obtener_libro_encargado/', $request_uri):
        require_once __DIR__ . '/../api/get/obtener_libro_encargado.php';
        break;

    case preg_match('/\/api\/get\/obtener_libro/', $request_uri):
        require_once __DIR__ . '/../api/get/obtener_libro.php';
        break;
    
    case preg_match('/\/api\/get\/obtener_libros_por_departamento/', $request_uri):
        require_once __DIR__ . '/../api/get/obtener_libros_por_departamento.php';
        break; 
        
    case preg_match('/\/api\/get\/obtener_libros_por_estudiante/', $request_uri):
        require_once __DIR__ . '/../api/get/obtener_libros_estudiante.php';
        break;    

    case preg_match('/\/api\/post\/asignar_roles/', $request_uri):
        require_once __DIR__ . '/../api/post/asignar_roles.php';
        break; 
    
    case preg_match('/\/api\/post\/eliminar_asociaciones_libro/', $request_uri):
        require_once __DIR__ . '/../api/post/eliminar_asociaciones_libro.php';
        break; 

    case preg_match('/\/api\/post\/quitar_roles/', $request_uri):
        require_once __DIR__ . '/../api/post/quitar_roles.php';
        break; 

    case preg_match('/\/api\/post\/matricular_estudiante_adiciones_cancelaciones/', $request_uri):
        require_once __DIR__ . '/../api/post/matricular_estudiante_adiciones_cancelaciones.php';
        break; 
    
    case preg_match('/\/api\/get\/departamentos/', $request_uri):
        require_once __DIR__ . '/../api/get/departamentos.php';
        break;

    case preg_match('/\/api\/get\/listar_clases_matriculables/', $request_uri):
        require_once __DIR__ . '/../api/get/listar_clases_matriculables.php';
        break;

    case preg_match('/\/api\/get\/listar_laboratorios_clase/', $request_uri):
        require_once __DIR__ . '/../api/get/listar_laboratorios_clase.php';
        break;

    case preg_match('/\/api\/get\/listar_tags/', $request_uri):
        require_once __DIR__ . '/../api/get/listar_tags.php';
        break;

    default:
        http_response_code(404);
        echo json_encode(["mensaje" => "Ruta no encontrada"]);
}
?>
