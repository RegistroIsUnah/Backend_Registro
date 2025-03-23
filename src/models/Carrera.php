<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Clase Carrera
 *
 * Maneja la interacción con la tabla `Carrera` y su relación con CentroCarrera en la base de datos.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class Carrera {
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;

    /**
     * Nombre de la tabla.
     *
     * @var string
     */
    private $table = "Carrera";

    /**
     * Constructor de la clase Carrera.
     *
     * Establece la conexión con la base de datos.
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtiene la lista de carreras, y si se especifica un centro_id, también los exámenes y su nota mínima.
     *
     * @param int|null $centro_id (Opcional) ID del centro para filtrar carreras.
     * @return array Lista de carreras con los exámenes asociados.
     * @throws Exception Si ocurre un error en la consulta.
     */
    public function obtenerCarreras($centro_id = null) {
        $carreras = [];
        
        if ($centro_id !== null) {
            // Se obtiene la lista de carreras asociadas al centro y sus exámenes
            $stmt = $this->conn->prepare("
                SELECT 
                    c.carrera_id, 
                    c.nombre AS carrera_nombre,
                    te.tipo_examen_id,
                    te.nombre AS examen_nombre,
                    te.nota_minima
                FROM Carrera c
                INNER JOIN CentroCarrera cc ON c.carrera_id = cc.carrera_id
                INNER JOIN CarreraExamen ce ON c.carrera_id = ce.carrera_id
                INNER JOIN TipoExamen te ON ce.tipo_examen_id = te.tipo_examen_id
                WHERE cc.centro_id = ?
                ORDER BY c.carrera_id, te.tipo_examen_id
            ");
            if (!$stmt) {
                throw new Exception('Error preparando la consulta: ' . $this->conn->error);
            }

            $stmt->bind_param("i", $centro_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Agrupar los resultados por carrera
            while ($row = $result->fetch_assoc()) {
                $carrera_id = $row['carrera_id'];
                
                if (!isset($carreras[$carrera_id])) {
                    $carreras[$carrera_id] = [
                        'carrera_id' => $carrera_id,
                        'carrera_nombre' => $row['carrera_nombre'],
                        'examenes' => []
                    ];
                }
                
                $carreras[$carrera_id]['examenes'][] = [
                    'tipo_examen_id' => $row['tipo_examen_id'],
                    'examen_nombre' => $row['examen_nombre'],
                    'nota_minima' => $row['nota_minima']
                ];
            }

            $stmt->close();
        } else {
            // Si no se proporciona un centro_id, obtenemos todas las carreras y sus exámenes
            $sql = "
                SELECT 
                    c.carrera_id, 
                    c.nombre AS carrera_nombre,
                    te.tipo_examen_id,
                    te.nombre AS examen_nombre,
                    te.nota_minima
                FROM Carrera c
                LEFT JOIN CarreraExamen ce ON c.carrera_id = ce.carrera_id
                LEFT JOIN TipoExamen te ON ce.tipo_examen_id = te.tipo_examen_id
                ORDER BY c.carrera_id, te.tipo_examen_id
            ";
            $result = $this->conn->query($sql);

            if (!$result) {
                throw new Exception('Error en la consulta: ' . $this->conn->error);
            }

            // Agrupar los resultados por carrera
            while ($row = $result->fetch_assoc()) {
                $carrera_id = $row['carrera_id'];
                
                if (!isset($carreras[$carrera_id])) {
                    $carreras[$carrera_id] = [
                        'carrera_id' => $carrera_id,
                        'carrera_nombre' => $row['carrera_nombre'],
                        'examenes' => []
                    ];
                }
                
                if ($row['tipo_examen_id']) {
                    $carreras[$carrera_id]['examenes'][] = [
                        'tipo_examen_id' => $row['tipo_examen_id'],
                        'examen_nombre' => $row['examen_nombre'],
                        'nota_minima' => $row['nota_minima']
                    ];
                }
            }
        }

        return $carreras;
    }

    /**
     * Obtiene los detalles de una carrera, su coordinador, el departamento al que pertence y su jefe de departamento.
     *
     * @param int $carrera_id ID de la carrera.
     * @return array Detalles de la carrera, coordinador y jefe de departamento.
     * @throws Exception Si ocurre un error en la consulta.
     */
    public function obtenerDetallesCarrera($carrera_id) {
        $sql = "
           SELECT 
                c.carrera_id,
                c.nombre AS carrera_nombre,
                c.coordinador_docente_id AS coordinador_id,
                CONCAT(d.nombre, ' ', d.apellido) AS coordinador_nombre_completo,
                dept.nombre AS departamento_nombre,
                dept.jefe_docente_id AS jefe_docente_id,
                CONCAT(jefe.nombre, ' ', jefe.apellido) AS jefe_departamento_nombre_completo
            FROM Carrera c
            LEFT JOIN Docente d ON c.coordinador_docente_id = d.docente_id
            LEFT JOIN Departamento dept ON c.dept_id = dept.dept_id
            LEFT JOIN Docente jefe ON dept.jefe_docente_id = jefe.docente_id
            WHERE c.carrera_id = ?;
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }

        $stmt->bind_param('i', $carrera_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null; // Si no se encuentra la carrera
        }

        $carrera = $result->fetch_assoc();
        $stmt->close();

        return $carrera;
    }
}
?>
