<?php
/*
 * API GET para obtener las clases actuales de un docente
 * 
 * Método: GET
 * Autenticación requerida: Sí (mismo estudiante o admin)
 * 
 * Respuestas:
 * - 200 OK: Devuelve datos del perfil
 * - 401 Unauthorized: No autenticado
 * - 403 Forbidden: No autorizado
 * - 404 Not Found: Estudiante no existe
 * - 500 Internal Server Error: Error en el servidor
 * 
 * @package API
 * @author Jose Vargas
 * @version 1.0

 servidor:puerto/api/get/lista_estudiantes_seccion.php?seccion_id=1

    Formato de salida
    {
        "success": true,
        "data": [
            {
                "numero_cuenta": "202387443658",
                "nombre": "Carlos",
                "apellido": "López",
                "correo_personal": "carlos.lopez@example.com"
            }
        ]
    }
    @author Jose Vargas
    Version 1.2
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');


if (!isset($_GET['seccion_id']) || !is_numeric($_GET['seccion_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro seccion_id es inválido o faltante']);
    exit;
}

$seccion_id = (int) $_GET['seccion_id'];

require_once __DIR__ . '/../../controllers/SeccionController.php';

$controller = new SeccionController();
$controller->seccionListaEstudiantes($seccion_id);
?>
