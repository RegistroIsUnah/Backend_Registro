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
    public function obtenerListaEsperaPorSeccion($seccionId) {
        $sql = "
            SELECT 
                m.seccion_id,
                e.estudiante_id,
                e.nombre,
                e.apellido,
                e.correo_personal,
                m.orden_inscripcion
            FROM Matricula m
            INNER JOIN Estudiante e ON m.estudiante_id = e.estudiante_id
            WHERE m.seccion_id = ?
            AND m.estado = 'EN_ESPERA'
            ORDER BY m.orden_inscripcion";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $seccionId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
