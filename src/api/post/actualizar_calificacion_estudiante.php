<?php
/*
     {
        "estudiante_id": 2023001,
        "seccion_id": 15,
        "calificacion": 85.5,
        "observacion": "Excelente participación en prácticas",
        "estado_curso_id": 2
    }

    {
    "success": true,
    "data": {
        "historial_id": 89,
        "fecha": "2023-10-05 14:30:00"
    }
}
 * @package API
 * @author Jose Vargas
 * @version 1.0
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Obtener datos de entrada
$data = json_decode(file_get_contents('php://input'), true);

require_once __DIR__ . '/../../controllers/DocenteController.php';

$controller = new DocenteController();
$controller->actualizarCalificacionEstudiante($data);

?>