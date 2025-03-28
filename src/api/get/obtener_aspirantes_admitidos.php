<?php
/**
 * API para obtener un archivo CSV con los aspirantes admitidos.
 *
 * Permite descargar un archivo CSV con la lista de aspirantes admitidos.
 * No se requieren parámetros adicionales.
 * 
 * Ejemplo de URL:
 * servidor:puerto/api/get/aspirantes_admitidos_csv.php
 * 
 * Respuestas HTTP:
 * - 200 OK: Devuelve un archivo CSV con los aspirantes admitidos.
 * - 403 Forbidden: Si el usuario no tiene permisos para realizar esta acción.
 * - 500 Internal Server Error: En caso de error al generar el archivo CSV.
 *
 * Ejemplo de respuesta (archivo CSV descargado):
 * 
 *   aspirante_id,identidad,nombre,apellido,correo,telefono,numSolicitud,carrera_principal,carrera_secundaria,centro
 *   1,0801199901234,Juan,Pérez,juan@example.com,98765432,SOL-2023-001,Ingeniería en Sistemas,,Campus Central
 *   2,0801199905678,María,García,maria@example.com,98765433,SOL-2023-002,Medicina,Enfermería,Campus Norte
 *
 * @package API
 * @author Jose Vargas
 * @version 1.1
 * GET /api/get/obtener_aspirantes_admitidos.php
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="aspirantes_admitidos.csv"');

require_once __DIR__ . '/../../controllers/AspiranteController.php';


$controller = new AspiranteController();

$controller->generarCSVAspirantesAdmitidos();

?>