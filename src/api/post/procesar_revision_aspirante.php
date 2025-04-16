<?php
/**
 * Endpoint para aceptar o rechazar una solicitud de aspirante.
 *
 * Se espera recibir vía POST (o PATCH) los siguientes parámetros:
 *   - aspirante_id: int (requerido)
 *   - revisor_id: int (requerido)
 *   - accion: string ('aceptar' o 'rechazar') (requerido)
 *   - motivos: JSON string (opcional, array de motivo_id; requerido si la acción es 'rechazar')
 * 
 * Metodos soportados:
 *  POST
 *
 * Ejemplo para rechazar:
 * {
 *   "aspirante_id": 10,
 *   "revisor_id": 3,
 *   "accion": "rechazar",
 *   "motivos": "[1,4]"
 * }
 * 
 * Ejemplo para aceptar
 * {
 * "aspirante_id": 10,
 * "revisor_id": 3,
 * "accion": "aceptar"
 * }
 * 
 * Responde en formato JSON.
 * 
 * @package API
 * @author Ruben Diaz
 * @version 1.2
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

 require_once __DIR__ . '/../../controllers/AspiranteController.php';

 $controller = new AspiranteController();
 $controller->procesarRevision($data);
?>
