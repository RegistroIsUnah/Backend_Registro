<?php
require_once __DIR__ . '/../modules/config/DataBase.php';


/**
 * Clase Laboratorio
 *
 * Maneja operaciones relacionadas con los laboratorios.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class Laboratorio {
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;

    /**
     * Constructor de la clase Laboratorio.
     */
    public function __construct() {
        // Aquí se maneja la conexión a la base de datos directamente en el modelo.
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener los detalles de un laboratorio con cupos disponibles.
     *
     * @param int $clase_id ID de la clase
     * @return array Detalles del laboratorio
     */
    public function obtenerLaboratorios($clase_id) {
        $sql = "SELECT
                    l.laboratorio_id,
                    DATE_FORMAT(l.hora_inicio, '%H%i') AS laboratorio_codigo,
                    l.hora_inicio,
                    l.hora_fin,
                    l.cupos - IFNULL(
                        (SELECT COUNT(*) 
                        FROM Matricula m
                        JOIN EstadoMatricula em ON m.estado_matricula_id = em.estado_matricula_id
                        WHERE m.laboratorio_id = l.laboratorio_id
                        AND em.nombre = 'Matriculado'), 0) AS cupos_disponibles,
                    a.nombre AS aula_nombre,
                    e.nombre AS edificio_nombre,
                    GROUP_CONCAT(ds.nombre ORDER BY ds.dia_id ASC) AS dias_laboratorio
                FROM Laboratorio l
                LEFT JOIN Aula a ON l.aula_id = a.aula_id
                LEFT JOIN Edificio e ON a.edificio_id = e.edificio_id
                LEFT JOIN EstadoSeccion es ON l.estado_seccion_id = es.estado_seccion_id
                LEFT JOIN SeccionDia sd ON l.laboratorio_id = sd.seccion_id
                LEFT JOIN DiaSemana ds ON sd.dia_id = ds.dia_id
                WHERE es.nombre = 'ACTIVA' 
                AND l.clase_id = ?
                GROUP BY l.laboratorio_id, l.hora_inicio, l.hora_fin, es.nombre, l.motivo_cancelacion, 
                        a.nombre, e.nombre
                                          ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $clase_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Obtenemos los resultados en un array
        $laboratorios = [];
        while ($row = $result->fetch_assoc()) {
            $laboratorios[] = $row;
        }

        return $laboratorios;
    }

     /**
     * Modifica un laboratorio existente
     *
     * @param int    $laboratorio_id      ID del laboratorio a modificar
     * @param int    $aula_id             ID del aula (opcional)
     * @param string $estado              Estado (ACTIVA/CANCELADA, opcional)
     * @param string $motivo_cancelacion  Motivo de cancelación (requerido si estado=CANCELADA)
     * @param int    $cupos               Número de cupos (opcional)
     * @param string $hora_inicio         Hora de inicio (HH:MM:SS, opcional)
     * @param string $hora_fin            Hora de fin (HH:MM:SS, opcional)
     * @param array  $dias                Array de IDs de días (opcional)
     *
     * @return bool True si la modificación fue exitosa
     * @throws Exception Si ocurre un error en la base de datos
     */
    public function modificarLaboratorio($laboratorio_id, $aula_id = null, $estado = null, 
                                      $motivo_cancelacion = null, $cupos = null, 
                                      $hora_inicio = null, $hora_fin = null, $dias = null) {
        // Convertir array de días a JSON
        $dias_json = $dias ? json_encode($dias) : null;
        
        $stmt = $this->conn->prepare("CALL SP_modificarLaboratorio(?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }

        // Tipos: i=entero, s=string
        if (!$stmt->bind_param("iisssiss", 
            $laboratorio_id, $aula_id, $estado, $motivo_cancelacion,
            $cupos, $hora_inicio, $hora_fin, $dias_json)) {
            throw new Exception("Error vinculando parámetros: " . $stmt->error);
        }

        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando la consulta: " . $stmt->error);
        }

        $stmt->close();
        return true;
    }

      /**
     * Crea un nuevo laboratorio en el sistema
     *
     * @param int    $clase_id             ID de la clase asociada
     * @param string $codigo_laboratorio   Código único del laboratorio
     * @param int    $periodo_academico_id ID del período académico
     * @param string $hora_inicio          Hora de inicio (formato HH:MM:SS)
     * @param string $hora_fin             Hora de fin (formato HH:MM:SS)
     * @param int    $aula_id              ID del aula asignada
     * @param int    $cupos                Número de cupos disponibles
     * @param array  $dias                 Array de IDs de días (ej: [1,3] para Lunes y Miércoles)
     * 
     * @return int ID del laboratorio creado
     * @throws Exception Si ocurre un error en la base de datos
     */
    public function crearLaboratorio($clase_id, $codigo_laboratorio, $periodo_academico_id, 
                                  $hora_inicio, $hora_fin, $aula_id, $cupos, $dias) {
        // Convertir array de días a JSON
        $dias_json = json_encode($dias);
        
        $stmt = $this->conn->prepare("CALL SP_crearLaboratorio(?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }

        if (!$stmt->bind_param("isisiiis", $clase_id, $codigo_laboratorio, $periodo_academico_id, 
                            $hora_inicio, $hora_fin, $aula_id, $cupos, $dias_json)) {
            throw new Exception("Error vinculando parámetros: " . $stmt->error);
        }

        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando la consulta: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row['laboratorio_id'];
    }
}
?>