<?php
/**
 * Controlador de Laboratorio
 *
 * @package Controllers
 * @author Ruben Diaz
 * @version 1.1
 * 
 */

 require_once __DIR__ . '/../models/Laboratorio.php';

 class LaboratorioController {
    
    private $modelo; // Propiedad para almacenar el modelo

    /**
     * Constructor del controlador.
     */
    public function __construct() {
        // Inicializar el modelo Laboratorio
        $this->modelo = new Laboratorio(); // Ya no necesitamos pasar la conexión aquí
    }

    /**
     * Obtener los laboratorios de una clase específica.
     *
     * @param int $clase_id ID de la clase
     * @return void Responde con los detalles de los laboratorios
     */
    public function obtenerLaboratorios($clase_id) {
        $laboratorios = $this->modelo->obtenerLaboratorios($clase_id);

        if (!empty($laboratorios)) {
            http_response_code(200);
            echo json_encode(['laboratorios' => $laboratorios]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'No se encontraron laboratorios para esta clase']);
        }
    }
}
?>