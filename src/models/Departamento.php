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

    /**
     * Obtiene clases y secciones activas por departamento, año y período.
     * @author Jose Vargas
     * @param int $deptId ID del departamento.
     * @param int $anio Año académico.
     * @param int $periodoId ID del período académico.
     */
    public function obtenerClasesYSecciones($deptId, $anio, $periodoId) {
        $sql = "
            SELECT 
                c.clase_id,
                c.nombre AS nombre_clase,
                c.codigo AS codigo_clase,
                s.seccion_id,
                s.hora_inicio,
                s.hora_fin,
                a.nombre AS aula,
                CONCAT(d.nombre, ' ', d.apellido) AS docente,
                p.anio,
                np.nombre AS periodo,
                es.nombre AS estado_seccion
            FROM Clase c
            INNER JOIN Seccion s ON c.clase_id = s.clase_id
            INNER JOIN PeriodoAcademico p ON s.periodo_academico_id = p.periodo_academico_id
            INNER JOIN NumeroPeriodo np ON p.numero_periodo_id = np.numero_periodo_id
            INNER JOIN Aula a ON s.aula_id = a.aula_id
            INNER JOIN Docente d ON s.docente_id = d.docente_id
            INNER JOIN EstadoSeccion es ON s.estado_seccion_id = es.estado_seccion_id
            WHERE 
                c.dept_id = ?
                AND p.anio = ?
                AND np.numero_periodo_id = ?
                AND es.nombre = 'ACTIVA'
            ORDER BY c.clase_id, s.hora_inicio";
    
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('iii', $deptId, $anio, $periodoId);
            $stmt->execute();
            
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return []; // Mejor devolver array vacío que lanzar excepción
            }
            
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            
            // Agrupar por clase
            $clases = [];
            foreach ($data as $row) {
                $claseId = $row['clase_id'];
                if (!isset($clases[$claseId])) {
                    $clases[$claseId] = [
                        'clase_id' => $claseId,
                        'nombre_clase' => $row['nombre_clase'],
                        'secciones' => []
                    ];
                }
                
                $clases[$claseId]['secciones'][] = [
                    'seccion_id' => $row['seccion_id'],
                    'codigo' => $row['codigo_clase'],
                    'hora_inicio' => $row['hora_inicio'],
                    'hora_fin' => $row['hora_fin'],
                    'aula' => $row['aula'],
                    'docente' => $row['docente']
                ];
            }
            
        return array_values($clases); // Convertir a array indexado
    }


}
?>
