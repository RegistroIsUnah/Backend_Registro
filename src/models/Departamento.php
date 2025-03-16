<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Clase Departamento
 *
 * Maneja la interacción con la tabla Departamento de la base de datos.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class Departamento {
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;
    
    /**
     * Constructor de la clase Departamento.
     *
     * Establece la conexión con la base de datos.
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Obtiene la lista de todos los departamentos.
     *
     * @return array Lista de departamentos.
     * @throws Exception Si ocurre un error en la consulta.
     */
    public function obtenerDepartamentos() {
        $sql = "SELECT dept_id, facultad_id, nombre, jefe_docente_id FROM Departamento";
        $result = $this->conn->query($sql);
        if (!$result) {
            throw new Exception("Error en la consulta: " . $this->conn->error);
        }
        
        $departamentos = [];
        while ($row = $result->fetch_assoc()) {
            $departamentos[] = $row;
        }
        return $departamentos;
    }

    /*
     Obtiene clases y secciones activas por departamento, año y período
     */
    public function obtenerClasesYSecciones($deptId, $anio, $periodo) {
        $sql = "
            SELECT 
                c.clase_id,
                c.nombre AS nombre_clase,
                s.seccion_id,
                s.codigo AS codigo_seccion,
                s.hora_inicio,
                s.hora_fin,
                a.nombre AS aula,
                d.nombre AS docente,
                p.anio,
                p.numero_periodo
            FROM Clase c
            INNER JOIN Seccion s ON c.clase_id = s.clase_id
            INNER JOIN PeriodoAcademico p ON s.periodo_academico_id = p.periodo_academico_id
            INNER JOIN Aula a ON s.aula_id = a.aula_id
            INNER JOIN Docente d ON s.docente_id = d.docente_id
            WHERE c.dept_id = ?
            AND p.anio = ?
            AND p.numero_periodo = ?
            AND s.estado = 'ACTIVA'
            ORDER BY c.nombre, s.codigo";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iis', $deptId, $anio, $periodo);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
