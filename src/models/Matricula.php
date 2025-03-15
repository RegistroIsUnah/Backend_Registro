<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Modelo para Matrícula.
 *
 * Encapsula la lógica relacionada a matricula.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.1
 * 
 */
class Matricula {
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;
    
    /**
     * Constructor que establece la conexión a la base de datos.
     */
    public function __construct(){
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Llama al procedimiento almacenado SP_matricular_estudiante para matricular a un estudiante.
     *
     * @param int $estudiante_id ID del estudiante.
     * @param int $seccion_id ID de la sección principal.
     * @param string $tipo_proceso Tipo de proceso (ej. "MATRICULA").
     * @param int $laboratorio_id ID del laboratorio seleccionado (0 si no se seleccionó ninguno).
     * @return array Resultado de la matrícula (matricula_id, estado, orden_inscripcion).
     * @throws Exception Si ocurre un error en la preparación o ejecución del SP.
     */
    public function matricularEstudiante($estudiante_id, $seccion_id, $tipo_proceso, $laboratorio_id) {
        $stmt = $this->conn->prepare("CALL SP_matricular_estudiante(?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception('Error preparando la consulta: ' . $this->conn->error);
        }
        
        $stmt->bind_param("iiss", $estudiante_id, $seccion_id, $tipo_proceso, $laboratorio_id);
        
        if (!$stmt->execute()){
            throw new Exception('Error ejecutando el procedimiento: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if ($result){
            $row = $result->fetch_assoc();
            $stmt->close();
            return $row;
        } else {
            $stmt->close();
            throw new Exception('No se obtuvo respuesta del procedimiento almacenado');
        }
    }

   /**
     * Obtiene las listas de espera de todas las clases de un departamento
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
