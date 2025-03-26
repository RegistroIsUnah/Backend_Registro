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
    public function matricularEstudiante() {
        // Validación de los parámetros recibidos
        if (!isset($_POST['estudiante_id']) || !is_numeric($_POST['estudiante_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Parámetro estudiante_id inválido o faltante']);
            exit;
        }

        if (!isset($_POST['seccion_id']) || !is_numeric($_POST['seccion_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Parámetro seccion_id inválido o faltante']);
            exit;
        }

        if (!isset($_POST['tipo_proceso']) || empty($_POST['tipo_proceso'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Falta el parámetro tipo_proceso']);
            exit;
        }

        // Recoger datos de la solicitud
        $estudiante_id = intval($_POST['estudiante_id']);
        $seccion_id = intval($_POST['seccion_id']);
        $tipo_proceso = $_POST['tipo_proceso'];  // Debería ser 'MATRICULA'
        $laboratorio_id = isset($_POST['lab_seccion_id']) ? intval($_POST['lab_seccion_id']) : 0;

        try {
            // Llamar al modelo para realizar la matrícula
            $resultado = $this->model->matricularEstudiante($estudiante_id, $seccion_id, $tipo_proceso, $laboratorio_id);

            // Responder con éxito
            http_response_code(200);
            echo json_encode([
                'matricula_id' => $resultado['matricula_id'],
                'estado' => $resultado['estado'],
                'orden_inscripcion' => $resultado['orden_inscripcion'],
                'message' => 'Matrícula realizada exitosamente'
            ]);
        } catch (Exception $e) {
            // Capturar y manejar errores
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

   /**
     * Obtiene la lista de espera por ID de sección.
     *
     * @return void
     */
    public function obtenerListaPorSeccion() {
        if (!isset($_GET['seccionId']) || !is_numeric($_GET['seccionId'])) {
            http_response_code(400);  // Parámetro inválido
            echo json_encode(['error' => 'Parámetro seccionId inválido o faltante']);
            exit;
        }

        try {
            $seccionId = (int)$_GET['seccionId'];
            $data = $this->model->obtenerListaEsperaPorSeccion($seccionId);

            if (empty($data)) {
                http_response_code(404);  // No encontrado
                echo json_encode(['error' => 'No hay estudiantes en lista de espera para esta sección']);
                exit;
            }

            http_response_code(200);  // OK
            echo json_encode([
                'seccion_id' => $seccionId,
                'lista_espera' => $data
            ]);
            
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            http_response_code(500);  // Error interno del servidor
            echo json_encode(['error' => 'Error interno del servidor']);
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
        // Validar los campos requeridos
        if (!isset($data['estudiante_id']) || !isset($data['seccion_id']) || !isset($data['tipo_proceso'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Faltan datos requeridos: estudiante_id, seccion_id o tipo_proceso']);
            exit;
        }

        // Asignar los parámetros recibidos
        $estudiante_id = intval($data['estudiante_id']);
        $seccion_id = intval($data['seccion_id']);
        $tipo_proceso = $data['tipo_proceso'];  // Debe ser 'ADICIONES_CANCELACIONES'
        $lab_seccion_id = isset($data['lab_seccion_id']) && is_numeric($data['lab_seccion_id']) ? intval($data['lab_seccion_id']) : 0;

        try {
            // Llamar al modelo para realizar la matrícula
            $result = $this->model->matricularEstudianteAdiciones($estudiante_id, $seccion_id, $tipo_proceso, $lab_seccion_id);

            // Responder con éxito
            http_response_code(200);
            echo json_encode($result);

        } catch (Exception $e) {
            // Capturar y manejar excepciones
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
?>
