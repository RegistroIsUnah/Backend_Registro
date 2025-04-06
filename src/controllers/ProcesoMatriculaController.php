<?php
/**
 * Controlador de Proceso de Matrícula
 *
 * Maneja la lógica de negocio para crear un proceso de matrícula.
 *
 * @package Controllers
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

require_once __DIR__ . '/../models/ProcesoMatricula.php';

class ProcesoMatriculaController {
     /**
     * Valida y procesa la creación de un proceso de matrícula.
     *
     * Se esperan los siguientes campos en $data:
     * - periodo_academico_id (entero)
     * - tipo_proceso (cadena, 'MATRICULA' o 'ADICIONES_CANCELACIONES')
     * - fecha_inicio (cadena en formato "YYYY-MM-DD HH:MM:SS")
     * - fecha_fin (cadena en formato "YYYY-MM-DD HH:MM:SS")
     *
     * @param array $data Datos recibidos del endpoint.
     * @return void
     */
    public function crearProcesoMatricula($data) {
        // Validar campos requeridos.
        $required = ['periodo_academico_id', 'tipo_proceso', 'fecha_inicio', 'fecha_fin'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Falta el campo $field"]);
                exit;
            }
        }

        // Validar formato de fecha.
        if (strtotime($data['fecha_inicio']) === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Formato de fecha_inicio inválido']);
            exit;
        }
        if (strtotime($data['fecha_fin']) === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Formato de fecha_fin inválido']);
            exit;
        }

        $periodo_academico_id = intval($data['periodo_academico_id']);
        $tipo_proceso = $data['tipo_proceso'];
        $fecha_inicio = $data['fecha_inicio'];
        $fecha_fin = $data['fecha_fin'];

        // Validar que tipo_proceso sea uno de los permitidos.
        $allowedTipos = ['MATRICULA', 'ADICIONES_CANCELACIONES'];
        if (!in_array($tipo_proceso, $allowedTipos)) {
            http_response_code(400);
            echo json_encode(['error' => 'tipo_proceso inválido']);
            exit;
        }

        try {
            // Instanciamos el modelo y creamos el proceso de matrícula
            $procesoModel = new ProcesoMatricula();
            $id = $procesoModel->crearProcesoMatricula($periodo_academico_id, $tipo_proceso, $fecha_inicio, $fecha_fin);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }

        http_response_code(200);
        echo json_encode(['proceso_id' => $id, 'message' => 'Proceso de matrícula creado exitosamente']);
    }
}
?>
