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

    /**
     * Obtiene todas las clases matriculadas por el estudiante.
     * 
     * @param int $estudiante_id El ID del estudiante.
     * @return array El resultado de la consulta con las clases matriculadas.
     */
    public function obtenerClasesMatriculadas($estudiante_id) {
        $sql = "
            SELECT
                c.codigo AS codigo,
                c.nombre AS asignatura,
                DATE_FORMAT(se.hora_inicio, '%H%i') AS seccion,
                se.hora_inicio AS hora_inicio,
                se.hora_fin AS hora_fin,
                GROUP_CONCAT(ds.nombre ORDER BY ds.dia_id) AS dias_seccion,
                e.nombre AS edificio_nombre,
                a.nombre AS aula_nombre,
                c.creditos AS creditos
            FROM
                Matricula m
            JOIN
                Seccion se ON m.seccion_id = se.seccion_id
            JOIN
                Clase c ON se.clase_id = c.clase_id
            JOIN
                Aula a ON se.aula_id = a.aula_id
            JOIN
                Edificio e ON a.edificio_id = e.edificio_id
            LEFT JOIN
                SeccionDia sd ON se.seccion_id = sd.seccion_id
            LEFT JOIN
                DiaSemana ds ON sd.dia_id = ds.dia_id
            WHERE
                m.estudiante_id = ? AND m.estado_matricula_id = (SELECT estado_matricula_id FROM EstadoMatricula WHERE nombre = 'MATRICULADO')
            GROUP BY
                se.seccion_id, c.clase_id, a.nombre, e.nombre
            ORDER BY
                se.seccion_id;
        ";

        // Preparar la consulta
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $estudiante_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Recoger todos los resultados
        $clasesMatriculadas = [];
        while ($row = $result->fetch_assoc()) {
            $clasesMatriculadas[] = $row;
        }

        // Cerrar la conexión
        $stmt->close();
        return $clasesMatriculadas;
    }

     /**
     * Obtiene todas las clases matriculadas en estado 'EN_ESPERA' por el estudiante.
     * 
     * @param int $estudiante_id El ID del estudiante.
     * @return array El resultado de la consulta con las clases matriculadas en estado 'EN_ESPERA'.
     */
    public function obtenerClasesEnEspera($estudiante_id) {
        $sql = "
            SELECT
                c.codigo AS codigo,
                c.nombre AS asignatura,
                DATE_FORMAT(se.hora_inicio, '%H%i') AS seccion,
                se.hora_inicio AS hora_inicio,
                se.hora_fin AS hora_fin,
                GROUP_CONCAT(ds.nombre ORDER BY ds.dia_id) AS dias_seccion,
                e.nombre AS edificio_nombre,
                a.nombre AS aula_nombre,
                c.creditos AS creditos
            FROM
                Matricula m
            JOIN
                Seccion se ON m.seccion_id = se.seccion_id
            JOIN
                Clase c ON se.clase_id = c.clase_id
            JOIN
                Aula a ON se.aula_id = a.aula_id
            JOIN
                Edificio e ON a.edificio_id = e.edificio_id
            LEFT JOIN
                SeccionDia sd ON se.seccion_id = sd.seccion_id
            LEFT JOIN
                DiaSemana ds ON sd.dia_id = ds.dia_id
            WHERE
                m.estudiante_id = ? AND m.estado_matricula_id = (SELECT estado_matricula_id FROM EstadoMatricula WHERE nombre = 'EN_ESPERA')
            GROUP BY
                se.seccion_id, c.clase_id, a.nombre, e.nombre
            ORDER BY
                se.seccion_id;
        ";

        // Preparar la consulta
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $estudiante_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Recoger todos los resultados
        $clasesEnEspera = [];
        while ($row = $result->fetch_assoc()) {
            $clasesEnEspera[] = $row;
        }

        // Cerrar la conexión
        $stmt->close();
        return $clasesEnEspera;
    }

       /**
     * Obtener los detalles de los laboratorios matriculados.
     *
     * @param int $estudiante_id ID del estudiante
     * @return array Detalles de los laboratorios matriculados
     */
    public function obtenerLaboratoriosMatriculados($estudiante_id) {
        $sql = "SELECT
                    c.codigo AS codigo,
                    c.nombre AS asignatura,
                    DATE_FORMAT(l.hora_inicio, '%H%i') AS laboratorio_codigo,
                    l.hora_inicio AS hora_inicio,
                    l.hora_fin AS hora_fin,
                    GROUP_CONCAT(ds.nombre ORDER BY ds.dia_id) AS dias_laboratorio,
                    e.nombre AS edificio_nombre,
                    a.nombre AS aula_nombre,
                    c.creditos AS creditos
                FROM
                    Matricula m
                JOIN
                    Laboratorio l ON m.laboratorio_id = l.laboratorio_id
                JOIN
                    Clase c ON l.clase_id = c.clase_id
                JOIN
                    Aula a ON l.aula_id = a.aula_id
                JOIN
                    Edificio e ON a.edificio_id = e.edificio_id
                LEFT JOIN
                    SeccionDia sd ON l.laboratorio_id = sd.seccion_id
                LEFT JOIN
                    DiaSemana ds ON sd.dia_id = ds.dia_id
                WHERE
                    m.estudiante_id = ? 
                    AND m.estado_matricula_id = (SELECT estado_matricula_id FROM EstadoMatricula WHERE nombre = 'MATRICULADO')
                GROUP BY
                    l.laboratorio_id, c.clase_id, a.nombre, e.nombre
                ORDER BY
                    l.laboratorio_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $estudiante_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $laboratorios = [];
        while ($row = $result->fetch_assoc()) {
            $laboratorios[] = $row;
        }

        return $laboratorios;
    }
}
?>
