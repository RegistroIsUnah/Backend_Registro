<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Clase Docente
 * 
 * Clase para manejar la interacción con la tabla `Clase` en la base de datos.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class Clase {
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;
    
    /**
     * Constructor de la clase Clase.
     *
     * Establece la conexión con la base de datos.
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Obtiene las clases de un departamento.
     *
     * @param int $dept_id ID del departamento.
     * @return array Lista de clases.
     * @throws Exception Si ocurre un error en la consulta.
     */
    public function obtenerClasesPorDepartamento($dept_id) {
        $query = "SELECT clase_id, codigo, nombre, creditos, tiene_laboratorio FROM Clase WHERE dept_id = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
        $stmt->bind_param("i", $dept_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $clases = [];
        while ($row = $result->fetch_assoc()) {
            $clases[] = $row;
        }
        $stmt->close();
        return $clases;
    }

    /**
     * Obtiene la lista de clases matriculables para un estudiante.
     *
     * Se listan las clases que:
     * - Pertenecen al departamento (c.dept_id = ?).
     * - Están asociadas a alguna de las carreras del estudiante (vía ClaseCarrera y EstudianteCarrera).
     * - Y, si tienen requisito (existe en ClaseRequisito), el estudiante ha aprobado alguna sección de esa clase.
     *
     * @param int $departamento_id ID del departamento.
     * @param int $estudiante_id ID del estudiante.
     * @return array Lista de clases matriculables.
     * @throws Exception Si ocurre un error en la consulta.
     */
    public function obtenerClasesMatriculables($departamento_id, $estudiante_id) {
        $sql = "
            SELECT DISTINCT c.clase_id, c.codigo, c.nombre, c.creditos, c.tiene_laboratorio
            FROM Clase c
            INNER JOIN ClaseCarrera cc ON c.clase_id = cc.clase_id
            WHERE c.dept_id = ?
              AND cc.carrera_id IN (
                  SELECT carrera_id FROM EstudianteCarrera WHERE estudiante_id = ?
              )
              AND (
                NOT EXISTS (
                  SELECT 1 FROM ClaseRequisito cr WHERE cr.clase_id = c.clase_id
                )
                OR EXISTS (
                  SELECT 1
                  FROM HistorialEstudiante he 
                  INNER JOIN Seccion s ON he.seccion_id = s.seccion_id
                  WHERE he.estudiante_id = ? 
                    AND s.clase_id = c.clase_id
                    AND he.estado_curso = 'APROBADA'
                )
              )
        ";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
        // Se requieren 3 parámetros: departamento_id y dos veces estudiante_id.
        $stmt->bind_param("iii", $departamento_id, $estudiante_id, $estudiante_id);
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando la consulta: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $clases = [];
        while ($row = $result->fetch_assoc()) {
            $clases[] = $row;
        }
        $stmt->close();
        return $clases;
    }

    /**
     * Obtiene la lista de laboratorios asociados a una clase.
     *
     * @param int $clase_id ID de la clase.
     * @return array Lista de laboratorios con todos sus detalles.
     * @throws Exception Si ocurre un error en la consulta.
     */
    public function obtenerLaboratoriosPorClase($clase_id) {
        $sql = "SELECT * FROM Laboratorio WHERE clase_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
        $stmt->bind_param("i", $clase_id);
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando la consulta: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $laboratorios = [];
        while ($row = $result->fetch_assoc()) {
            $laboratorios[] = $row;
        }
        $stmt->close();
        return $laboratorios;
    }
}
?>
