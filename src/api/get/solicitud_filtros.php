<?php
/**
 * API para búsqueda avanzada de solicitudes
 * 
 * Parámetros opcionales (GET):
 * - estado: Filtrar por estado
 * - solicitud_id: Búsqueda por ID específico
 * - numero_cuenta: Búsqueda por número de cuenta de estudiante
 * 
 * Ejemplos:
 * GET /api/get/buscar_solicitudes.php?estado=APROBADA
 * GET /api/get/buscar_solicitudes.php?solicitud_id=5
 * GET /api/get/buscar_solicitudes.php?numero_cuenta=202310001
 * GET /api/get/buscar_solicitudes.php?estado=PENDIENTE&numero_cuenta=202310002
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

require_once __DIR__ . '/../../controllers/SolicitudController.php';

try {
    $controller = new SolicitudController();
    $resultado = $controller->buscarSolicitudesAvanzado(
        $_GET['estado'] ?? null,
        $_GET['solicitud_id'] ?? null,
        $_GET['numero_cuenta'] ?? null
    );
    
    echo json_encode([
        'success' => true,
        'count' => count($resultado),
        'data' => $resultado
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>