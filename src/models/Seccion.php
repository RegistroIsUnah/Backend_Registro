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
     * Actualiza los campos de docente, aula, estado, motivo de cancelación, cupos y video_url.
     *
     * @param int $seccion_id ID de la sección a modificar.
     * @param int|null $docente_id Nuevo ID de docente (o NULL para no modificar).
     * @param int|null $aula_id Nuevo ID de aula (o NULL para no modificar).
     * @param string|null $estado Nuevo estado ('ACTIVA' o 'CANCELADA') o NULL para no modificar.
     * @param string|null $motivo_cancelacion Motivo de cancelación (requerido si estado es 'CANCELADA').
     * @param int|null $cupos Nuevo número de cupos (o NULL para no modificar).
     * @param string|null $video_url Nueva URL del video (o NULL para no modificar).
     * @return string Mensaje de éxito.
     * @throws Exception Si ocurre un error durante la modificación.
     */
    public function modificarSeccion($seccion_id, $docente_id, $aula_id, $estado, $motivo_cancelacion, $cupos, $video_url) {
        $stmt = $this->conn->prepare("CALL SP_modificarSeccion(?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
        // "iiissi s": 3 enteros, 2 strings, 1 entero, 1 string
        if (!$stmt->bind_param("iiissis", $seccion_id, $docente_id, $aula_id, $estado, $motivo_cancelacion, $cupos, $video_url)) {
            throw new Exception("Error vinculando parámetros: " . $stmt->error);
        }
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando la consulta: " . $stmt->error);
        }
        $stmt->close();
        return "Sección modificada exitosamente";
    }

     /**
     * Obtiene las secciones de una clase con los detalles del docente, aula y edificio.
     *
     * @param int $clase_id ID de la clase.
     * @return array Lista de secciones con sus detalles.
     * @throws Exception Si ocurre un error en la consulta.
     */
    public function obtenerSeccionesPorClase($clase_id) {
        $query = "
            SELECT 
                s.seccion_id,
                s.hora_inicio,
                s.hora_fin,
                s.estado,
                s.video_url,
                s.motivo_cancelacion,
                s.cupos,
                d.nombre AS docente_nombre,
                d.apellido AS docente_apellido,
                a.nombre AS aula_nombre,
                e.nombre AS edificio_nombre
            FROM Seccion s
            LEFT JOIN Docente d ON s.docente_id = d.docente_id
            LEFT JOIN Aula a ON s.aula_id = a.aula_id
            LEFT JOIN Edificio e ON a.edificio_id = e.edificio_id
            WHERE s.clase_id = ?
        ";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
        $stmt->bind_param("i", $clase_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $secciones = [];
        while ($row = $result->fetch_assoc()) {
            $secciones[] = $row;
        }
        $stmt->close();
        return $secciones;
    }
}
?>
