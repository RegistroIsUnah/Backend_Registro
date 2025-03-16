<?php
/**
 * Controlador de Clase
 *
 * Maneja la lógica de negocio para obtener las clases de un departamento.
 *
 * @package Controllers
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
require_once __DIR__ . '/../models/Clase.php';

class ClaseController {
    /**
     * Obtiene las clases de un departamento y envía la respuesta en JSON.
     *
     * @param int $dept_id ID del departamento.
     * @return void
     */
    public function getClasesPorDepartamento($dept_id) {
        try {
            $claseModel = new Clase();
            $clases = $claseModel->obtenerClasesPorDepartamento($dept_id);
            http_response_code(200);
            echo json_encode($clases);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Lista las clases matriculables para un estudiante.
     *
     * Se espera recibir en $data:
     * - departamento_id: ID del departamento.
     * - estudiante_id: ID del estudiante.
     *
     * @param array $data Datos recibidos del endpoint.
     * @return void
     */
    public function listarClasesMatriculables($data) {
        if (!isset($data['departamento_id']) || !isset($data['estudiante_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Faltan datos: departamento_id y estudiante_id son requeridos']);
            exit;
        }
        
        $departamento_id = intval($data['departamento_id']);
        $estudiante_id = intval($data['estudiante_id']);
        
        try {
            $modelo = new Clase();
            $clases = $modelo->obtenerClasesMatriculables($departamento_id, $estudiante_id);
            http_response_code(200);
            echo json_encode($clases);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Lista los laboratorios asociados a una clase.
     *
     * Se espera recibir en $data:
     * - clase_id: ID de la clase.
     *
     * @param array $data Datos recibidos del endpoint.
     * @return void
     */
    public function listarLaboratorios($data) {
        if (!isset($data['clase_id']) || empty($data['clase_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El parámetro clase_id es requerido']);
            exit;
        }
        
        $clase_id = intval($data['clase_id']);
        
        try {
            $laboratorioModel = new Clase();
            $laboratorios = $laboratorioModel->obtenerLaboratoriosPorClase($clase_id);
            http_response_code(200);
            echo json_encode($laboratorios);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
?>
