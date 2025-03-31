<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Modelo para la relación UsuarioRol.
 *
 * Permite el manejo de la tabla UsuarioRol de la base de datos.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class UsuarioRol {
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
     * Asigna roles a un usuario.
     *
     * @param int $usuario_id ID del usuario.
     * @param array $roles Array de role IDs a asignar.
     * @return bool True si se asignaron correctamente.
     * @throws Exception Si ocurre un error.
     */
    public function asignarRoles($usuario_id, $roles) {
        $this->conn->begin_transaction();
        try {
            // Usamos INSERT IGNORE para evitar duplicados si el usuario ya tiene el rol.
            $stmt = $this->conn->prepare("INSERT IGNORE INTO UsuarioRol (usuario_id, rol_id) VALUES (?, ?)");
            if (!$stmt) {
                throw new Exception("Error preparando la consulta: " . $this->conn->error);
            }
            foreach ($roles as $rol_id) {
                $rol_id = (int)$rol_id;
                $stmt->bind_param("ii", $usuario_id, $rol_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error asignando rol: " . $stmt->error);
                }
            }
            $stmt->close();
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
    
    /**
     * Quita roles de un usuario.
     *
     * @param int $usuario_id ID del usuario.
     * @param array $roles Array de role IDs a quitar.
     * @return bool True si se quitaron correctamente.
     * @throws Exception Si ocurre un error.
     */
    public function quitarRoles($usuario_id, $roles) {
        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare("DELETE FROM UsuarioRol WHERE usuario_id = ? AND rol_id = ?");
            if (!$stmt) {
                throw new Exception("Error preparando la consulta: " . $this->conn->error);
            }
            foreach ($roles as $rol_id) {
                $rol_id = (int)$rol_id;
                $stmt->bind_param("ii", $usuario_id, $rol_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error eliminando rol: " . $stmt->error);
                }
            }
            $stmt->close();
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
}
?>
