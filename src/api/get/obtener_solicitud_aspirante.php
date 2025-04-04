<?php
/**
 * Endpoint para obtener una solicitud de aspirante para revisión.
 *
 * Se espera recibir el parámetro GET 'revisor_id'.
 * Cada llamada asignará y devolverá una única solicitud que esté en estado PENDIENTE o CORREGIDO_PENDIENTE,
 * y que no esté asignada (revisor_usuario_id IS NULL).
 * Si no hay solicitudes pendientes, se devuelve un mensaje indicándolo.
 *
 * Ejemplo de URL:
 * servidor:puerto/api/get/obtener_solicitud_aspirante.php?revisor_id=3
 *
 * Responde en formato JSON.
 *
 * @package API
 * @version 1.0
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

if (!isset($_GET['revisor_id']) || !is_numeric($_GET['revisor_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro revisor_id es inválido o faltante']);
    exit;
}

$revisor_id = (int) $_GET['revisor_id'];

require_once __DIR__ . '/../../controllers/AspiranteController.php';

$controller = new AspiranteController();
$controller->obtenerSolicitudParaRevision($revisor_id);
?>
