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
 * 
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
     * Obtiene los roles de un usuario dado su usuario_id.
     *
     * @param int $usuario_id ID del usuario.
     * @return array Lista de roles asociados al usuario.
     */
    public function obtenerRolesPorUsuarioId($usuario_id) {
        $query = "SELECT r.nombre
                  FROM Rol r
                  INNER JOIN UsuarioRol ur ON r.rol_id = ur.rol_id
                  WHERE ur.usuario_id = ?";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $roles = [];

        while ($role = $result->fetch_assoc()) {
            $roles[] = $role['nombre'];
        }

        $stmt->close();

        return $roles;
    }
}
?>
