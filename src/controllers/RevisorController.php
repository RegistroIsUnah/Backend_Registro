<?php
require_once __DIR__ . '/../models/Revisor.php';
/**
 * Clase Revisor
 *
 * Maneja la solicitud para ser revisor de admisiones.
 *
 * @package Controller
 * @author Jose Vargas
 * @version 1.0
 * 
 */
class RevisorController {
    private $model;

    public function __construct() {
        $this->model = new Revisor();
    }

    /**
     * Maneja la solicitud para ser revisor
     */
    public function procesarSolicitudRevisor() {
        header('Content-Type: application/json');
        
        try {
            session_start();
            
            // Validar autenticación y rol
            if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'estudiante') {
                http_response_code(403);
                echo json_encode(['error' => 'Acceso no autorizado']);
                return;
            }

            // Obtener datos del POST
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validar campos requeridos
            if (empty($input['carrera_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID de carrera es requerido']);
                return;
            }

            // Procesar solicitud
            $this->model->procesarSolicitudRevisor(
                $_SESSION['usuario_id'],
                $input['carrera_id']
            );

            echo json_encode([
                'success' => true,
                'message' => 'Permisos de revisor otorgados exitosamente'
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
?>