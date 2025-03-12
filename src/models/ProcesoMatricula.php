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
    
    /**
     * Crea un nuevo proceso de matrícula.
     *
     * Si la fecha_fin ya pasó, se inserta con estado 'INACTIVO'; de lo contrario, 'ACTIVO'.
     *
     * @param int $periodo_academico_id ID del período académico.
     * @param string $tipo_proceso Tipo de proceso ('MATRICULA' o 'ADICIONES_CANCELACIONES').
     * @param string $fecha_inicio Fecha de inicio en formato "YYYY-MM-DD HH:MM:SS".
     * @param string $fecha_fin Fecha de fin en formato "YYYY-MM-DD HH:MM:SS".
     * @return int ID del proceso de matrícula creado.
     * @throws Exception Si ocurre un error durante la inserción.
     */
    public function crearProcesoMatricula($periodo_academico_id, $tipo_proceso, $fecha_inicio, $fecha_fin) {
        // Determinar el estado basado en la fecha_fin:
        $estado = (strtotime($fecha_fin) < time()) ? 'INACTIVO' : 'ACTIVO';
        
        $stmt = $this->conn->prepare("INSERT INTO ProcesoMatricula (periodo_academico_id, tipo_proceso, fecha_inicio, fecha_fin, estado) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
        $stmt->bind_param("issss", $periodo_academico_id, $tipo_proceso, $fecha_inicio, $fecha_fin, $estado);
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando la consulta: " . $stmt->error);
        }
        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    }
}
?>
