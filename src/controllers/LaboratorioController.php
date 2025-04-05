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
     * Crea un nuevo laboratorio si no hay conflictos en el horario ni aula.
     */
    public function crearLaboratorio() {
        // Recoger datos del formulario (o JSON)
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos no proporcionados']);
            return;
        }

        $clase_id = $data['clase_id'];
        $periodo_academico_id = $data['periodo_academico_id'];
        $hora_inicio = $data['hora_inicio'];
        $hora_fin = $data['hora_fin'];
        $aula_id = $data['aula_id'];
        $cupos = $data['cupos'];
        $codigo_laboratorio = date('Hi', strtotime($hora_inicio)); // Código del laboratorio basado en la hora de inicio

        try {
            // Crear el laboratorio
            $laboratorio_id = $this->modelo->crearLaboratorio($clase_id, $codigo_laboratorio, $periodo_academico_id, $hora_inicio, $hora_fin, $aula_id, $cupos);
            
            http_response_code(200);
            echo json_encode(['message' => 'Laboratorio creado exitosamente', 'laboratorio_id' => $laboratorio_id]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Modifica un laboratorio, incluyendo la actualización de la hora de inicio y el código del laboratorio.
     */
    public function modificarLaboratorio() {
        // Recoger datos del formulario (o JSON)
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos no proporcionados']);
            return;
        }

        $laboratorio_id = $data['laboratorio_id'];
        $clase_id = $data['clase_id'] ?? null;
        $periodo_academico_id = $data['periodo_academico_id'] ?? null;
        $hora_inicio = $data['hora_inicio'] ?? null;
        $hora_fin = $data['hora_fin'] ?? null;
        $aula_id = $data['aula_id'] ?? null;
        $cupos = $data['cupos'] ?? null;

        try {
            // Modificar el laboratorio
            $this->modelo->modificarLaboratorio($laboratorio_id, $clase_id, null, $periodo_academico_id, $hora_inicio, $hora_fin, $aula_id, $cupos);
            
            http_response_code(200);
            echo json_encode(['message' => 'Laboratorio modificado exitosamente']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
?>