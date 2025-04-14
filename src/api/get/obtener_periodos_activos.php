<?php
/**
 * API para obtener los periodos académicos activos.
 *
 * Método: GET
 *
 * Ejemplo de URL:
 *  servidor:puerto/api/get/obtener_periodos_activos.php
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

// No se reciben parámetros, solo es necesario obtener los periodos activos
require_once __DIR__ . '/../../controllers/PeriodoAcademicoController.php';

$periodoAcademicoController = new PeriodoAcademicoController();
$periodoAcademicoController->obtenerPeriodosActivos();
?>
