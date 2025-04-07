<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Clase Rol
 *
 * Maneja operaciones relacionadas con el Rol.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class Rol
{
      /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;


    /**
     * Constructor de la clase Rol.
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtiene todos los roles de la base de datos.
     *
     * @return array Lista de roles con ID y nombre
     */
    public function obtenerRoles()
    {
        // Consulta para obtener todos los roles (ID y nombre)
        $query = "SELECT rol_id, nombre FROM Rol";

        // Preparara la consulta
        $stmt = $this->conn->prepare($query);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $roles = [];

            // Obtener los resultados
            while ($row = $result->fetch_assoc()) {
                $roles[] = [
                    'rol_id' => $row['rol_id'],
                    'nombre' => $row['nombre']
                ];
            }

            return $roles;
        } else {
            throw new Exception("Error al obtener los roles.");
        }
    }
}
?>