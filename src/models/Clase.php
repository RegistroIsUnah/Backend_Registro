<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Clase Docente
 * 
 * Clase para manejar la interacción con la tabla `Clase` en la base de datos.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class Clase {
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;
    
    /**
     * Constructor de la clase Clase.
     *
     * Establece la conexión con la base de datos.
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Obtiene las clases de un departamento.
     *
     * @param int $dept_id ID del departamento.
     * @return array Lista de clases.
     * @throws Exception Si ocurre un error en la consulta.
     */
    public function obtenerClasesPorDepartamento($dept_id) {
        $query = "SELECT clase_id, codigo, nombre, creditos, tiene_laboratorio FROM Clase WHERE dept_id = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
        $stmt->bind_param("i", $dept_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $clases = [];
        while ($row = $result->fetch_assoc()) {
            $clases[] = $row;
        }
        $stmt->close();
        return $clases;
    }
}
?>
