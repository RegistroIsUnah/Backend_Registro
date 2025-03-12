<?php
/**
 * Controlador de Listas de Espera
 *
 * Maneja la lógica para obtener la lista de esperas.
 *
 * @package Controllers
 * @author Jose Vargas
 * @version 1.0
 * 
 */



require_once __DIR__ . '/../models/ListasEsperaModel.php';



class ListasEsperaController {
    private $model;

    /**
     * Constructor de la clase.
     * Inicializa el modelo necesario para acceder a los datos.
     */
    public function __construct() {
        $this->model = new ListasEspera();
    }

    /**
     * Envía una respuesta JSON al cliente.
     *
     * @param mixed $data Datos a enviar en la respuesta.
     * @param int $statusCode Código de estado HTTP (por defecto 200).
     */
    private function responder($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
    }

    /**
     * Envía un mensaje de error en formato JSON.
     *
     * @param string $mensaje Descripción del error.
     * @param int $statusCode Código de estado HTTP.
     */
    private function error($mensaje, $statusCode) {
        $this->responder(['error' => $mensaje], $statusCode);
    }

    /**
     * Obtiene las listas de espera para un departamento específico.
     * Valida el parámetro 'departamentoId' y maneja las respuestas.
     */
    public function obtenerListasPorDepartamento() {
        // Validar que el parámetro 'departamentoId' esté presente y sea numérico
        if (!isset($_GET['departamentoId']) || !is_numeric($_GET['departamentoId'])) {
            return $this->error('Parámetro departamentoId inválido o faltante', 400);
        }

        try {
            // Convertir el parámetro a entero
            $departamentoId = (int)$_GET['departamentoId'];
            
            // Obtener datos del modelo
            $data = $this->model->obtenerListasEspera($departamentoId);

            // Verificar si hay datos
            if (empty($data)) {
                return $this->error('No hay estudiantes en lista de espera', 404);
            }

            // Procesar y estructurar los datos
            $response = $this->procesarDatos($data);
            
            // Enviar respuesta exitosa
            $this->responder($response);
            
        } catch (Exception $e) {
            // Manejar errores inesperados
            $this->error('Error interno del servidor', 500);
        }
    }

    /**
     * Procesa y estructura los datos obtenidos del modelo.
     *
     * @param array $data Datos crudos obtenidos de la base de datos.
     * @return array Datos estructurados para la respuesta.
     */
    private function procesarDatos($data) {
        $response = [];
        $currentSection = null;

        foreach ($data as $row) {
            // Si es una nueva sección, crear una nueva entrada
            if ($currentSection !== $row['seccion_id']) {
                $currentSection = $row['seccion_id'];
                $response[] = [
                    'seccion_id' => $currentSection,
                    'clase' => $row['clase'],
                    'departamento' => $row['departamento'],
                    'lista_espera' => []
                ];
            }
            
            // Obtener el índice de la última sección agregada
            $lastIndex = count($response) - 1;
            
            // Agregar estudiante a la lista de espera de la sección actual
            $response[$lastIndex]['lista_espera'][] = [
                'estudiante_id' => $row['estudiante_id'],
                'nombre' => $row['nombre'],
                'apellido' => $row['apellido'],
                'correo_personal' => $row['correo_personal'],
                'fecha_solicitud' => $row['fecha_solicitud']
            ];
        }
        
        return $response;
    }
}
?>
