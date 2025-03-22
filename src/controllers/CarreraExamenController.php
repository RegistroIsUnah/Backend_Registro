<?php
/**
 * Controlador de CarreraExamen
 *
 * Maneja la lógica de negocio para listar las carreras y los exámenes con sus puntajes.
 *
 * @package Controllers
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

require_once __DIR__ . '/../models/CarreraExamen.php';

class CarreraExamenController {
    /**
     * Lista todas las carreras con los exámenes y sus puntajes.
     *
     * @return void
     */
    public function listarCarrerasConExamenesYPuntajes() {
        try {
            $modelo = new CarreraExamen();
            $carreras = $modelo->obtenerCarrerasConExamenesYPuntajes();
            http_response_code(200);
            echo json_encode($carreras);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
?>
