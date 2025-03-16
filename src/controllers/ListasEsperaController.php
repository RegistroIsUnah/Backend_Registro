<?php
/**
 * Controlador para manejar listas de espera por sección
 *
 * @package Controllers
 * @author Jose Vargas
 * @version 1.2
 */
require_once __DIR__ . '/../models/ListasEsperaModel.php';

class ListasEsperaController {
    private $model;

    public function __construct() {
        $this->model = new ListasEspera();
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
     * Obtiene la lista de espera por ID de sección
     */
    public function obtenerListaPorSeccion() {
        if (!isset($_GET['seccionId']) || !is_numeric($_GET['seccionId'])) {
            return $this->error('Parámetro seccionId inválido o faltante', 400);
        }

        try {
            $seccionId = (int)$_GET['seccionId'];
            $data = $this->model->obtenerListaEsperaPorSeccion($seccionId);

            if (empty($data)) {
                return $this->error('No hay estudiantes en lista de espera para esta sección', 404);
            }

            $this->responder([
                'seccion_id' => $seccionId,
                'lista_espera' => $data
            ]);
            
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            $this->error('Error interno del servidor', 500);
        }
    }
}
?>