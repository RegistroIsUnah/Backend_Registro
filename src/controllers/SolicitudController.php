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
}

?>