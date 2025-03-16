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
        //$this->model = new ListasEspera();
    }

    private function responder($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
    }


    private function error($mensaje, $statusCode) {
        $this->responder(['error' => $mensaje], $statusCode);
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
     * Obtiene la lista de espera por ID de sección
     */
    public function obtenerListaPorSeccion() {
        if (!isset($_GET['seccionId']) || !is_numeric($_GET['seccionId'])) {
            return $this->error('Parámetro seccionId inválido o faltante', 400);
        }

        try {
            $seccionId = (int)$_GET['seccionId'];
            $data = $this->model->obtenerListaEsperaPorSeccion($seccionId);

            if (empty($data)) {
                return $this->error('No hay estudiantes en lista de espera para esta sección', 404);
            }

            $this->responder([
                'seccion_id' => $seccionId,
                'lista_espera' => $data
            ]);
            
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            $this->error('Error interno del servidor', 500);
        }
    }

     /**
     * Matricula un estudiante en el proceso de ADICIONES_CANCELACIONES.
     *
     * Se espera recibir en $data (desde JSON) los siguientes campos:
     * - estudiante_id (requerido)
     * - seccion_id (requerido)
     * - tipo_proceso (debe ser 'ADICIONES_CANCELACIONES')
     * - lab_seccion_id (opcional; 0 o NULL si no aplica)
     *
     * @param array $data Datos recibidos del endpoint.
     * @return void
     */
    public function matricularEstudianteAdiciones($data) {
        if (!isset($data['estudiante_id']) || !isset($data['seccion_id']) || !isset($data['tipo_proceso'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Faltan datos requeridos: estudiante_id, seccion_id o tipo_proceso']);
            exit;
        }
        
        $estudiante_id = intval($data['estudiante_id']);
        $seccion_id = intval($data['seccion_id']);
        $tipo_proceso = $data['tipo_proceso'];
        // lab_seccion_id es opcional: se toma 0 si no se envía o no es numérico.
        $lab_seccion_id = (isset($data['lab_seccion_id']) && is_numeric($data['lab_seccion_id'])) ? intval($data['lab_seccion_id']) : 0;
        
        try {
            $matriculaModel = new Matricula();
            $result = $matriculaModel->matricularEstudianteAdiciones($estudiante_id, $seccion_id, $tipo_proceso, $lab_seccion_id);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
        
        http_response_code(200);
        echo json_encode($result);
    }
}
?>
