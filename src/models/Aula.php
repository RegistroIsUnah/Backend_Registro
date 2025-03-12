<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Clase Aula
 *
 * Maneja la interacción con la tabla `Aula` en la base de datos.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class Aula {
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;

    /**
     * Nombre de la tabla en la base de datos.
     *
     * @var string
     */
    private $table = "Aula";

    /**
     * Constructor de la clase Aula.
     *
     * Establece la conexión con la base de datos.
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtiene las aulas asociadas a un edificio.
     *
     * @param int $edificio_id ID del edificio.
     * @return array Lista de aulas asociadas; retorna un array vacío si no hay resultados.
     * @throws Exception Si falla la preparación de la consulta.
     */
    public function obtenerAulasPorEdificio($edificio_id) {
        $query = "SELECT aula_id, nombre, capacidad FROM " . $this->table . " WHERE edificio_id = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            throw new Exception('Error preparando la consulta: ' . $this->conn->error);
        }
        $stmt->bind_param("i", $edificio_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $aulas = [];
        while ($row = $result->fetch_assoc()) {
            $aulas[] = $row;
        }
        $stmt->close();
        return $aulas;
    }
}
?>
