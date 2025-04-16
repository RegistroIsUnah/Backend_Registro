<?php
/**
 * API para generar reportes de secciones académicas.
 *
 * Este endpoint genera reportes de secciones en formato JSON, CSV o PDF.
 *
 * Parámetros GET:
 * - deptId: ID del departamento (requerido)
 * - formato: Salida deseada (json, csv o pdf, default: json)
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/obtener_reporte_carga_academica.php?deptId=1&formato=pdf
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve JSON con datos o ruta al archivo generado
 * - 400 Bad Request: Si faltan parámetros o son inválidos
 * - 500 Internal Server Error: Si ocurre un error en el servidor
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

require_once __DIR__ . '/../../controllers/SeccionController.php';

try {
    // Validar parámetro deptId
    if (!isset($_GET['deptId']) || !is_numeric($_GET['deptId'])) {
        http_response_code(400);
        echo json_encode(['error' => 'El parámetro deptId es requerido y debe ser numérico']);
        exit;
    }

    $deptId = (int)$_GET['deptId'];

    // Establecer formato por defecto a json si no se pasa
    $formato = isset($_GET['formato']) ? strtolower($_GET['formato']) : 'json';

    // Validar formato
    if (!in_array($formato, ['json', 'csv', 'pdf'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Formato inválido. Los formatos permitidos son json, csv y pdf']);
        exit;
    }

    $controller = new SeccionController();
    $controller->generarReporte($deptId, $formato);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error interno del servidor',
        'message' => $e->getMessage()
    ]);
}
?>