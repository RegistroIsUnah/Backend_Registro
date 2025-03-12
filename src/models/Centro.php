<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Clase Centro
 *
 * Maneja la interacción con la tabla `Centro` en la base de datos.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class Centro {
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
    private $table = "Centro";

    /**
     * Constructor de la clase Centro.
     *
     * Establece la conexión con la base de datos.
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtiene la lista de centros.
     *
     * @return array Lista de centros.
     * @throws Exception Si falla la consulta.
     */
    public function obtenerCentros() {
        $centros = [];
        $sql = "SELECT centro_id, nombre FROM " . $this->table;
        $result = $this->conn->query($sql);
        if (!$result) {
            throw new Exception("Error en la consulta: " . $this->conn->error);
        }
        while ($row = $result->fetch_assoc()) {
            $centros[] = $row;
        }
        return $centros;
    }
}
?>
