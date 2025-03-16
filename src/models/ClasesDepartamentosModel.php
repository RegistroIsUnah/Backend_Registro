<?php
require_once __DIR__ . '/../../config/DataBase.php';

class ClasesDepartamentosModel {
    private $conn;

    public function __construct() {
        $db = new DataBase();
        $this->conn = $db->getConnection();
    }

    /**
     * Obtiene clases y secciones activas por departamento, año y período
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