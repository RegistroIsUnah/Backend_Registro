<?php
require_once __DIR__ . '/../../config/DataBase.php';


/**
 * Maneja la interacción para conseguir las listas de espera
 *
 * @package Models
 * @author Jose Vargas
 * @version 1.0
 * 
 */



class ListasEspera {
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;

    /**
     * Constructor de la clase Lista de Espera.
     *
     * Establece la conexión con la base de datos.
     */
    public function __construct() {
        $db = new DataBase();
        $this->conn = $db->getConnection();
    }


    /**
     * Crea las listas de espera de todas las clases de un departamento
     *
     * Si la fecha de fin ya pasó al momento de la creación, se inserta con estado 'INACTIVO';
     * de lo contrario, se inserta como 'ACTIVO'.
     *
     * @param int $departamentoId ID del departamento.
     * @return array Lista de espera de las clases del departamento.
     */
    public function obtenerListasEspera($departamentoId) {
        $sql = "
            SELECT 
                c.nombre AS clase,
                s.seccion_id,
                d.nombre AS departamento,
                e.estudiante_id,
                e.correo_personal,
                e.nombre,
                e.apellido,
                m.fecha AS fecha_solicitud
            FROM Departamento d
            INNER JOIN Clase c ON d.dept_id = c.dept_id
            INNER JOIN Seccion s ON c.clase_id = s.clase_id
            INNER JOIN Matricula m ON s.seccion_id = m.seccion_id
            INNER JOIN Estudiante e ON m.estudiante_id = e.estudiante_id
            WHERE d.dept_id = ?
            AND m.estado = 'EN_ESPERA'
            ORDER BY s.seccion_id, m.fecha";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $departamentoId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
