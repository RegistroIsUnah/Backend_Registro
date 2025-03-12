<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Clase Seccion
 *
 * Maneja la creación de secciones mediante el procedimiento almacenado SP_crearSeccion.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class Seccion {
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;

    /**
     * Constructor de la clase Seccion.
     *
     * Establece la conexión con la base de datos.
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Crea una sección utilizando el procedimiento almacenado SP_crearSeccion.
     *
     * @param int    $clase_id             ID de la clase.
     * @param int    $docente_id           ID del docente.
     * @param int    $periodo_academico_id ID del período académico.
     * @param int    $aula_id              ID del aula.
     * @param string $hora_inicio          Hora de inicio (formato "HH:MM:SS").
     * @param string $hora_fin             Hora de fin (formato "HH:MM:SS").
     * @param int    $cupos                Número de cupos disponibles.
     * @param string $dias                 Cadena con los días separados por comas (ej: "Lunes,Miércoles").
     * @return int ID de la sección creada.
     * @throws Exception Si ocurre algún error durante la creación.
     */
    public function crearSeccion($clase_id, $docente_id, $periodo_academico_id, $aula_id, $hora_inicio, $hora_fin, $cupos, $dias) {
        // Preparamos la llamada al SP
        $stmt = $this->conn->prepare("CALL SP_crearSeccion(?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
        // Bind de parámetros:
        // p_clase_id (i), p_docente_id (i), p_periodo_academico_id (i), p_aula_id (i),
        // p_hora_inicio (s), p_hora_fin (s), p_cupos (i), p_dias (s)
        $bind = $stmt->bind_param("iiiissis", $clase_id, $docente_id, $periodo_academico_id, $aula_id, $hora_inicio, $hora_fin, $cupos, $dias);
        if (!$bind) {
            throw new Exception("Error vinculando parámetros: " . $stmt->error);
        }
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando la consulta: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $seccion_id = null;
        if ($result) {
            $row = $result->fetch_assoc();
            $seccion_id = $row['seccion_id'] ?? null;
            $result->free();
        }
        $stmt->close();
        if (!$seccion_id) {
            throw new Exception("No se pudo crear la sección");
        }
        return $seccion_id;
    }

    /**
     * Modifica una sección utilizando el procedimiento almacenado SP_modificar_seccion.
     *
     * El SP actualiza los campos de la sección (docente, aula, estado y motivo de cancelación).
     * Si se intenta cancelar sin proporcionar un motivo, se lanzará un SIGNAL y el error se capturará.
     *
     * @param int $seccion_id ID de la sección a modificar.
     * @param int|null $docente_id Nuevo ID de docente (o NULL para no modificar).
     * @param int|null $aula_id Nuevo ID de aula (o NULL para no modificar).
     * @param string|null $estado Nuevo estado ('ACTIVA' o 'CANCELADA') o NULL para no modificar.
     * @param string|null $motivo_cancelacion Motivo de cancelación (requerido si estado es 'CANCELADA').
     * @return string Mensaje de éxito.
     * @throws Exception Si ocurre un error durante la modificación.
     */
    public function modificarSeccion($seccion_id, $docente_id, $aula_id, $estado, $motivo_cancelacion) {
        $stmt = $this->conn->prepare("CALL SP_modificar_seccion(?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
        // Se utiliza "iiiss": 4 enteros, 1 string.
        $stmt->bind_param("iiiss", $seccion_id, $docente_id, $aula_id, $estado, $motivo_cancelacion);
        if (!$stmt->execute()) {
            // Captura el error, que incluirá los mensajes lanzados mediante SIGNAL.
            throw new Exception("Error ejecutando la consulta: " . $stmt->error);
        }
        $stmt->close();
        return "Sección modificada exitosamente";
    }
}
?>
