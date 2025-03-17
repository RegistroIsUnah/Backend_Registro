<?php
/**
 * Controlador de Estudiante
 *
 *
 * @package Controllers
 * @author Jose Vargas
 * @version 1.0
 * 
 */

require_once __DIR__ . '/../models/Estudiante.php';

class EstudianteController {
    private $modelo;

    public function __construct() {
        $this->modelo = new Estudiante();
    }

    /**
     * Obtiene los docentes de las clases del estudiante y envía a la vista
     */
    public function obtenerDocentesClases() {
        header('Content-Type: application/json');
        
        try {
            // Obtener ID del estudiante desde sesión
            if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'estudiante') {
                http_response_code(403);
                echo json_encode(['error' => 'Acceso no autorizado']);
                return;
            }
            
            $estudianteId = $_SESSION['usuario_id'];
            
            // Obtener datos
            $docentesClases = $this->modelo->obtenerDocentesDeClases($estudianteId);
            
            if (empty($docentesClases)) {
                http_response_code(404);
                echo json_encode(['message' => 'No se encontraron clases matriculadas']);
                return;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $docentesClases
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener datos: ' . $e->getMessage()
            ]);
        }
    }
}
?>
