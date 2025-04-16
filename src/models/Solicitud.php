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
                e.numero_cuenta,
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




     /**
     * Actualiza el estado de una solicitud
     */
    public function actualizarEstadoSolicitud($solicitud_id, $estado_nombre) {
        // Obtener ID del estado
        $sql = "SELECT estado_solicitud_id FROM EstadoSolicitud 
                WHERE nombre = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $estado_nombre);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Estado no válido");
        }
        
        $estado_id = $result->fetch_assoc()['estado_solicitud_id'];
        
        // Actualizar estado
        $sqlUpdate = "UPDATE Solicitud 
                     SET estado_solicitud_id = ?
                     WHERE solicitud_id = ?";
        $stmt = $this->conn->prepare($sqlUpdate);
        $stmt->bind_param("ii", $estado_id, $solicitud_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar estado");
        }
    }

    /**
     * Registra el motivo de rechazo de una solicitud
     */
    public function registrarMotivoRechazo($solicitud_id, $descripcion) {
        // Insertar nuevo motivo
        $sql = "INSERT INTO MotivoRechazoSolicitud (descripcion)
                VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $descripcion);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al registrar motivo");
        }
        
        $motivo_id = $stmt->insert_id;
        
        // Vincular motivo a la solicitud
        $sqlUpdate = "UPDATE Solicitud 
                     SET motivo_id = ?
                     WHERE solicitud_id = ?";
        $stmt = $this->conn->prepare($sqlUpdate);
        $stmt->bind_param("ii", $motivo_id, $solicitud_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al vincular motivo");
        }
    }

    /**
     * Ejecuta acciones adicionales al aprobar una solicitud
     */
    public function procesarSolicitudAprobada($solicitud_id) {
        // Obtener tipo de solicitud
        $sql = "SELECT tipo_solicitud_id FROM Solicitud 
                WHERE solicitud_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $solicitud_id);
        $stmt->execute();
        $tipo_solicitud_id = $stmt->get_result()->fetch_assoc()['tipo_solicitud_id'];
        
        // Ejecutar acción según tipo
        switch($tipo_solicitud_id) {
            case 1: // Cambio de Centro
                $this->aplicarCambioCentro($solicitud_id);
                break;
                
            case 3: // Cambio de Carrera
                $this->aplicarCambioCarrera($solicitud_id);
                break;
        }
    }

    private function aplicarCambioCarrera($solicitud_id) {
        try {
            // Obtener datos de la solicitud
            $sql = "SELECT scc.carrera_actual_id, scc.carrera_nuevo_id, 
                    s.estudiante_id 
                    FROM SolicitudCambioCarrera scc
                    JOIN Solicitud s ON scc.solicitud_id = s.solicitud_id
                    WHERE scc.solicitud_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $solicitud_id);
            $stmt->execute();
            $data = $stmt->get_result()->fetch_assoc();
    
            if (!$data) {
                throw new Exception("Solicitud de cambio no encontrada");
            }
    
            $estudiante_id = $data['estudiante_id'];
            $carrera_actual = $data['carrera_actual_id'];
            $carrera_nueva = $data['carrera_nuevo_id'];
    
            // Obtener IDs de estados
            $estado_matriculado = $this->obtenerEstadoId('MATRICULADO', 'EstadoAspiranteCarrera');
            $estado_inactivo = $this->obtenerEstadoId('NO APROBADO', 'EstadoAspiranteCarrera');
    
            // Iniciar transacción
            $this->conn->begin_transaction();
    
            // 1. Desactivar carrera actual
            $sqlUpdate = "UPDATE EstudianteCarrera 
                         SET estado_aspirante_carrera_id = ?
                         WHERE estudiante_id = ? 
                         AND carrera_id = ?";
            $stmt = $this->conn->prepare($sqlUpdate);
            $stmt->bind_param("iii", $estado_inactivo, $estudiante_id, $carrera_actual);
            $stmt->execute();
    
            // 2. Verificar filas afectadas
            if ($stmt->affected_rows === 0) {
                throw new Exception("El estudiante no estaba matriculado en la carrera actual");
            }
    
            // 3. Insertar nueva carrera
            $sqlInsert = "INSERT INTO EstudianteCarrera 
                        (estudiante_id, carrera_id, estado_aspirante_carrera_id)
                        VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE 
                        estado_aspirante_carrera_id = VALUES(estado_aspirante_carrera_id)";
            $stmt = $this->conn->prepare($sqlInsert);
            $stmt->bind_param("iii", $estudiante_id, $carrera_nueva, $estado_matriculado);
            $stmt->execute();
    
            $this->conn->commit();
    
        } catch (Exception $e) {
            $this->conn->rollback();
            throw new Exception("Error al aplicar cambio de carrera: " . $e->getMessage());
        }
    }
    
    /**
     * Método auxiliar para obtener IDs de estado
     */
    private function obtenerEstadoId($nombreEstado, $tablaEstado) {
        $sql = "SELECT estado_aspirante_carrera_id 
                FROM $tablaEstado 
                WHERE nombre = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $nombreEstado);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if (!$result) {
            throw new Exception("Estado '$nombreEstado' no encontrado");
        }
        
        return $result['estado_aspirante_carrera_id'];
    }


    
    private function aplicarCambioCentro($solicitud_id) {
        // Obtener datos del cambio
        $sql = "SELECT centro_nuevo_id, estudiante_id 
                FROM SolicitudCambioCentro 
                JOIN Solicitud USING(solicitud_id)
                WHERE solicitud_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $solicitud_id);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();
        
        // Actualizar centro del estudiante
        $sqlUpdate = "UPDATE Estudiante 
                     SET centro_id = ?
                     WHERE estudiante_id = ?";
        $stmt = $this->conn->prepare($sqlUpdate);
        $stmt->bind_param("ii", $data['centro_nuevo_id'], $data['estudiante_id']);
        $stmt->execute();
    }


    public function busquedaAvanzada($estado = null, $solicitud_id = null, $numero_cuenta = null) {
        $sql = "SELECT 
                    s.solicitud_id,
                    s.fecha_solicitud,
                    ts.nombre AS tipo_solicitud,
                    es.nombre AS estado,
                    s.archivo_pdf,
                    s.motivo_id,
                    mrs.descripcion AS motivo,
                    s.estudiante_id,
                    e.numero_cuenta,
                    e.nombre AS nombre,
                    e.apellido AS apellido
                FROM Solicitud s
                JOIN EstadoSolicitud es ON s.estado_solicitud_id = es.estado_solicitud_id
                JOIN TipoSolicitud ts ON s.tipo_solicitud_id = ts.tipo_solicitud_id
                LEFT JOIN MotivoRechazoSolicitud mrs ON s.motivo_id = mrs.motivo_id
                JOIN Estudiante e ON s.estudiante_id = e.estudiante_id
                WHERE 1=1";
        
        $params = [];
        $types = '';
        
        if ($estado) {
            $sql .= " AND es.nombre = ?";
            $params[] = $estado;
            $types .= 's';
        }
        
        if ($solicitud_id) {
            $sql .= " AND s.solicitud_id = ?";
            $params[] = $solicitud_id;
            $types .= 'i';
        }
        
        if ($numero_cuenta) {
            $sql .= " AND e.numero_cuenta LIKE CONCAT('%', ?, '%')";
            $params[] = $numero_cuenta;
            $types .= 's';
        }
        
        $sql .= " ORDER BY s.fecha_solicitud DESC";
    
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

}