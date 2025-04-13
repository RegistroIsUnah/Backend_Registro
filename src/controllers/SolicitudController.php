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
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
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

    /**
     * Obtiene solicitudes por tipo
     * 
     * @return void
     * @version 1.0
     * @author Jose Vargas
     */
    public function obtenerSolicitudesPorTipo($tipoSolicitud) {
        header('Content-Type: application/json');
    
        try {
            if (empty($tipoSolicitud)) {
                throw new Exception('Parámetro tipo_solicitud requerido', 400);
            }
    
            $tipoSolicitud = filter_var($tipoSolicitud);
    
            $solicitudes = $this->model->obtenerSolicitudesPorTipo($tipoSolicitud);
    
            $response = [
                'success' => true,
                'data' => $solicitudes,
                'meta' => [
                    'total' => count($solicitudes),
                    'tipo_solicitud' => $tipoSolicitud
                ]
            ];
    
            if (empty($solicitudes)) {
                $response['message'] = 'No se encontraron solicitudes de este tipo';
            }
    
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }


    /**
     * Endpoint para aceptar una solicitud
     */
    public function aceptarSolicitud() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);

        try {
            // Validaciones básicas
            if (!isset($data['solicitud_id'])) {
                throw new Exception("Falta el ID de la solicitud");
            }

            $solicitud_id = (int)$data['solicitud_id'];
            
            // 1. Actualizar estado a APROBADA
            $this->model->actualizarEstadoSolicitud(
                $solicitud_id, 
                'APROBADA'
            );

            /*
            // 2. Ejecutar acciones adicionales según tipo de solicitud
            $this->model->procesarSolicitudAprobada($solicitud_id);
            */
            echo json_encode([
                'success' => true,
                'message' => 'Solicitud aprobada exitosamente'
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Endpoint para rechazar una solicitud
     */
    public function rechazarSolicitud() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);

        try {
            // Validar campos requeridos
            $required = ['solicitud_id', 'motivo_id'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new Exception("Falta el campo: $field");
                }
            }

            $solicitud_id = (int)$data['solicitud_id'];
            $motivo_id = (int)$data['motivo_id'];

            // Verificar si el motivo existe
            $sqlCheck = "SELECT motivo_id FROM MotivoRechazoSolicitud WHERE motivo_id = ?";
            $stmt = $this->conn->prepare($sqlCheck);
            $stmt->bind_param("i", $motivo_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Motivo de rechazo no válido");
            }

            // 1. Actualizar estado a DENEGADA
            $this->model->actualizarEstadoSolicitud(
                $solicitud_id, 
                'DENEGADA'
            );

            // 2. Actualizar solicitud con el motivo de rechazo
            $sqlUpdate = "UPDATE Solicitud 
                        SET motivo_id = ?
                        WHERE solicitud_id = ?";
            $stmt = $this->conn->prepare($sqlUpdate);
            $stmt->bind_param("ii", $motivo_id, $solicitud_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al vincular motivo");
            }

            echo json_encode([
                'success' => true,
                'message' => 'Solicitud rechazada exitosamente'
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }


    /**
     * Búsqueda avanzada de solicitudes con múltiples filtros
     * 
     * @param string|null $estado
     * @param int|null $solicitud_id
     * @param string|null $numero_cuenta
     * @return array
     */
    public function buscarSolicitudesAvanzado(
        ?string $estado = null,
        ?int $solicitud_id = null,
        ?string $numero_cuenta = null
    ): array {
        try {
            // Sanitizar inputs
            $filters = [
                'estado' => $estado ? htmlspecialchars($estado) : null,
                'solicitud_id' => $solicitud_id,
                'numero_cuenta' => $numero_cuenta ? htmlspecialchars($numero_cuenta) : null
            ];

            return $this->model->busquedaAvanzada(
                $filters['estado'],
                $filters['solicitud_id'],
                $filters['numero_cuenta']
            );

        } catch (Exception $e) {
            throw new Exception("Error en la búsqueda: " . $e->getMessage());
        }
    }


}
?>
