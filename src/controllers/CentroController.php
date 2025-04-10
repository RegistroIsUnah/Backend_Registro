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
     * Modelo de secciones
     * @var Centro
     */
    private $modelo;
    
    /**
     * Constructor - Inicializa el modelo
     */
    public function __construct()
    {
        require_once __DIR__ . '/../models/Seccion.php';
        $this->modelo = new Centro();
    }

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

    /**
     * Obtiene la lista de edificios y envía la respuesta en formato JSON.
     *
     * @return void
     */
    public function getEdificios() {
        header('Content-Type: application/json');
        
        try {
            $edificios = $this->modelo->obtenerTodosEdificios();
            
            echo json_encode([
                'success' => true,
                'data' => $edificios,
                'message' => 'Listado de edificios obtenido correctamente'
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener los edificios: ' . $e->getMessage()
            ]);
        }
    }
}
?>
