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
        // Preparar la llamada al procedimiento almacenado SP_matricular_estudiante
        $stmt = $this->conn->prepare("CALL SP_matricular_estudiante(?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception('Error preparando la consulta: ' . $this->conn->error);
        }

        // Vincular los parámetros
        $stmt->bind_param("iisi", $estudiante_id, $seccion_id, $tipo_proceso, $laboratorio_id);

        // Ejecutar el procedimiento almacenado
        if (!$stmt->execute()) {
            throw new Exception('Error ejecutando el procedimiento: ' . $stmt->error);
        }

        // Obtener el resultado
        $result = $stmt->get_result();
        if ($result) {
            // Recuperamos los datos de la matrícula
            $row = $result->fetch_assoc();
            $stmt->close();
            return $row;
        } else {
            $stmt->close();
            throw new Exception('No se obtuvo respuesta del procedimiento almacenado');
        }
    }

    /**
     * Obtiene la lista de espera de una sección basada en el estado de la matrícula.
     *
     * @param int $seccionId ID de la sección.
     * @return array Lista de estudiantes en espera de la sección.
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
            INNER JOIN EstadoMatricula em ON m.estado_matricula_id = em.estado_matricula_id
            WHERE m.seccion_id = ?
              AND em.nombre = 'EN_ESPERA'  -- Filtrar por estado 'EN_ESPERA'
            ORDER BY m.orden_inscripcion
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }

        $stmt->bind_param('i', $seccionId);
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando la consulta: " . $stmt->error);
        }

        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

       /**
     * Matricula un estudiante en una sección en Adiciones y Cancelaciones.
     *
     * @param int $estudiante_id ID del estudiante.
     * @param int $seccion_id ID de la sección principal.
     * @param string $tipo_proceso Tipo de proceso (debe ser 'ADICIONES_CANCELACIONES').
     * @param int $lab_seccion_id ID del laboratorio seleccionado (0 o NULL si no aplica).
     * @return array Arreglo asociativo con los datos resultantes de la matrícula (por ejemplo, matricula_id, estado, orden_inscripcion).
     * @throws Exception Si ocurre un error durante la ejecución.
     */
    public function matricularEstudianteAdiciones($estudiante_id, $seccion_id, $tipo_proceso, $lab_seccion_id) {
        // Verificar que el tipo de proceso sea 'ADICIONES_CANCELACIONES'
        if (strtoupper($tipo_proceso) !== 'ADICIONES_CANCELACIONES') {
            throw new Exception("El tipo de proceso debe ser 'ADICIONES_CANCELACIONES'");
        }

        // Preparar la llamada al procedimiento almacenado SP_matricular_estudiante_adiciones_cancelaciones
        $stmt = $this->conn->prepare("CALL SP_matricular_estudiante_adiciones_cancelaciones(?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }

        // Vincular los parámetros (i = entero, s = string)
        if (!$stmt->bind_param("iisi", $estudiante_id, $seccion_id, $tipo_proceso, $lab_seccion_id)) {
            throw new Exception("Error vinculando parámetros: " . $stmt->error);
        }

        // Ejecutar el procedimiento almacenado
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando el procedimiento: " . $stmt->error);
        }

        // Obtener el resultado
        $result = $stmt->get_result();
        $data = [];
        if ($result) {
            $data = $result->fetch_assoc();
            $result->free();
        }

        $stmt->close();

        // Si no se obtuvo respuesta, lanzar una excepción
        if (empty($data)) {
            throw new Exception("No se obtuvo respuesta del procedimiento");
        }

        return $data;
    }
}
?>
