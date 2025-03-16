<?php
/**
 * Controlador de Departamento
 *
 * Maneja la lógica de negocio para listar todos los departamentos.
 *
 * @package Controllers
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

require_once __DIR__ . '/../models/Departamento.php';

class DepartamentoController {
    /**
     * Lista todos los departamentos y envía la respuesta en formato JSON.
     *
     * @return void
     */
    public function listarDepartamentos() {
        try {
            $departamentoModel = new Departamento();
            $departamentos = $departamentoModel->obtenerDepartamentos();
            http_response_code(200);
            echo json_encode($departamentos);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
}
?>
