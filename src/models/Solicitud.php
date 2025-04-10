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

    /**
     * Obtiene las solicitudes de los estudiantes por carrera.
     *
     * @param int $carrera_id ID de la carrera.
     * @return array Solicitudes agrupadas por estudiante
     */
    public function obtenerSolicitudesPorCarrera($carrera_id) {
        $sql = "
            SELECT 
                c.carrera_id,
                c.nombre AS carrera_nombre,
                e.estudiante_id,
                e.numero_cuenta,
                e.nombre AS estudiante_nombre,
                e.apellido AS estudiante_apellido,
                s.solicitud_id,
                s.tipo_solicitud_id,
                ts.nombre AS tipo_solicitud,
                s.fecha_solicitud,
                es.nombre AS estado_solicitud
            FROM Carrera c
            JOIN EstudianteCarrera ec ON c.carrera_id = ec.carrera_id
            JOIN Estudiante e ON ec.estudiante_id = e.estudiante_id
            JOIN Solicitud s ON e.estudiante_id = s.estudiante_id
            JOIN EstadoSolicitud es ON s.estado_solicitud_id = es.estado_solicitud_id
            JOIN TipoSolicitud ts ON s.tipo_solicitud_id = ts.tipo_solicitud_id
            WHERE c.carrera_id = ?
            GROUP BY 
                c.carrera_id, c.nombre, 
                e.estudiante_id, e.numero_cuenta, e.nombre, e.apellido, 
                s.solicitud_id, ts.nombre, s.fecha_solicitud, es.nombre
            ORDER BY e.estudiante_id, s.fecha_solicitud
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $carrera_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $solicitudes = [];
        while ($row = $result->fetch_assoc()) {
            $solicitudes['carrera']['carrera_id'] = $row['carrera_id'];
            $solicitudes['carrera']['carrera_nombre'] = $row['carrera_nombre'];
            $solicitudes['estudiantes'][$row['estudiante_id']]['estudiante_id'] = $row['estudiante_id'];
            $solicitudes['estudiantes'][$row['estudiante_id']]['numero_cuenta'] = $row['numero_cuenta'];
            $solicitudes['estudiantes'][$row['estudiante_id']]['nombre'] = $row['estudiante_nombre'];
            $solicitudes['estudiantes'][$row['estudiante_id']]['apellido'] = $row['estudiante_apellido'];
            $solicitudes['estudiantes'][$row['estudiante_id']]['solicitudes'][] = [
                'solicitud_id' => $row['solicitud_id'],
                'tipo_solicitud' => $row['tipo_solicitud'],
                'fecha_solicitud' => $row['fecha_solicitud'],
                'estado_solicitud' => $row['estado_solicitud']
            ];
        }
        
        return array_values($solicitudes);
    }

    /**
     * Obtiene todos los datos de una solicitud incluyendo el tipo y estado.
     *
     * @param int $solicitud_id ID de la solicitud
     * @return array Detalles de la solicitud
     * @author Jose Vargas
     */
    public function obtenerSolicitudPorId($solicitud_id) {
        $sql = "
            SELECT 
                s.solicitud_id,
                s.estudiante_id,
                e.nombre AS estudiante_nombre,
                e.apellido AS estudiante_apellido,
                s.fecha_solicitud,
                ts.nombre AS tipo_solicitud,
                es.nombre AS estado_solicitud,
                s.archivo_pdf
            FROM Solicitud s
            JOIN Estudiante e ON s.estudiante_id = e.estudiante_id
            JOIN TipoSolicitud ts ON s.tipo_solicitud_id = ts.tipo_solicitud_id
            JOIN EstadoSolicitud es ON s.estado_solicitud_id = es.estado_solicitud_id
            WHERE s.solicitud_id = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $solicitud_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Verificar si se encontró la solicitud
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            throw new Exception('Solicitud no encontrada');
        }
    }

    
    /**
     * Obtiene solicitudes por tipo
     * 
     * @param string $tipoSolicitud Nombre del tipo de solicitud
     * @return array
     * @throws Exception
     * @author Jose Vargas
     */
    public function obtenerSolicitudesPorTipo($tipoSolicitud) {
        $sql = "
            SELECT 
                s.solicitud_id,
                s.estudiante_id,
                e.nombre,
                e.apellido,
                s.tipo_solicitud_id,
                ts.nombre AS tipo_solicitud,
                s.motivo_id,
                s.fecha_solicitud,
                s.archivo_pdf,
                s.estado_solicitud_id,
                es.nombre AS estado
            FROM Solicitud s
            INNER JOIN TipoSolicitud ts ON s.tipo_solicitud_id = ts.tipo_solicitud_id
            INNER JOIN Estudiante e ON s.estudiante_id = e.estudiante_id
            INNER JOIN EstadoSolicitud es ON s.estado_solicitud_id= es.estado_solicitud_id
            WHERE ts.nombre = ?
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }

        $stmt->bind_param("s", $tipoSolicitud);
        
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando la consulta: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $solicitudes = [];
        while ($row = $result->fetch_assoc()) {
            $solicitudes[] = $row;
        }
        
        $stmt->close();
        return $solicitudes;
    }










}