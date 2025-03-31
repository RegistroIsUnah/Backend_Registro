<?php
/**
 * Controlador de Carrera
 *
 * Maneja la lógica de negocio para obtener la lista de carreras.
 *
 * @package Controllers
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

require_once __DIR__ . '/../models/Carrera.php';

class CarreraController {
    /**
     * Obtiene la lista de carreras y envía la respuesta en formato JSON.
     *
     * @param int|null $centro_id (Opcional) ID del centro para filtrar las carreras.
     * @return void
     */
    public function getCarreras($centro_id = null) {
        try {
            $carreraModel = new Carrera();
            $carreras = $carreraModel->obtenerCarreras($centro_id);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
        http_response_code(200);
        echo json_encode($carreras);
    }

    /**
     * Obtiene los detalles de una carrera, su coordinador y jefe de departamento.
     *
     * @param array $data Datos recibidos del endpoint.
     * @return void
     */
    public function obtenerDetallesCarrera($data) {
        if (!isset($data['carrera_id']) || empty($data['carrera_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El parámetro carrera_id es requerido']);
            exit;
        }

        $carrera_id = intval($data['carrera_id']);
        
        try {
            $carreraModel = new Carrera();
            $carrera = $carreraModel->obtenerDetallesCarrera($carrera_id);

            if ($carrera === null) {
                http_response_code(404);
                echo json_encode(['error' => 'Carrera no encontrada']);
                exit;
            }

            http_response_code(200);
            echo json_encode($carrera);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
?>
