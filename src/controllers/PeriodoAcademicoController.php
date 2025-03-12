<?php
/**
 * Controlador de Periodo Academico
 *
 * Maneja la lógica de negocio para crear un período académico.
 *
 * @package Controllers
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

require_once __DIR__ . '/../models/PeriodoAcademico.php';

class PeriodoAcademicoController {
    /**
     * Valida los datos recibidos y crea un nuevo período académico.
     *
     * Se esperan los siguientes campos en $data:
     * - anio (numérico)
     * - numero_periodo (cadena)
     * - fecha_inicio (cadena en formato "YYYY-MM-DD HH:MM:SS")
     * - fecha_fin (cadena en formato "YYYY-MM-DD HH:MM:SS")
     *
     * @param array $data Datos recibidos del endpoint.
     * @return void
     */
    public function crearPeriodoAcademico($data) {
        // Validar que se hayan enviado los campos requeridos.
        $required = ['anio', 'numero_periodo', 'fecha_inicio', 'fecha_fin'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Falta el campo $field"]);
                exit;
            }
        }

        // Validar que 'anio' sea numérico.
        $anio = intval($data['anio']);
        $numero_periodo = $data['numero_periodo'];
        $fecha_inicio = $data['fecha_inicio'];
        $fecha_fin = $data['fecha_fin'];

        // Validar formato de fecha usando strtotime.
        if (strtotime($fecha_inicio) === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Formato de fecha_inicio inválido']);
            exit;
        }
        if (strtotime($fecha_fin) === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Formato de fecha_fin inválido']);
            exit;
        }

        try {
            $periodoModel = new PeriodoAcademico();
            $id = $periodoModel->crearPeriodoAcademico($anio, $numero_periodo, $fecha_inicio, $fecha_fin);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }

        http_response_code(200);
        echo json_encode(['periodo_academico_id' => $id, 'message' => 'Periodo académico creado exitosamente']);
    }
}
?>
