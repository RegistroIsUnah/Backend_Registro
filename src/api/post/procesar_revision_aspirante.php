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
 
 header("Access-Control-Allow-Origin: *");
 header('Content-Type: application/json');

 $data = $_POST;

 require_once __DIR__ . '/../../controllers/AspiranteController.php';

 $controller = new AspiranteController();
 $controller->procesarRevision($data);
?>
