<?php
require_once __DIR__ . '/../modules/config/DataBase.php';


/**
 * Clase Laboratorio
 *
 * Maneja operaciones relacionadas con los laboratorios.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class Laboratorio {
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;

    /**
     * Constructor de la clase Laboratorio.
     */
    public function __construct() {
        // Aquí se maneja la conexión a la base de datos directamente en el modelo.
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener los detalles de un laboratorio con cupos disponibles.
     *
     * @param int $clase_id ID de la clase
     * @return array Detalles del laboratorio
     */
    public function obtenerLaboratorios($clase_id) {
        $sql = "SELECT
                    l.laboratorio_id,
                    l.codigo_laboratorio,
                    DATE_FORMAT(l.hora_inicio, '%H%i') AS laboratorio_codigo,
                    l.hora_inicio,
                    l.hora_fin,
                    l.motivo_cancelacion,
                    l.cupos - IFNULL(
                        (SELECT COUNT(*) 
                         FROM Matricula m
                         WHERE m.laboratorio_id = l.laboratorio_id), 0) AS cupos_disponibles,
                    a.nombre AS aula_nombre,
                    e.nombre AS edificio_nombre,
                    GROUP_CONCAT(ds.nombre ORDER BY ds.dia_id ASC) AS dias_laboratorio
                FROM Laboratorio l
                LEFT JOIN Aula a ON l.aula_id = a.aula_id
                LEFT JOIN Edificio e ON a.edificio_id = e.edificio_id
                LEFT JOIN EstadoSeccion es ON l.estado_seccion_id = es.estado_seccion_id
                LEFT JOIN SeccionDia sd ON l.laboratorio_id = sd.seccion_id
                LEFT JOIN DiaSemana ds ON sd.dia_id = ds.dia_id
                WHERE es.nombre = 'ACTIVA' 
                AND l.clase_id = ?
                GROUP BY l.laboratorio_id, l.hora_inicio, l.hora_fin, es.nombre, l.motivo_cancelacion, 
                         a.nombre, e.nombre";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $clase_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Obtenemos los resultados en un array
        $laboratorios = [];
        while ($row = $result->fetch_assoc()) {
            $laboratorios[] = $row;
        }

        return $laboratorios;
    }
}
?>