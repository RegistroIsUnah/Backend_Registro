<?php
/**
 * API para obtener los estudiantes matriculados en una sección específica.
 *
 * Método: GET
 *
 * Parámetros (en query string):
 *  - seccion_id: ID de la sección.
 * 
 * Ejemplo de URL:
 *  servidor:puerto/api/get/obtener_estudiantes_seccion.php?seccion_id=1
 * 
 * Métodos soportados:
 *  GET
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Recibimos los datos por query string
$data = $_GET;

require_once __DIR__ . '/../../controllers/EstudianteController.php';

$estudianteController = new EstudianteController();
$estudianteController->obtenerEstudiantesMatriculadosEnSeccion($data);
?>