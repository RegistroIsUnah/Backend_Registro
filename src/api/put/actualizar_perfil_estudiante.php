<?php
/**
 * API para actualizar perfil de estudiante
 * 
 * Métodos aceptados: PUT, POST, GET
 * Autenticación requerida: Sí (rol estudiante)
 * Content-Type: application/json (para PUT/POST)
 * 
 * Parámetros permitidos:
 * - telefono (string)
 * - direccion (string)
 * - correo_personal (string)
 * 
 * Respuestas:
 * - 200 OK: Perfil actualizado exitosamente
 * - 400 Bad Request: Datos inválidos
 * - 401 Unauthorized: No autenticado
 * - 500 Internal Server Error: Error en el servidor
 * 
 * @package API
 * @author Jose Vargas
 * @version 1.0
 */

/*
{
    "success": true,
    "message": "Perfil actualizado exitosamente"
}
{
    "success": false,
    "error": "No hay campos válidos para actualizar"
}
*/
require_once __DIR__ . '/../../controllers/EstudianteController.php';

$controller = new EstudianteController();
$controller->actualizarPerfil();
?>