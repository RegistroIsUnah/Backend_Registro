<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Clase CarreraExamen
 *
 * Maneja la interacción con las tablas relacionadas con las carreras y los exámenes asociados.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class CarreraExamen {
    /** 
     * Conexión a la base de datos.
     * 
     * @var mysqli
     */
    private $conn;

    /**
     * Constructor de la clase CarreraExamen.
     *
     * Establece la conexión con la base de datos.
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtiene todas las carreras con los exámenes asociados a ellas y los puntajes.
     *
     * @return array Lista de carreras con los exámenes y puntajes correspondientes.
     * @throws Exception Si ocurre un error en la consulta.
     */
    public function obtenerCarrerasConExamenesYPuntajes() {
            $sql = "
            SELECT 
                c.carrera_id, 
                c.nombre AS carrera_nombre,
                te.tipo_examen_id,
                te.nombre AS examen_nombre,
                te.nota_minima
            FROM Carrera c
            INNER JOIN CarreraExamen ce ON c.carrera_id = ce.carrera_id
            INNER JOIN TipoExamen te ON ce.tipo_examen_id = te.tipo_examen_id
            ORDER BY c.carrera_id, te.tipo_examen_id;
        ";

        $result = $this->conn->query($sql);

        if (!$result) {
            throw new Exception("Error en la consulta: " . $this->conn->error);
        }

        $carreras = [];
        while ($row = $result->fetch_assoc()) {
            $carreras[] = $row;
        }
        return $carreras;
    }
}
?>
