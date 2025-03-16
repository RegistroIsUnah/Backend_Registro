<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Modelo para Usuario.
 *
 * Encapsula la lógica de Usuario. 
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class Usuario {
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
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
