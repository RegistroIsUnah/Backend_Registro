<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Clase Tag
 *
 * Maneja la interacción con la tabla Tag.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class Tag {
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;
    
    /**
     * Constructor de la clase Tag.
     *
     * Establece la conexión con la base de datos.
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Obtiene todos los tags con su información.
     *
     * @return array Lista de tags.
     * @throws Exception Si ocurre un error en la consulta.
     */
    public function obtenerTags() {
        $sql = "SELECT tag_id, tag_nombre FROM Tag";
        $result = $this->conn->query($sql);
        if (!$result) {
            throw new Exception("Error en la consulta: " . $this->conn->error);
        }
        $tags = [];
        while ($row = $result->fetch_assoc()) {
            $tags[] = $row;
        }
        return $tags;
    }
}
?>
