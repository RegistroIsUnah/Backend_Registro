<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Clase Departamento
 *
 * Maneja la interacción con la tabla Departamento de la base de datos.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class Departamento {
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;
    
    /**
     * Constructor de la clase Departamento.
     *
     * Establece la conexión con la base de datos.
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Obtiene la lista de todos los departamentos.
     *
     * @return array Lista de departamentos.
     * @throws Exception Si ocurre un error en la consulta.
     */
    public function obtenerDepartamentos() {
        $sql = "SELECT dept_id, facultad_id, nombre, jefe_docente_id FROM Departamento";
        $result = $this->conn->query($sql);
        if (!$result) {
            throw new Exception("Error en la consulta: " . $this->conn->error);
        }
        
        $departamentos = [];
        while ($row = $result->fetch_assoc()) {
            $departamentos[] = $row;
        }
        return $departamentos;
    }
}
?>
