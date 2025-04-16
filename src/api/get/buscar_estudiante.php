<?php
/*
 * API GET para buscar estudiantes con filtros
 * 
 * Parámetros opcionales:
 * - nombre: Búsqueda parcial por nombre
 * - no_cuenta: Búsqueda exacta por número de cuenta
 * - centro: Filtro por nombre de centro
 * - carrera: Filtro por nombre de carrera
 * 
 * Ejemplo: 
 * /api/get/buscar_estudiante.php?nombre=Juan&centro=Ingeniería
 * 
 * Respuestas:
 * - 200 OK: Lista de estudiantes encontrados
 * - 400 Bad Request: Parámetros inválidos
 * - 500 Internal Server Error: Error en el servidor
 * 
 * @package API
 * @version 1.0
 * @author Jose Vargas  
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
 
 $filtros = [
     'nombre' => $_GET['nombre'] ?? '',
     'no_cuenta' => $_GET['no_cuenta'] ?? '',
     'carrera' => $_GET['carrera'] ?? '',
     'departamento' => $_GET['departamento'] ?? ''
 ];
 

$controller = new EstudianteController();
$controller->buscarEstudiante($filtros);
?>
