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
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $errors = [];
            $data = [];
    
            // Validación estudiante_id
            if (empty($input['estudiante_id'])) {
                $errors[] = 'El ID del estudiante es requerido';
            } elseif (!filter_var($input['estudiante_id'], FILTER_VALIDATE_INT)) {
                $errors[] = 'ID de estudiante inválido';
            } else {
                $data['estudiante_id'] = (int)$input['estudiante_id'];
            }
    
            // Validación seccion_id
            if (empty($input['seccion_id'])) {
                $errors[] = 'El ID de la sección es requerido';
            } elseif (!filter_var($input['seccion_id'], FILTER_VALIDATE_INT)) {
                $errors[] = 'ID de sección inválido';
            } else {
                $data['seccion_id'] = (int)$input['seccion_id'];
            }
    
            // Validación tipo_proceso
            if (empty($input['tipo_proceso'])) {
                $errors[] = 'El tipo de proceso es requerido';
            } else {
                $tipoProceso = strtoupper(trim($input['tipo_proceso']));
                if (!in_array($tipoProceso, ['MATRICULA', 'ADICIONES_CANCELACIONES'])) {
                    $errors[] = 'Tipo de proceso no válido';
                } else {
                    $data['tipo_proceso'] = $tipoProceso;
                }
            }
    
            // Validación laboratorio_id
            $data['laboratorio_id'] = isset($input['laboratorio_id']) && $input['laboratorio_id'] !== '' 
                ? (int)$input['laboratorio_id'] 
                : null;
    
            if ($errors) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $errors
                ]);
                return;
            }
    
            $resultado = $this->model->matricularEstudiante(
                $data['estudiante_id'],
                $data['seccion_id'],
                $data['tipo_proceso'],
                $data['laboratorio_id']
            );
    
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $resultado,
                'message' => 'Matrícula procesada correctamente'
            ]);
    
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error en el servidor',
                'error' => $e->getMessage(),
                'error_code' => $e->getCode()
            ]);
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

    /**
     * Endpoint que obtiene las clases matriculadas de un estudiante
     * Recibe el ID del estudiante y devuelve las clases matriculadas.
     * 
     * @param int $estudiante_id El ID del estudiante
     * @return void Responde con un JSON con las clases matriculadas
     */
    public function obtenerClasesMatriculadas($estudiante_id) {
        // Verificar que el estudiante ID es válido
        if (empty($estudiante_id) || !is_numeric($estudiante_id)) {
            http_response_code(400);
            echo json_encode(['error' => 'El parámetro estudiante_id es inválido']);
            return;
        }

        // Instanciar el modelo de matrícula
        $matriculaModel = new Matricula();

        try {
            // Obtener las clases matriculadas
            $clasesMatriculadas = $matriculaModel->obtenerClasesMatriculadas($estudiante_id);

            // Si no hay clases matriculadas, responder con mensaje
            if (empty($clasesMatriculadas)) {
                http_response_code(404);
                echo json_encode(['message' => 'No se encontraron clases matriculadas']);
                return;
            }

            // Si se encontraron clases, devolver la respuesta en formato JSON
            echo json_encode($clasesMatriculadas);

        } catch (Exception $e) {
            // Si ocurre algún error, responder con un error
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

     /**
     * Endpoint que obtiene las clases matriculadas en estado 'EN_ESPERA' de un estudiante
     * Recibe el ID del estudiante y devuelve las clases en estado 'EN_ESPERA'.
     * 
     * @param int $estudiante_id El ID del estudiante
     * @return void Responde con un JSON con las clases matriculadas en estado 'EN_ESPERA'
     */
    public function obtenerClasesEnEspera($estudiante_id) {
        // Verificar que el estudiante ID es válido
        if (empty($estudiante_id) || !is_numeric($estudiante_id)) {
            http_response_code(400);
            echo json_encode(['error' => 'El parámetro estudiante_id es inválido']);
            return;
        }

        // Instanciar el modelo de matrícula
        $matriculaModel = new Matricula();

        try {
            // Obtener las clases en espera
            $clasesEnEspera = $matriculaModel->obtenerClasesEnEspera($estudiante_id);

            // Si no hay clases en espera, responder con mensaje
            if (empty($clasesEnEspera)) {
                http_response_code(404);
                echo json_encode(['message' => 'No se encontraron clases en espera']);
                return;
            }

            // Si se encontraron clases en espera, devolver la respuesta en formato JSON
            echo json_encode($clasesEnEspera);

        } catch (Exception $e) {
            // Si ocurre algún error, responder con un error
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Obtener los laboratorios matriculados de un estudiante.
     *
     * @param int $estudiante_id ID del estudiante
     * @return void Responde con los detalles de los laboratorios matriculados
     */
    public function obtenerLaboratoriosMatriculados($estudiante_id) {
        $laboratorios = $this->model->obtenerLaboratoriosMatriculados($estudiante_id);

        if (!empty($laboratorios)) {
            http_response_code(200);
            echo json_encode(['laboratorios' => $laboratorios]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'No se encontraron laboratorios matriculados para este estudiante']);
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
     * Cancela la matrícula de un estudiante en una sección.
     *
     * @param array $data Datos recibidos del endpoint (ID del estudiante y ID de la sección).
     * @return void
     */
    public function cancelarMatricula($data) {
        if (!isset($data['estudiante_id']) || !isset($data['seccion_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Faltan datos: estudiante_id y seccion_id son requeridos']);
            exit;
        }
    
        try {
            // Pasar el array completo al modelo
            $this->model->cancelarMatricula($data);
            
            http_response_code(200);
            echo json_encode(['message' => 'Matrícula cancelada correctamente']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }    
}
?>
