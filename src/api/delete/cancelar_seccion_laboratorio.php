<?php
/**
 * API para cancelar la matrícula de un estudiante en una sección tambien cancela el laboratorio.
 *
 * Método: POST
 *
 * Parámetros (en JSON):
 *  - estudiante_id: ID del estudiante.
 *  - seccion_id: ID de la sección a cancelar.
 * 
 * Ejemplo de URL:
 *  servidor:puerto/api/post/cancelar_matricula.php
 *
 * Métodos soportados:
 *  POST
 *
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

// Recibimos los datos en formato JSON
$data = json_decode(file_get_contents('php://input'), true);

require_once __DIR__ . '/../../controllers/MatriculaController.php';

$seccionController = new MatriculaController();
$seccionController->cancelarMatricula($data);
?>
