<?php
/*
 * API para solicitud de cambio de carrera
 * 
 * Método: POST
 * 
 * Campos requeridos:
 * - carrera_solicitada_id: int (ID de la nueva carrera)
 * - motivo: string (opcional)
 *
 * @package API
 * @author Jose Vargas
 * @version 1.0
 * 
    POST /api/post/registrar_cambio_carrera.php
    Content-Type: application/json

    {
        "carrera_solicitada_id": 5,
        "motivo": "Interés en nueva área de estudio"
    }
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../controllers/EstudianteController.php';
$data = $_POST;
$controller = new EstudianteController();
$controller->solicitarCambioCarrera($data);