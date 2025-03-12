<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Clase Carrera
 *
 * Maneja la interacción con la tabla `Carrera` y su relación con CentroCarrera en la base de datos.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class Carrera {
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;

    /**
     * Nombre de la tabla.
     *
     * @var string
     */
    private $table = "Carrera";

    /**
     * Constructor de la clase Carrera.
     *
     * Establece la conexión con la base de datos.
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtiene la lista de carreras.
     *
     * Si se especifica un centro_id, retorna solo las carreras asociadas a ese centro; de lo contrario, retorna todas.
     *
     * @param int|null $centro_id (Opcional) ID del centro para filtrar carreras.
     * @return array Lista de carreras.
     * @throws Exception Si falla la consulta.
     */
    public function obtenerCarreras($centro_id = null) {
        $carreras = [];
        if ($centro_id !== null) {
            // Se asume que existe una relación entre centros y carreras en la tabla CentroCarrera.
            $stmt = $this->conn->prepare("
                SELECT c.carrera_id, c.nombre 
                FROM Carrera c
                INNER JOIN CentroCarrera cc ON c.carrera_id = cc.carrera_id
                WHERE cc.centro_id = ?
            ");
            if (!$stmt) {
                throw new Exception('Error preparando la consulta: ' . $this->conn->error);
            }
            $stmt->bind_param("i", $centro_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $carreras[] = $row;
            }
            $stmt->close();
        } else {
            $sql = "SELECT carrera_id, nombre FROM Carrera";
            $result = $this->conn->query($sql);
            if (!$result) {
                throw new Exception('Error en la consulta: ' . $this->conn->error);
            }
            while ($row = $result->fetch_assoc()) {
                $carreras[] = $row;
            }
        }
        return $carreras;
    }
}
?>
