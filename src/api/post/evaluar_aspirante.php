<?php
/**
 * API POST para evaluar aspirantes
 * 
 * Método: POST
 * Autenticación requerida: Sí (rol admin)
 * Content-Type: application/json
 * 
 * Parámetros (JSON):
 * - aspirante_id (int) - ID del aspirante a evaluar
 * 
 * Respuestas:
 * - 200 OK: Resultado de la evaluación
 * - 400 Bad Request: Datos faltantes
 * - 500 Internal Server Error: Error en el servidor
 * 
 * Ejemplo de respuesta:
 * {
 *   "success": true,
 *   "resultado": {
 *     "decision": "ADMITIDO",
 *     "carrera_asignada": "Ingeniería en Sistemas",
 *     "detalles": [...] 
 *   }
 * }
 */

require_once __DIR__ . '/../../controllers/AspiranteController.php';

$controller = new AspiranteController();
$controller->evaluarAspirante();