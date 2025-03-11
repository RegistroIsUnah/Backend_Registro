<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Clase Usuario
 *
 * Maneja la interacción con la tabla `Usuario` en la base de datos.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.1
 */
class Usuario {
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
    private $table = "Usuario";

    /**
     * Constructor de la clase Usuario.
     *
     * Establece la conexión con la base de datos.
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtiene un usuario por su nombre de usuario.
     *
     * @param string $username Nombre de usuario a buscar.
     * @return array|null Retorna un array asociativo con los datos del usuario si existe, o null si no se encuentra.
     */
    public function obtenerUsuarioPorUsername($username) {
        $query = "SELECT usuario_id, username, password, rol_id FROM " . $this->table . " WHERE username = ?";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            // Aquí podrías manejar el error de forma personalizada o lanzar una excepción.
            return null;
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();

        $stmt->close(); // Liberamos el recurso

        return $usuario;
    }
}
?>
