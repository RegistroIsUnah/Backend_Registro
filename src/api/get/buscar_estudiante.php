<?php
/*
 * API GET para buscar estudiantes con filtros
 * 
 * Parámetros opcionales:
 * - nombre: Búsqueda parcial por nombre
 * - no_cuenta: Búsqueda exacta por número de cuenta
 * - centro: Filtro por nombre de centro
 * - carrera: Filtro por nombre de carrera
 * 
 * Ejemplo: 
 * /api/get/buscar_estudiante.php?nombre=Juan&centro=Ingeniería
 * 
 * Respuestas:
 * - 200 OK: Lista de estudiantes encontrados
 * - 400 Bad Request: Parámetros inválidos
 * - 500 Internal Server Error: Error en el servidor
 * 
 * @package API
 * @version 1.0
 * @author Jose Vargas  
 */

 header("Access-Control-Allow-Origin: *");
 header('Content-Type: application/json');
 
 require_once __DIR__ . '/../../controllers/EstudianteController.php';
 
 $filtros = [
     'nombre' => $_GET['nombre'] ?? '',
     'no_cuenta' => $_GET['no_cuenta'] ?? '',
     'carrera' => $_GET['carrera'] ?? '',
     'departamento' => $_GET['departamento'] ?? ''
 ];
 

$controller = new EstudianteController();
$controller->buscarEstudiante($filtros);
?>
