<?php
/**
 * Controlador de Laboratorio
 *
 * @package Controllers
 * @author Ruben Diaz
 * @version 1.1
 * 
 */

 require_once __DIR__ . '/../models/Laboratorio.php';

 class LaboratorioController {
    
    private $modelo; // Propiedad para almacenar el modelo

    /**
     * Constructor del controlador.
     */
    public function __construct() {
        // Inicializar el modelo Laboratorio
        $this->modelo = new Laboratorio(); // Ya no necesitamos pasar la conexión aquí
    }

    /**
     * Obtener los laboratorios de una clase específica.
     *
     * @param int $clase_id ID de la clase
     * @return void Responde con los detalles de los laboratorios
     */
    public function obtenerLaboratorios($clase_id) {
        $laboratorios = $this->modelo->obtenerLaboratorios($clase_id);

        if (!empty($laboratorios)) {
            http_response_code(200);
            echo json_encode(['laboratorios' => $laboratorios]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'No se encontraron laboratorios para esta clase']);
        }
    }

    /**
     * Crea un nuevo laboratorio
     *
     * @param array $data Datos del laboratorio a crear:
     *                    - clase_id (int) ID de la clase
     *                    - codigo_laboratorio (string) Código del laboratorio
     *                    - periodo_academico_id (int) ID del período
     *                    - hora_inicio (string) Hora de inicio
     *                    - hora_fin (string) Hora de fin
     *                    - aula_id (int) ID del aula
     *                    - cupos (int) Número de cupos
     *                    - dias (array) IDs de días de la semana
     * 
     * @return void Envía respuesta JSON con el resultado
     */
    public function crearLaboratorio($data) {
        // Validar campos requeridos
        $required_fields = ['clase_id', 'codigo_laboratorio', 'periodo_academico_id', 
                          'hora_inicio', 'hora_fin', 'aula_id', 'cupos', 'dias'];
        
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Falta el campo requerido: $field"]);
                exit;
            }
        }

        // Validar formato de días (debe ser array)
        if (!is_array($data['dias']) || empty($data['dias'])) {
            http_response_code(400);
            echo json_encode(['error' => "El campo 'dias' debe ser un array no vacío"]);
            exit;
        }

        try {
            $laboratorio_id = $this->modelo->crearLaboratorio(
                intval($data['clase_id']),
                $data['codigo_laboratorio'],
                intval($data['periodo_academico_id']),
                $data['hora_inicio'],
                $data['hora_fin'],
                intval($data['aula_id']),
                intval($data['cupos']),
                $data['dias']
            );

            http_response_code(201);
            echo json_encode([
                'message' => 'Laboratorio creado exitosamente',
                'laboratorio_id' => $laboratorio_id
            ]);
        } catch (Exception $e) {
            // Diferenciar entre errores de validación (400) y errores de servidor (500)
            $statusCode = strpos($e->getMessage(), 'Error preparando') !== false || 
                         strpos($e->getMessage(), 'Error ejecutando') !== false ? 500 : 400;
            
            http_response_code($statusCode);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Modifica un laboratorio existente
     *
     * @param array $data Datos del laboratorio a modificar:
     *                    - laboratorio_id (int) Requerido
     *                    - aula_id (int) Opcional
     *                    - estado (string) Opcional
     *                    - motivo_cancelacion (string) Opcional
     *                    - cupos (int) Opcional
     *                    - hora_inicio (string) Opcional
     *                    - hora_fin (string) Opcional
     *                    - dias (array) Opcional
     *
     * @return void Envía respuesta JSON con el resultado
     */
    public function modificarLaboratorio($data) {
        // Validar campo requerido
        if (!isset($data['laboratorio_id']) || empty($data['laboratorio_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El campo laboratorio_id es requerido']);
            return;
        }

        // Validar motivo si se cancela
        if (isset($data['estado']) && strtoupper($data['estado']) === 'CANCELADA' && 
            (!isset($data['motivo_cancelacion']) || empty(trim($data['motivo_cancelacion'])))) {
            http_response_code(400);
            echo json_encode(['error' => 'Se requiere motivo_cancelacion cuando el estado es CANCELADA']);
            return;
        }

        // Validar formato de días si se proporcionan
        if (isset($data['dias']) && !is_array($data['dias'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El campo dias debe ser un array']);
            return;
        }

        try {
            $result = $this->modelo->modificarLaboratorio(
                intval($data['laboratorio_id']),
                isset($data['aula_id']) ? intval($data['aula_id']) : null,
                isset($data['estado']) ? $data['estado'] : null,
                isset($data['motivo_cancelacion']) ? $data['motivo_cancelacion'] : null,
                isset($data['cupos']) ? intval($data['cupos']) : null,
                isset($data['hora_inicio']) ? $data['hora_inicio'] : null,
                isset($data['hora_fin']) ? $data['hora_fin'] : null,
                isset($data['dias']) ? $data['dias'] : null
            );

            http_response_code(200);
            echo json_encode(['message' => 'Laboratorio modificado exitosamente']);
        } catch (Exception $e) {
            // Diferenciar entre errores de validación (400) y del servidor (500)
            $statusCode = strpos($e->getMessage(), 'Error') === false ? 400 : 500;
            http_response_code($statusCode);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
?>