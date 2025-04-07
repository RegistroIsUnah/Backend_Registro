<?php
/**
 * Controlador de Solicitud.
 *
 * Encapsula la lógica de porcesos relacionado a solicitud
 *
 * @package Controllers
 * @author Ruben Diaz
 * @version 1.2
 * 
 */

require_once __DIR__ . '/../models/Solicitud.php';


class SolicitudController {

    private $model;

    public function __construct() {
        $this->model = new Solicitud();
    }

     /**
     * Crear una solicitud extraordinaria.
     *
     * @param array $data Datos recibidos del endpoint.
     * @return void
     */
    public function crearSolicitud($data) {
        if (!isset($data['estudiante_id'], $data['tipo_solicitud'], $_FILES['archivo_pdf'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Faltan datos: estudiante_id, tipo_solicitud y archivo_pdf son requeridos']);
            exit;
        }

        try {
            // Subir el archivo PDF
            $archivo_pdf = $this->model->subirArchivo($_FILES['archivo_pdf']);

            // Crear la solicitud
            $solicitud_id = $this->model->crearSolicitud(
                $data['estudiante_id'],
                $data['tipo_solicitud'],
                $archivo_pdf
            );

            http_response_code(200);
            echo json_encode(['message' => 'Solicitud creada correctamente', 'solicitud_id' => $solicitud_id]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Lista las solicitudes agrupadas por estudiantes de una carrera específica.
     *
     * @param array $data Datos recibidos del endpoint (ID de la carrera).
     * @return void
     */
    public function listarSolicitudesPorCarrera($data) {
        if (!isset($data['carrera_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Falta el parámetro: carrera_id']);
            exit;
        }

        $carrera_id = intval($data['carrera_id']);

        try {
            // Obtener las solicitudes por carrera
            $solicitudes = $this->model->obtenerSolicitudesPorCarrera($carrera_id);
            http_response_code(200);
            echo json_encode($solicitudes);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Obtiene todos los detalles de una solicitud incluyendo el tipo y estado.
     *
     * @param array $data Datos recibidos del endpoint (solicitud_id).
     * @return void
     */
    public function obtenerSolicitud($data) {
        if (!isset($data['solicitud_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Falta el parámetro: solicitud_id']);
            exit;
        }

        $solicitud_id = intval($data['solicitud_id']);

        try {
            // Llamamos al modelo para obtener la solicitud
            $modelo = new Solicitud();
            $solicitud = $modelo->obtenerSolicitudPorId($solicitud_id);

            // Retornamos los detalles de la solicitud
            http_response_code(200);
            echo json_encode($solicitud);

        } catch (Exception $e) {
            // Manejo de errores
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
?>