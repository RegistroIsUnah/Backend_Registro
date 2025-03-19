<?php
/**
 * API POST para procesar solicitud de revisor de admisiones
 * 
 * Método: POST
 * Autenticación requerida: Sí (rol estudiante)
 * Content-Type: application/json
 * 
 * Parámetros (JSON):
 * - carrera_id (int) - ID de la carrera a revisar
 * 
 * Respuestas:
 * - 200 OK: Permisos otorgados exitosamente
 * - 400 Bad Request: Datos faltantes o inválidos
 * - 403 Forbidden: Acceso no autorizado
 * - 500 Internal Server Error: Error en el servidor
 * @author Jose Vargas
 * @version 1.0
 */

require_once __DIR__ . '/../../controllers/RevisorController.php';

$controller = new RevisorController();
$controller->procesarSolicitudRevisor();
?>