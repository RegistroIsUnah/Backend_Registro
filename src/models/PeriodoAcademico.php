<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Clase PeriodoAcademico
 *
 * Maneja la interacción con la tabla PeriodoAcademico.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class PeriodoAcademico {
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;

    /**
     * Constructor de la clase PeriodoAcademico.
     *
     * Establece la conexión con la base de datos.
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Crea un nuevo período académico.
     *
     * Si la fecha de fin ya pasó al momento de la creación, se inserta con estado 'INACTIVO';
     * de lo contrario, se inserta como 'ACTIVO'.
     *
     * @param int $anio Año del período.
     * @param string $numero_periodo Número del período ('1', '2', etc.).
     * @param string $fecha_inicio Fecha de inicio en formato "YYYY-MM-DD HH:MM:SS".
     * @param string $fecha_fin Fecha de fin en formato "YYYY-MM-DD HH:MM:SS".
     * @return int ID del período académico creado.
     * @throws Exception Si ocurre un error durante la inserción.
     */
    public function crearPeriodoAcademico($anio, $numero_periodo, $fecha_inicio, $fecha_fin) {
        // Determinar el estado al momento de la inserción:
        // Si la fecha_fin ya pasó, se asigna 'INACTIVO'; de lo contrario, 'ACTIVO'.
        $estado = (strtotime($fecha_fin) < time()) ? 'INACTIVO' : 'ACTIVO';
        
        $stmt = $this->conn->prepare("INSERT INTO PeriodoAcademico (anio, numero_periodo, fecha_inicio, fecha_fin, estado) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
        $stmt->bind_param("issss", $anio, $numero_periodo, $fecha_inicio, $fecha_fin, $estado);
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando la consulta: " . $stmt->error);
        }
        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    }
}
?>
