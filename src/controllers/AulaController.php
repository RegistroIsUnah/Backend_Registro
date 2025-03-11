<?php
/**
 * Controlador de Aula
 *
 * Maneja la lógica de negocio relacionada con las aulas.
 *
 * @package Controllers
 * @author Ruben Diaz
 * @version 1.0
 */

require_once __DIR__ . '/../models/Aula.php';

class AulaController {
    /**
     * Obtiene las aulas de un edificio y envía la respuesta en JSON.
     *
     * @param int $edificio_id ID del edificio.
     * @return void
     */
    public function getAulasPorEdificio($edificio_id) {
        try {
            $aulaModel = new Aula();
            $aulas = $aulaModel->obtenerAulasPorEdificio($edificio_id);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }

        if (empty($aulas)) {
            http_response_code(404);
            echo json_encode(['error' => 'No se encontraron aulas para el edificio especificado']);
            exit;
        }

        http_response_code(200);
        echo json_encode(['aulas' => $aulas]);
    }
}
?>
