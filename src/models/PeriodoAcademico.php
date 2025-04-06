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
     * Obtiene el estado_periodo_id correspondiente al estado del periodo ('ACTIVO' o 'INACTIVO')
     *
     * @param string $estado_nombre Estado del periodo ('ACTIVO' o 'INACTIVO')
     * @return int ID del estado del periodo
     * @throws Exception Si el estado no es válido o no se encuentra en la base de datos.
     */
    public function obtenerEstadoPeriodoId($estado_nombre) {
        $stmt = $this->conn->prepare("SELECT estado_periodo_id FROM EstadoPeriodo WHERE nombre = ?");
        if (!$stmt) {
            throw new Exception("Error preparando la consulta de estado: " . $this->conn->error);
        }
        $stmt->bind_param("s", $estado_nombre);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            throw new Exception("Estado del periodo no válido.");
        }
        $row = $result->fetch_assoc();
        $estado_periodo_id = $row['estado_periodo_id'];
        $stmt->close();
        return $estado_periodo_id;
    }

    /**
     * Crea un nuevo período académico.
     *
     * @param int $anio Año del período.
     * @param string $numero_periodo Número del período ('1', '2', etc.).
     * @param string $fecha_inicio Fecha de inicio en formato "YYYY-MM-DD HH:MM:SS".
     * @param string $fecha_fin Fecha de fin en formato "YYYY-MM-DD HH:MM:SS".
     * @param int $estado_periodo_id ID del estado del período académico.
     * @return int ID del período académico creado.
     * @throws Exception Si ocurre un error durante la inserción.
     */
    public function crearPeriodoAcademico($anio, $numero_periodo, $fecha_inicio, $fecha_fin, $estado_periodo_id) {
        $stmt = $this->conn->prepare("INSERT INTO PeriodoAcademico (anio, numero_periodo, fecha_inicio, fecha_fin, estado_periodo_id) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparando la consulta de inserción: " . $this->conn->error);
        }
        $stmt->bind_param("isssi", $anio, $numero_periodo, $fecha_inicio, $fecha_fin, $estado_periodo_id);
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando la consulta: " . $stmt->error);
        }
        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    }

    /**
     * Obtiene los periodos académicos activos, comprobando que la fecha actual no haya pasado.
     *
     * @return array Lista de periodos académicos activos
     * @throws Exception Si ocurre un error en la consulta
     */
    public function obtenerPeriodosActivos() {
        // Obtener la fecha actual
        $fecha_actual = date('Y-m-d');

        // Consulta SQL para obtener los periodos académicos activos y comparar con la fecha actual
        $sql = "
            SELECT 
                pa.periodo_academico_id,
                pa.anio,
                np.nombre AS numero_periodo,
                pa.fecha_inicio,
                pa.fecha_fin,
                ep.nombre AS estado_proceso
            FROM 
                PeriodoAcademico pa
            JOIN 
                NumeroPeriodo np ON pa.numero_periodo_id = np.numero_periodo_id
            JOIN 
                EstadoProceso ep ON pa.estado_proceso_id = ep.estado_proceso_id
            WHERE 
                ep.nombre = 'ACTIVO'
                AND pa.fecha_fin >= ? -- Comparar la fecha de fin con la fecha actual
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }

        $stmt->bind_param("s", $fecha_actual); // Pasamos la fecha actual a la consulta
        $stmt->execute();
        $result = $stmt->get_result();

        // Comprobamos si existen periodos académicos activos
        $periodos = [];
        while ($row = $result->fetch_assoc()) {
            $periodos[] = $row;
        }

        $stmt->close();

        // Devolver los periodos activos
        return $periodos;
    }
}
?>
