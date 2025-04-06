<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Clase ProcesoMatricula
 *
 * Maneja la interacción con la tabla ProcesoMatricula.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class ProcesoMatricula {
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;
    
    /**
     * Constructor de la clase ProcesoMatricula.
     *
     * Establece la conexión con la base de datos.
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Función para obtener el estado 'ACTIVO' de la base de datos
    private function obtenerEstadoProcesoId($estado_nombre) {
        $stmt = $this->conn->prepare("SELECT estado_proceso_id FROM EstadoProceso WHERE nombre = ?");
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
        $stmt->bind_param("s", $estado_nombre);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            throw new Exception("Estado del proceso no válido.");
        }
        $row = $result->fetch_assoc();
        return $row['estado_proceso_id'];
    }

    // Función para obtener el tipo de proceso
    private function obtenerTipoProcesoId($tipo_proceso) {
        $stmt = $this->conn->prepare("SELECT tipo_proceso_id FROM TipoProcesoMatricula WHERE nombre = ?");
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
        $stmt->bind_param("s", $tipo_proceso);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            throw new Exception("Tipo de proceso no válido.");
        }
        $row = $result->fetch_assoc();
        return $row['tipo_proceso_id'];
    }

     /**
     * Crea un nuevo proceso de matrícula.
     *
     * Si la fecha_fin ya pasó, se inserta con estado 'INACTIVO';
     * de lo contrario, 'ACTIVO'.
     *
     * @param int $periodo_academico_id ID del período académico.
     * @param string $tipo_proceso Tipo de proceso ('MATRICULA' o 'ADICIONES_CANCELACIONES').
     * @param string $fecha_inicio Fecha de inicio en formato "YYYY-MM-DD HH:MM:SS".
     * @param string $fecha_fin Fecha de fin en formato "YYYY-MM-DD HH:MM:SS".
     * @return int ID del proceso de matrícula creado.
     * @throws Exception Si ocurre un error durante la inserción.
     */
    public function crearProcesoMatricula($periodo_academico_id, $tipo_proceso, $fecha_inicio, $fecha_fin) {
        // Obtener el estado 'ACTIVO'
        $estado_proceso_id = $this->obtenerEstadoProcesoId('ACTIVO');
        
        // Obtener el tipo de proceso
        $tipo_proceso_id = $this->obtenerTipoProcesoId($tipo_proceso);

        // Insertar el nuevo proceso de matrícula con el estado y tipo de proceso
        $stmt = $this->conn->prepare("INSERT INTO ProcesoMatricula (periodo_academico_id, tipo_proceso_id, fecha_inicio, fecha_fin, estado_proceso_id) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparando la consulta de inserción: " . $this->conn->error);
        }
        $stmt->bind_param("iissi", $periodo_academico_id, $tipo_proceso_id, $fecha_inicio, $fecha_fin, $estado_proceso_id);
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando la consulta: " . $stmt->error);
        }
        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    }
}
?>
