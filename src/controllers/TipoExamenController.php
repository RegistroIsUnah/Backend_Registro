<?php
/**
 * Controlador de TipoExamen
 *
 * Maneja las solicitudes relacionadas con los tipos de examen.
 *
 * @package Controllers
 */

require_once __DIR__ . '/../models/TipoExamen.php';

/**
 * Clase TipoExamenController
 *
 * Controlador para crear y gestionar los tipos de examen.
 * 
 * @package Controllers
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class TipoExamenController {

    /**
     * Instancia del modelo TipoExamen.
     *
     * @var TipoExamen
     */
    private $modelo;

    /**
     * Constructor del controlador.
     * 
     * Inicializa el modelo TipoExamen.
     */
    public function __construct() {
        $this->modelo = new TipoExamen();
    }

     /**
     * Crear un nuevo tipo de examen.
     * 
     * Recibe los datos de la solicitud y crea un nuevo tipo de examen.
     *
     * @return void Responde con un mensaje de éxito o error en formato JSON.
     */
    public function crearTipoExamen() {
        // Validar los datos entrantes
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->nombre) || !isset($data->nota_minima)) {
            echo json_encode(['error' => 'El nombre y la nota mínima son requeridos.']);
            return;
        }

        $nombre = $data->nombre;
        $nota_minima = $data->nota_minima;

        try {
            // Crear el tipo de examen
            $resultado = $this->modelo->crearTipoExamen($nombre, $nota_minima);
            
            // Enviar respuesta
            echo json_encode($resultado);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Asocia un examen con una carrera.
     * 
     * Recibe los datos de la solicitud y crea la asociación.
     *
     * @return void Responde con un mensaje de éxito o error en formato JSON.
     */
    public function asociarExamenCarrera() {
        // Validar los datos entrantes
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->examen_id) || !isset($data->carrera_id)) {
            echo json_encode(['error' => 'El examen_id y carrera_id son requeridos.']);
            return;
        }

        $examen_id = $data->examen_id;
        $carrera_id = $data->carrera_id;

        try {
            // Asociar el examen con la carrera
            $resultado = $this->modelo->asociarExamenCarrera($examen_id, $carrera_id);
            
            // Enviar respuesta
            echo json_encode($resultado);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Modificar los detalles de un examen.
     * 
     * Recibe los datos de la solicitud y modifica el examen.
     *
     * @return void Responde con un mensaje de éxito o error en formato JSON.
     */
    public function modificarExamen() {
        // Validar los datos entrantes
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->examen_id)) {
            echo json_encode(['error' => 'El examen_id es requerido.']);
            return;
        }

        $examen_id = $data->examen_id;
        $nombre = isset($data->nombre) ? $data->nombre : null;
        $nota_minima = isset($data->nota_minima) ? $data->nota_minima : null;

        try {
            // Modificar el examen en el modelo
            $resultado = $this->modelo->modificarExamen($examen_id, $nombre, $nota_minima);
            
            // Enviar respuesta
            echo json_encode($resultado);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

     /**
     * Desasocia múltiples exámenes de una carrera.
     *
     * Recibe los datos de la solicitud y elimina la relación entre los exámenes y la carrera.
     *
     * @param array $examen_ids Array de IDs de los exámenes.
     * @param int $carrera_id ID de la carrera.
     * @return void Responde con un mensaje de éxito o error en formato JSON.
     */
    public function desasociarExamenesDeCarrera($examen_ids, $carrera_id) {
        try {
            // Desasociar los exámenes de la carrera
            $resultado = $this->modelo->desasociarExamenesDeCarrera($examen_ids, $carrera_id);
            
            // Enviar respuesta
            echo json_encode($resultado);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
?>