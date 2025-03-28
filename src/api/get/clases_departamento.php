<?php
/**
 * GET /api/get/clases_departamento.php?departamentoId=1&anio=2024&periodo=1
 * 
 * 
 * 
 * 
 * 
 * 
 * [
*    {
*        "clase_id": 15,
*        "nombre_clase": "Cálculo Diferencial",
*        "secciones": [
*            {
*                "seccion_id": 23,
*                "codigo": "MAT-1501",
*                "horario": {
*                    "inicio": "08:00:00",
*                    "fin": "10:00:00"
*                },
*                "aula": "Aula 302",
*                "docente": "Dr. Juan Pérez"
*            },
*            {
*                "seccion_id": 24,
*                "codigo": "MAT-1502",
*                "horario": {
*                    "inicio": "14:00:00",
*                    "fin": "16:00:00"
*                },
*                "aula": "Aula 305",
*                "docente": "Dra. María Gómez"
*            }
*        ]
*    }
*]
 * @author Jose Vargas
 * 
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require_once __DIR__ . '/../../controllers/DepartamentoController.php';

$controller = new DepartamentoController();
$controller->obtenerClasesPorDepartamento();
?>