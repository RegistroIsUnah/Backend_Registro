<?php
/**
 * Controlador de Tag
 *
 * Maneja la lógica de negocio para listar todos los tags.
 *
 * @package Controllers
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

require_once __DIR__ . '/../models/Tag.php';

class TagController {
    /**
     * Lista todos los tags y envía la respuesta en formato JSON.
     *
     * @return void
     */
    public function listarTags() {
        try {
            $tagModel = new Tag();
            $tags = $tagModel->obtenerTags();
            http_response_code(200);
            echo json_encode($tags);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
}
?>
