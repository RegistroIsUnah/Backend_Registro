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
     * @param int    $clase_id
     * @param int    $docente_id
     * @param int    $periodo_academico_id
     * @param int    $aula_id
     * @param string $hora_inicio
     * @param string $hora_fin
     * @param int    $cupos
     * @param string $dias        Cadena separada por comas (ej: "Lunes,Martes").
     * @param string $video_url   Ruta del video (o NULL si no se envía).
     * @return int ID de la sección creada.
     * @throws Exception Si ocurre un error durante la creación.
     */
    public function crearSeccion($clase_id, $docente_id, $periodo_academico_id, $aula_id, $hora_inicio, $hora_fin, $cupos, $dias, $video_url) {
        $stmt = $this->conn->prepare("CALL SP_crearSeccion(?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
        // 8 primeros parámetros + 1 para video_url = 9 marcadores
        $bind = $stmt->bind_param("iiiississ", 
            $clase_id,
            $docente_id,
            $periodo_academico_id,
            $aula_id,
            $hora_inicio,
            $hora_fin,
            $cupos,
            $dias,
            $video_url
        );
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
     * El SP actualiza los campos de la sección (docente, aula, estado, motivo de cancelación y cupos).
     * Si se intenta cancelar sin proporcionar un motivo, se lanzará un SIGNAL y el error se capturará.
     *
     * @param int $seccion_id ID de la sección a modificar.
     * @param int|null $docente_id Nuevo ID de docente (o NULL para no modificar).
     * @param int|null $aula_id Nuevo ID de aula (o NULL para no modificar).
     * @param string|null $estado Nuevo estado ('ACTIVA' o 'CANCELADA') o NULL para no modificar.
     * @param string|null $motivo_cancelacion Motivo de cancelación (requerido si estado es 'CANCELADA').
     * @param int|null $cupos Nuevo número de cupos (o NULL para no modificar).
     * @return string Mensaje de éxito.
     * @throws Exception Si ocurre un error durante la modificación.
     */
    public function modificarSeccion($seccion_id, $docente_id, $aula_id, $estado, $motivo_cancelacion, $cupos) {
        $stmt = $this->conn->prepare("CALL SP_modificar_seccion(?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
        // La cadena de tipos es "iiissi": 3 enteros, 2 strings, 1 entero.
        if (!$stmt->bind_param("iiissi", $seccion_id, $docente_id, $aula_id, $estado, $motivo_cancelacion, $cupos)) {
            throw new Exception("Error vinculando parámetros: " . $stmt->error);
        }
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando la consulta: " . $stmt->error);
        }
        $stmt->close();
        return "Sección modificada exitosamente";
    }

}
?>
