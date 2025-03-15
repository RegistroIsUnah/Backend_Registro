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
}
?>
