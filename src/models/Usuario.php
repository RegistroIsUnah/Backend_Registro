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

    /**
     * Actualiza la contraseña de un usuario
     * 
     * @param int $userId
     * @param string $newPassword
     * @param string|null $oldPassword (opcional para validación)
     * @return bool
     * @throws Exception
     */
    public function cambiarPassword($userId, $newPassword, $oldPassword = null) {
        // Validar contraseña anterior si se provee
        if ($oldPassword !== null) {
            $sql = "SELECT password FROM Usuario WHERE usuario_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Usuario no encontrado");
            }
            
            $user = $result->fetch_assoc();
            if (!password_verify($oldPassword, $user['password'])) {
                throw new Exception("Contraseña actual incorrecta");
            }
        }

        // Hashear nueva contraseña
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // Actualizar en BD
        $sql = "UPDATE Usuario SET password = ? WHERE usuario_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $hashedPassword, $userId);

        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar contraseña: " . $stmt->error);
        }

        return true;
    }

    /**
     * Lista todos los usuarios con los roles que tienen.
     *
     * Solo se listan el usuario (usuario_id y username) y los nombres de los roles asociados.
     *
     * @return array Arreglo de usuarios. Cada usuario es un array con las claves:
     *   - usuario_id
     *   - username
     *   - roles (array de nombres de roles)
     * @throws Exception Si ocurre un error en la consulta.
     */
    public function listarUsuariosConRoles() {
        $sql = "SELECT u.usuario_id, u.username, r.nombre AS rol_nombre
                FROM Usuario u
                INNER JOIN UsuarioRol ur ON u.usuario_id = ur.usuario_id
                INNER JOIN Rol r ON ur.rol_id = r.rol_id
                ORDER BY u.usuario_id";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando la consulta: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // Agrupar los roles por usuario
        $usuarios = [];
        foreach ($data as $row) {
            $uid = $row['usuario_id'];
            if (!isset($usuarios[$uid])) {
                $usuarios[$uid] = [
                    'usuario_id' => $uid,
                    'username'   => $row['username'],
                    'roles'      => []
                ];
            }
            $usuarios[$uid]['roles'][] = $row['rol_nombre'];
        }
        
        return array_values($usuarios);
    }

}
?>
