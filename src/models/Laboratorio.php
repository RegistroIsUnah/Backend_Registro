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
                    l.codigo_laboratorio,
                    DATE_FORMAT(l.hora_inicio, '%H%i') AS laboratorio_codigo,
                    l.hora_inicio,
                    l.hora_fin,
                    l.motivo_cancelacion,
                    l.cupos - IFNULL(
                        (SELECT COUNT(*) 
                         FROM Matricula m
                         WHERE m.laboratorio_id = l.laboratorio_id), 0) AS cupos_disponibles,
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
                         a.nombre, e.nombre";

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
     * Registra un laboratorio en la base de datos.
     *
     * @param int $clase_id ID de la clase asociada al laboratorio
     * @param string $codigo_laboratorio Código del laboratorio basado en la hora de inicio
     * @param int $periodo_academico_id ID del periodo académico
     * @param string $hora_inicio Hora de inicio del laboratorio
     * @param string $hora_fin Hora de fin del laboratorio
     * @param int $aula_id ID del aula donde se llevará a cabo el laboratorio
     * @param int $cupos Número de cupos disponibles para el laboratorio
     * @return int ID del laboratorio creado
     * @throws Exception Si ya existe un laboratorio en el mismo horario o aula
     */
    public function crearLaboratorio($clase_id, $codigo_laboratorio, $periodo_academico_id, $hora_inicio, $hora_fin, $aula_id, $cupos) {
        // Verificar si ya existe un laboratorio en el mismo horario y aula
        $sqlCheck = "SELECT COUNT(*) FROM Laboratorio 
                    WHERE aula_id = ? 
                    AND periodo_academico_id = ? 
                    AND ((hora_inicio BETWEEN ? AND ?) OR (hora_fin BETWEEN ? AND ?))";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bind_param("iissss", $aula_id, $periodo_academico_id, $hora_inicio, $hora_fin, $hora_inicio, $hora_fin);
        $stmtCheck->execute();
        $result = $stmtCheck->get_result();
        $row = $result->fetch_row();
        
        if ($row[0] > 0) {
            throw new Exception("Ya existe un laboratorio en el mismo horario en esta aula.");
        }

        // Verificar si existe un laboratorio para la misma clase en el mismo horario
        $sqlCheckClase = "SELECT COUNT(*) FROM Laboratorio
                        WHERE clase_id = ? AND periodo_academico_id = ?
                        AND ((hora_inicio BETWEEN ? AND ?) OR (hora_fin BETWEEN ? AND ?))";
        $stmtCheckClase = $this->conn->prepare($sqlCheckClase);
        $stmtCheckClase->bind_param("iissss", $clase_id, $periodo_academico_id, $hora_inicio, $hora_fin, $hora_inicio, $hora_fin);
        $stmtCheckClase->execute();
        $resultClase = $stmtCheckClase->get_result();
        $rowClase = $resultClase->fetch_row();

        if ($rowClase[0] > 0) {
            throw new Exception("Ya existe un laboratorio para esta clase en el mismo horario.");
        }

        // Insertar el laboratorio en la base de datos
        $estado_seccion_id = 1; // Estado "ACTIVO"
        $sql = "INSERT INTO Laboratorio (clase_id, codigo_laboratorio, periodo_academico_id, hora_inicio, hora_fin, aula_id, estado_seccion_id, cupos) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issssiii", $clase_id, $codigo_laboratorio, $periodo_academico_id, $hora_inicio, $hora_fin, $aula_id, $estado_seccion_id, $cupos);
        $stmt->execute();
        
        return $stmt->insert_id;
    }

    /**
     * Modifica los detalles de un laboratorio, incluyendo la actualización de la hora de inicio y el código del laboratorio.
     *
     * @param int $laboratorio_id ID del laboratorio
     * @param int|null $clase_id ID de la clase asociada al laboratorio (opcional)
     * @param string|null $codigo_laboratorio Código del laboratorio basado en la hora de inicio (opcional)
     * @param int|null $periodo_academico_id ID del periodo académico (opcional)
     * @param string|null $hora_inicio Hora de inicio del laboratorio (opcional)
     * @param string|null $hora_fin Hora de fin del laboratorio (opcional)
     * @param int|null $aula_id ID del aula donde se llevará a cabo el laboratorio (opcional)
     * @param int|null $cupos Número de cupos disponibles para el laboratorio (opcional)
     * @return void
     * @throws Exception Si ya existe un laboratorio en el mismo horario o aula
     */
    public function modificarLaboratorio($laboratorio_id, $clase_id = null, $codigo_laboratorio = null, $periodo_academico_id = null, $hora_inicio = null, $hora_fin = null, $aula_id = null, $cupos = null) {
        // Si se modifica la hora de inicio, se debe actualizar el código del laboratorio
        if ($hora_inicio !== null) {
            $codigo_laboratorio = date('Hi', strtotime($hora_inicio)); // Generar código laboratorio basado en la hora
        }

        // Verificar si ya existe un laboratorio en el mismo horario y aula
        if ($hora_inicio !== null && $aula_id !== null) {
            $sqlCheck = "SELECT COUNT(*) FROM Laboratorio 
                        WHERE aula_id = ? 
                        AND laboratorio_id != ? 
                        AND ((hora_inicio BETWEEN ? AND ?) OR (hora_fin BETWEEN ? AND ?))";
            $stmtCheck = $this->conn->prepare($sqlCheck);
            $stmtCheck->bind_param("iissss", $aula_id, $laboratorio_id, $hora_inicio, $hora_fin, $hora_inicio, $hora_fin);
            $stmtCheck->execute();
            $result = $stmtCheck->get_result();
            $row = $result->fetch_row();
            
            if ($row[0] > 0) {
                throw new Exception("Ya existe un laboratorio en el mismo horario en esta aula.");
            }
        }

        // Verificar si existe un laboratorio para la misma clase en el mismo horario
        if ($hora_inicio !== null && $clase_id !== null) {
            $sqlCheckClase = "SELECT COUNT(*) FROM Laboratorio
                            WHERE clase_id = ? 
                            AND laboratorio_id != ?
                            AND ((hora_inicio BETWEEN ? AND ?) OR (hora_fin BETWEEN ? AND ?))";
            $stmtCheckClase = $this->conn->prepare($sqlCheckClase);
            $stmtCheckClase->bind_param("iissss", $clase_id, $laboratorio_id, $hora_inicio, $hora_fin, $hora_inicio, $hora_fin);
            $stmtCheckClase->execute();
            $resultClase = $stmtCheckClase->get_result();
            $rowClase = $resultClase->fetch_row();

            if ($rowClase[0] > 0) {
                throw new Exception("Ya existe un laboratorio para esta clase en el mismo horario.");
            }
        }

        // Construir la consulta de actualización
        $sql = "UPDATE Laboratorio SET ";
        $params = [];
        $paramTypes = "";

        // Solo actualizamos los campos proporcionados
        if ($clase_id !== null) {
            $sql .= "clase_id = ?, ";
            $params[] = $clase_id;
            $paramTypes .= "i";
        }
        if ($codigo_laboratorio !== null) {
            $sql .= "codigo_laboratorio = ?, ";
            $params[] = $codigo_laboratorio;
            $paramTypes .= "s";
        }
        if ($periodo_academico_id !== null) {
            $sql .= "periodo_academico_id = ?, ";
            $params[] = $periodo_academico_id;
            $paramTypes .= "i";
        }
        if ($hora_inicio !== null) {
            $sql .= "hora_inicio = ?, ";
            $params[] = $hora_inicio;
            $paramTypes .= "s";
        }
        if ($hora_fin !== null) {
            $sql .= "hora_fin = ?, ";
            $params[] = $hora_fin;
            $paramTypes .= "s";
        }
        if ($aula_id !== null) {
            $sql .= "aula_id = ?, ";
            $params[] = $aula_id;
            $paramTypes .= "i";
        }
        if ($cupos !== null) {
            $sql .= "cupos = ?, ";
            $params[] = $cupos;
            $paramTypes .= "i";
        }

        // Eliminar la coma final
        $sql = rtrim($sql, ", ");

        // Condición para el laboratorio específico
        $sql .= " WHERE laboratorio_id = ?";

        // Añadir el laboratorio_id a los parámetros
        $params[] = $laboratorio_id;
        $paramTypes .= "i";

        // Preparar y ejecutar la consulta
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($paramTypes, ...$params);
        $stmt->execute();
    }

}
?>