<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Clase Docente
 *
 * Maneja operaciones relacionadas con el docente.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class Docente {
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;

    /**
     * Constructor de la clase Docente.
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /** EN DESHUSO
     * Asigna un usuario a un docente utilizando el procedimiento almacenado SP_asignarUsuarioDocente.
     *
     * @param int $docente_id
     * @param string $username
     * @param string $password
     * @return array Resultado con el mensaje de éxito.
     * @throws Exception Si ocurre un error.
     */
    public function asignarUsuario($docente_id, $username, $password) {
        $stmt = $this->conn->prepare("CALL SP_asignarUsuarioDocente(?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
        $stmt->bind_param("iss", $docente_id, $username, $password);
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando la consulta: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $mensaje = null;
        if ($result) {
            $row = $result->fetch_assoc();
            $mensaje = $row['mensaje'] ?? null;
            $result->free();
        }
        $stmt->close();
        if (!$mensaje) {
            throw new Exception("No se obtuvo respuesta del procedimiento");
        }
        return ['mensaje' => $mensaje];
    }
}
?>
