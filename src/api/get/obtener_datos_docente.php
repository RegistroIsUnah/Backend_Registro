<?php
/**
 * API para obtener los datos completos de un docente por su ID.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/obtener_datos_docente.php?docente_id=1
 *
 * Método soportado:
 *  GET
 *
 * Respuestas HTTP:
 * - 200 OK: Datos del docente en formato JSON (incluye departamento y centro)
 * - 400 Bad Request: Si falta el parámetro de entrada o es inválido
 * - 404 Not Found: Si no se encuentra el docente
 * - 500 Internal Server Error: Si ocurre un error en el servidor
 * 
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Verificar el método de la solicitud
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Validar y obtener el parámetro de entrada
if (empty($_GET['docente_id']) || !is_numeric($_GET['docente_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Se requiere un ID de docente válido']);
    exit;
}

$docente_id = intval($_GET['docente_id']);

try {
    // Incluir el controlador
    require_once __DIR__ . '/../../controllers/DocenteController.php';
    
    $docenteController = new DocenteController();
    $docenteController->getDocente($docente_id);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error en el servidor',
        'details' => $e->getMessage()
    ]);
}
?>