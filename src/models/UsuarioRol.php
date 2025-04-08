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


    /**
     * Asigna roles a un docente, y en caso de ser Coordinador o Jefe de Departament
     * 
     * @param int $docente_id ID del docente.
     * @param array $roles Array de role nombres a asignar.
     * @param int $departamento_id (Opcional) ID del departamento.
     * @param int $carrera_id (Opcional) ID de la carrera.
     * @return bool True si se asignaron correctamente.
     * @throws Exception Si ocurre un error.
     */
    public function asignarRolesDocente($docente_id, $roles, $departamento_id = null, $carrera_id = null) {
        // Comienza la transacción
        $this->conn->begin_transaction();
        try {
            // Validar si el docente ya tiene un rol conflictivo
            $stmt = $this->conn->prepare("SELECT r.nombre FROM UsuarioRol ur 
                                          JOIN Rol r ON ur.rol_id = r.rol_id 
                                          JOIN Docente d ON d.usuario_id = ur.usuario_id 
                                          WHERE d.docente_id = ?");
    
            $stmt->bind_param("i", $docente_id);
            $stmt->execute();
            $result = $stmt->get_result();
    
            // Obtener los roles actuales del docente
            $roles_docente = [];
            while ($row = $result->fetch_assoc()) {
                $roles_docente[] = $row['nombre'];
            }
    
            // Verificar si el docente ya tiene un rol incompatible
            if (in_array('Coordinador', $roles_docente) && in_array('Jefe de Departamento', $roles)) {
                throw new Exception("El docente ya es Coordinador, no puede ser Jefe de Departamento.");
            }
    
            if (in_array('Jefe de Departamento', $roles_docente) && in_array('Coordinador', $roles)) {
                throw new Exception("El docente ya es Jefe de Departamento, no puede ser Coordinador.");
            }
    
            // Asignar los roles
            $stmt = $this->conn->prepare("INSERT INTO UsuarioRol (usuario_id, rol_id) VALUES (?, ?)");
            
            foreach ($roles as $rol) {
                $rol_id = $this->obtenerRolIdPorNombre($rol); // Método para obtener el rol_id por nombre
                if (!$rol_id) {
                    throw new Exception("Rol '$rol' no encontrado.");
                }
    
                $stmt->bind_param("ii", $docente_id, $rol_id);
                $stmt->execute();
    
                // Asignar jefe o coordinador según corresponda
                if ($rol === 'Jefe de Departamento' && $departamento_id) {
                    $stmt2 = $this->conn->prepare("UPDATE Departamento SET jefe_docente_id = ? WHERE dept_id = ?");
                    $stmt2->bind_param("ii", $docente_id, $departamento_id);
                    $stmt2->execute();
                } elseif ($rol === 'Coordinador' && $carrera_id) {
                    $stmt2 = $this->conn->prepare("UPDATE Carrera SET coordinador_docente_id = ? WHERE carrera_id = ?");
                    $stmt2->bind_param("ii", $docente_id, $carrera_id);
                    $stmt2->execute();
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
     * Obtiene el rol_id correspondiente a un nombre de rol.
     *
     * @param string $rol_nombre El nombre del rol.
     * @return int|null El rol_id si se encuentra, o null si no se encuentra.
     * @throws Exception Si ocurre un error.
     */
    private function obtenerRolIdPorNombre($rol_nombre)
    {
        $query = "SELECT rol_id FROM Rol WHERE nombre = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
        $stmt->bind_param("s", $rol_nombre);
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando la consulta: " . $stmt->error);
        }
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['rol_id'];
        }
        return null;  // Retorna null si no se encuentra el rol
    }

    /**
     * Quita roles de un docente y actualiza el departamento o la carrera según corresponda.
     *
     * @param int $docente_id ID del docente.
     * @param array $roles Array de nombres de roles a quitar.
     * @param int $departamento_id (Opcional) ID del departamento para Jefe de Departamento.
     * @param int $carrera_id (Opcional) ID de la carrera para Coordinador.
     * @return bool True si se quitaron correctamente.
     * @throws Exception Si ocurre un error.
     */
    public function quitarRolesDocente($docente_id, $roles, $departamento_id = null, $carrera_id = null) {
        // Comienza la transacción
        $this->conn->begin_transaction();
        try {
            // Validar si el docente tiene los roles que se van a quitar
            $stmt = $this->conn->prepare("SELECT r.nombre FROM UsuarioRol ur 
                                        JOIN Rol r ON ur.rol_id = r.rol_id 
                                        JOIN Docente d ON d.usuario_id = ur.usuario_id 
                                        WHERE d.docente_id = ?");
            $stmt->bind_param("i", $docente_id);
            $stmt->execute();
            $result = $stmt->get_result();

            // Obtener los roles actuales del docente
            $roles_docente = [];
            while ($row = $result->fetch_assoc()) {
                $roles_docente[] = $row['nombre'];
            }

            // Verificar si el docente tiene algún rol que queremos quitar
            foreach ($roles as $rol) {
                if (!in_array($rol, $roles_docente)) {
                    throw new Exception("El docente no tiene el rol '$rol' asignado.");
                }
            }

            // Quitar los roles del docente
            $stmt = $this->conn->prepare("DELETE FROM UsuarioRol WHERE usuario_id = ? AND rol_id = ?");
            
            foreach ($roles as $rol) {
                $rol_id = $this->obtenerRolIdPorNombre($rol); // Método para obtener el rol_id por nombre
                if (!$rol_id) {
                    throw new Exception("Rol '$rol' no encontrado.");
                }

                $stmt->bind_param("ii", $docente_id, $rol_id);
                $stmt->execute();
            }

            // Si el rol es 'Jefe de Departamento', actualizar el Departamento
            if (in_array('Jefe de Departamento', $roles)) {
                if ($departamento_id) {
                    $stmt2 = $this->conn->prepare("UPDATE Departamento SET jefe_docente_id = NULL WHERE dept_id = ?");
                    $stmt2->bind_param("i", $departamento_id);
                    $stmt2->execute();
                }
            }

            // Si el rol es 'Coordinador', actualizar la Carrera
            if (in_array('Coordinador', $roles)) {
                if ($carrera_id) {
                    $stmt2 = $this->conn->prepare("UPDATE Carrera SET coordinador_docente_id = NULL WHERE carrera_id = ?");
                    $stmt2->bind_param("i", $carrera_id);
                    $stmt2->execute();
                }
            }

            // Cerrar las declaraciones y confirmar la transacción
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
