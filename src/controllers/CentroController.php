<?php
/**
 * Controlador de Centro
 *
 * Maneja la lógica de negocio para obtener la lista de centros.
 *
 * @package Controllers
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

require_once __DIR__ . '/../models/Centro.php';

class CentroController {
    /**
     * Obtiene la lista de centros y envía la respuesta en formato JSON.
     *
     * @return void
     */
    public function getCentros() {
        try {
            $centroModel = new Centro();
            $centros = $centroModel->obtenerCentros();
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
        http_response_code(200);
        echo json_encode($centros);
    }
}
?>
