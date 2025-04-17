<?php
/**
 * API para descargar archivos de chat
 * 
 * Método: GET
 * Parámetros:
 * - id (int): ID del archivo en ArchivoChat
 * - numero_cuenta (string): Número de cuenta del estudiante (autenticación)
 * 
 * Ejemplo: /api/chat/descargar.php?id=123&numero_cuenta=20241000001
 * 
 *  Métodos soportados:
 *  GET
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

// Validar método y parámetros
if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

if (empty($_GET['id']) || empty($_GET['numero_cuenta'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Se requieren ID y número de cuenta']);
    exit;
}

require_once __DIR__ . '/../../controllers/ChatController.php';

try {
    $controller = new ChatController();
    $resultado = $controller->descargarArchivo(
        (int)$_GET['id'],
        $_GET['numero_cuenta'] // Pasamos el número de cuenta directamente
    );

    // Si hay error, devolver JSON
    if (!$resultado['success']) {
        http_response_code($resultado['code'] ?? 500);
        echo json_encode(['error' => $resultado['error']]);
        exit;
    }

    // Servir el archivo (aquí cambiamos a headers binarios)
    header('Content-Type: ' . $resultado['tipo_mime']);
    header('Content-Length: ' . filesize($resultado['ruta']));
    header('Content-Disposition: attachment; filename="' . $resultado['nombre_original'] . '"');
    readfile($resultado['ruta']);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno: ' . $e->getMessage()]);
}