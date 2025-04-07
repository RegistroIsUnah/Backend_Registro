<?php
/**
 * Controlador de Seccion
 *
 * Maneja la lógica de negocio para crear una sección.
 *
 * @package Controllers
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

require_once __DIR__ . '/../models/Seccion.php';

class SeccionController {
    private $modelo;

    public function __construct() {
        $this->modelo = new Seccion();
    }
   /**
     * Valida y procesa la creación de una sección.
     *
     * Se espera recibir los siguientes campos en $data:
     * - clase_id
     * - docente_id
     * - periodo_academico_id
     * - aula_id
     * - cupos
     * - hora_inicio (formato "HH:MM:SS")
     * - hora_fin (formato "HH:MM:SS")
     * - dias (cadena de días separados por comas, ej: "Lunes,Miércoles")
     *
     * @param array $data Datos recibidos del endpoint (JSON).
     * @return void
     */
    public function crearSeccion($data, $files) {
        // Validar campos requeridos (clase_id, docente_id, periodo_academico_id, etc.)
        $required = ['clase_id', 'docente_id', 'periodo_academico_id', 'aula_id', 'hora_inicio', 'hora_fin', 'cupos', 'dias'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Falta el campo $field"]);
                exit;
            }
        }

        // Convertir numéricos
        $clase_id             = intval($data['clase_id']);
        $docente_id           = intval($data['docente_id']);
        $periodo_academico_id = intval($data['periodo_academico_id']);
        $aula_id              = intval($data['aula_id']);
        $cupos                = intval($data['cupos']);
        $hora_inicio          = $data['hora_inicio'];
        $hora_fin             = $data['hora_fin'];
        $dias                 = $data['dias'];

        // Manejo de archivo de video (opcional).
        $video_url = null;
        if (isset($files['video']) && $files['video']['error'] === UPLOAD_ERR_OK) {
            // Validar tipo de archivo de video 
            $allowedVideoTypes = ['video/mp4', 'video/avi', 'video/mpeg', 'video/quicktime'];
            if (!in_array($files['video']['type'], $allowedVideoTypes)) {
                http_response_code(400);
                echo json_encode(['error' => 'Tipo de video no permitido']);
                exit;
            }
            // Subir el archivo
            $uploadsDirVideo = __DIR__ . '/../../uploads/videos/';
            if (!is_dir($uploadsDirVideo)) {
                mkdir($uploadsDirVideo, 0755, true);
            }
            $extVideo = pathinfo($files['video']['name'], PATHINFO_EXTENSION);
            $videoName = uniqid('video_', true) . '.' . $extVideo;
            $fullPathVideo = $uploadsDirVideo . $videoName;

            if (!move_uploaded_file($files['video']['tmp_name'], $fullPathVideo)) {
                http_response_code(500);
                echo json_encode(['error' => 'No se pudo guardar el video']);
                exit;
            }
            // Guardar la ruta relativa
            $video_url = 'uploads/videos/' . $videoName;
        }

        try {
            // Instanciar el modelo y crear la sección
            $seccionModel = new Seccion();
            $seccion_id = $seccionModel->crearSeccion(
                $clase_id,
                $docente_id,
                $periodo_academico_id,
                $aula_id,
                $hora_inicio,
                $hora_fin,
                $cupos,
                $dias,
                $video_url
            );
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }

        http_response_code(200);
        echo json_encode([
            'seccion_id' => $seccion_id,
            'message' => 'Sección creada exitosamente'
        ]);
    }

    /**
     * Valida y procesa la modificación de una sección.
     *
     * Se espera recibir en $data los siguientes campos:
     * - seccion_id (requerido)
     * - docente_id (opcional)
     * - aula_id (opcional)
     * - estado (opcional, 'ACTIVA' o 'CANCELADA')
     * - motivo_cancelacion (opcional, requerido si estado es 'CANCELADA')
     * - cupos (opcional)
     * - video_url (opcional)
     *
     * @param array $data Datos recibidos del endpoint.
     * @return void
     */
    public function modificarSeccion($data) {
        // Validar campos requeridos
        if (!isset($data['seccion_id']) || empty($data['seccion_id'])) {
            http_response_code(400);
            echo json_encode(['error' => "Falta el campo seccion_id"]);
            exit;
        }
        
        // Asignar variables con valores de la solicitud
        $seccion_id = intval($data['seccion_id']);
        $docente_id = isset($data['docente_id']) && $data['docente_id'] !== "" ? intval($data['docente_id']) : null;
        $aula_id = isset($data['aula_id']) && $data['aula_id'] !== "" ? intval($data['aula_id']) : null;
        $estado = isset($data['estado']) && $data['estado'] !== "" ? $data['estado'] : null;
        $motivo_cancelacion = isset($data['motivo_cancelacion']) && $data['motivo_cancelacion'] !== "" ? $data['motivo_cancelacion'] : null;
        $cupos = isset($data['cupos']) && $data['cupos'] !== "" ? intval($data['cupos']) : null;
        $video_url = isset($data['video_url']) && $data['video_url'] !== "" ? $data['video_url'] : null;

        try {
            // Instanciar el modelo Seccion y llamar a la función para modificar la sección
            $seccionModel = new Seccion();
            $mensaje = $seccionModel->modificarSeccion(
                $seccion_id,
                $docente_id,
                $aula_id,
                $estado,
                $motivo_cancelacion,
                $cupos,
                $video_url
            );
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }

        http_response_code(200);
        echo json_encode(['message' => $mensaje]);
    }

     /**
     * Obtiene las secciones de una clase y envía la respuesta en JSON.
     *
     * @param int $clase_id ID de la clase.
     * @return void
     */
    public function getSeccionesPorClase($clase_id) {
        try {
            $seccionModel = new Seccion();
            $secciones = $seccionModel->obtenerSeccionesPorClase($clase_id);
            http_response_code(200);
            echo json_encode($secciones);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Obtiene las secciones de una clase en estado activo y envía la respuesta en JSON.
     *
     * @param int $clase_id ID de la clase.
     * @return void
     */
    public function getSeccionesPorClaseMatricula($clase_id) {
        try {
            $seccionModel = new Seccion();
            $secciones = $seccionModel->obtenerSeccionesPorClaseMatricula($clase_id);
            http_response_code(200);
            echo json_encode($secciones);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }



    /**
     * Obtiene la lista de estudiantes de una sección específica
     * 
     * @param int $seccionId ID de la sección
     * @return void
     * @author Jose Vargas
     * @version 1.0
     */
    public function seccionListaEstudiantes($seccionId) {
        header('Content-Type: application/json');
        
        try {
            // Obtener datos del modelo
            $estudiantes = $this->modelo->seccionListaEstudiantes($seccionId);
            
            // Formatear respuesta
            $response = [
                'success' => true,
                'data' => array_map(function($estudiante) {
                    return [
                        'numero_cuenta' => $estudiante['numero_cuenta'],
                        'nombre' => $estudiante['nombre'],
                        'apellido' => $estudiante['apellido'],
                        'correo_personal' => $estudiante['correo_personal']
                    ];
                }, $estudiantes)
            ];
            
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
?>
