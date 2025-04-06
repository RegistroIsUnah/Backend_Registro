<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Clase Solicitud
 *
 * Maneja la interacción con la tabla 'Solicitud' en la base de datos.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class Solicitud {
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;

    /**
     * Constructor de la clase Solicitud.
     *
     * Establece la conexión con la base de datos.
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

     /**
     * Crea una nueva solicitud extraordinaria.
     *
     * @param int $estudiante_id ID del estudiante.
     * @param string $tipo_solicitud Nombre del tipo de solicitud.
     * @param string $archivo_pdf Archivo PDF subido.
     * @return int ID de la solicitud creada.
     */
    public function crearSolicitud($estudiante_id, $tipo_solicitud, $archivo_pdf) {
        // Obtener el estado de la solicitud "PENDIENTE"
        $sqlEstadoPendiente = "SELECT estado_solicitud_id FROM EstadoSolicitud WHERE nombre = 'PENDIENTE'";
        $stmt = $this->conn->prepare($sqlEstadoPendiente);
        $stmt->execute();
        $estado_solicitud_id = $stmt->get_result()->fetch_assoc()['estado_solicitud_id'];

        if (!$estado_solicitud_id) {
            throw new Exception("Estado 'PENDIENTE' no encontrado en la tabla EstadoSolicitud");
        }

        // Buscar el ID del tipo de solicitud
        $sqlTipoSolicitud = "SELECT tipo_solicitud_id FROM TipoSolicitud WHERE nombre = ?";
        $stmt = $this->conn->prepare($sqlTipoSolicitud);
        $stmt->bind_param("s", $tipo_solicitud);
        $stmt->execute();
        $tipo_solicitud_id = $stmt->get_result()->fetch_assoc()['tipo_solicitud_id'];

        if (!$tipo_solicitud_id) {
            throw new Exception("Tipo de solicitud no encontrado");
        }

        // Registrar la solicitud
        $fecha_solicitud = date('Y-m-d'); // Fecha actual
        $sql = "INSERT INTO Solicitud (estudiante_id, tipo_solicitud_id, fecha_solicitud, archivo_pdf, estado_solicitud_id) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iissi", $estudiante_id, $tipo_solicitud_id, $fecha_solicitud, $archivo_pdf, $estado_solicitud_id);

        if (!$stmt->execute()) {
            throw new Exception("Error al registrar la solicitud");
        }

        $solicitud_id = $stmt->insert_id; // Obtener el ID de la solicitud

        // Procesar solicitud según el tipo
        if ($tipo_solicitud_id == 1) { // Cambio de Centro
            $this->registrarCambioCentro($solicitud_id, $_POST['centro_actual_id'], $_POST['centro_nuevo_id']);
        } elseif ($tipo_solicitud_id == 3) { // Cambio de Carrera
            $this->registrarCambioCarrera($solicitud_id, $_POST['carrera_actual_id'], $_POST['carrera_nuevo_id']);
        }

        return $solicitud_id;
    }

     /**
     * Registrar un cambio de centro.
     *
     * @param int $solicitud_id ID de la solicitud.
     * @param int $centro_actual_id ID del centro actual.
     * @param int $centro_nuevo_id ID del nuevo centro.
     */
    private function registrarCambioCentro($solicitud_id, $centro_actual_id, $centro_nuevo_id) {
        $fecha_cambio = date('Y-m-d H:i:s');
        $sql = "INSERT INTO SolicitudCambioCentro (solicitud_id, centro_actual_id, centro_nuevo_id, fecha_cambio)
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiis", $solicitud_id, $centro_actual_id, $centro_nuevo_id, $fecha_cambio);
        $stmt->execute();
    }

    /**
     * Registrar un cambio de carrera.
     *
     * @param int $solicitud_id ID de la solicitud.
     * @param int $carrera_actual_id ID de la carrera actual.
     * @param int $carrera_nuevo_id ID de la nueva carrera.
     */
    private function registrarCambioCarrera($solicitud_id, $carrera_actual_id, $carrera_nuevo_id) {
        $fecha_cambio = date('Y-m-d H:i:s');
        $sql = "INSERT INTO SolicitudCambioCarrera (solicitud_id, carrera_actual_id, carrera_nuevo_id, fecha_cambio)
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiis", $solicitud_id, $carrera_actual_id, $carrera_nuevo_id, $fecha_cambio);
        $stmt->execute();
    }

    /**
     * Subir el archivo PDF de la solicitud.
     *
     * @param array $archivo Archivo PDF subido.
     * @return string Ruta del archivo.
     */
    public function subirArchivo($archivo) {
        $targetDir = __DIR__ . "/../../uploads/solicitudes_exceptcionales/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $fileName = uniqid('solicitud_', true) . '.' . pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $targetFile = $targetDir . $fileName;

        if (!move_uploaded_file($archivo['tmp_name'], $targetFile)) {
            throw new Exception("Error al subir el archivo");
        }

        return $fileName;
    }

}