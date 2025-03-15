<?php
/**
 * Controlador de Matrícula.
 *
 * Encapsula la lógica de porcesos relacionado a matricula
 *
 * @package Controllers
 * @author Ruben Diaz
 * @version 1.2
 * 
 */

require_once __DIR__ . '/../models/Matricula.php';


class MatriculaController {
    private $model;

    public function __construct() {
        $this->model = new Matricula();
    }

    /**
     * Matricula a un estudiante en la sección principal y, opcionalmente, en el laboratorio seleccionado.
     *
     * @param int $estudiante_id ID del estudiante.
     * @param int $seccion_id ID de la sección principal.
     * @param string $tipo_proceso Tipo de proceso (ej. "MATRICULA").
     * @param int $laboratorio_id ID del laboratorio seleccionado (0 si no se seleccionó ninguno).
     * @return void Envía la respuesta en formato JSON.
     */
    public function matricularEstudiante($estudiante_id, $seccion_id, $tipo_proceso, $laboratorio_id) {
        try {
            $resultado = $this->model->matricularEstudiante($estudiante_id, $seccion_id, $tipo_proceso, $laboratorio_id);
            http_response_code(200);
            echo json_encode($resultado);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Obtiene la lista de espera para una sección.
     *
     * @param int $seccionId ID de la sección.
     * @return void Envía la respuesta en formato JSON.
     */
    public function obtenerListaEsperaPorSeccion($seccionId) {
        try {
            $data = $this->model->obtenerListaEsperaPorSeccion($seccionId);

            if (empty($data)) {
                http_response_code(404);
                echo json_encode(['error' => 'No hay estudiantes en lista de espera para esta sección']);
                return;
            }

            http_response_code(200);
            echo json_encode([
                'seccion_id' => $seccionId,
                'lista_espera' => $data
            ]);
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }
}
?>
