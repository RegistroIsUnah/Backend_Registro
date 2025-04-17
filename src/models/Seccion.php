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
        // Llamar al procedimiento almacenado SP_crearSeccion
        $stmt = $this->conn->prepare("CALL SP_crearSeccion(?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
    
        // Vincular parámetros (9 marcadores)
        $stmt->bind_param("iiississs", 
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
    
        // Ejecutar la consulta
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando la consulta: " . $stmt->error);
        }
    
        // Obtener el resultado (ID de la sección)
        $result = $stmt->get_result();
        $seccion_id = null;
        if ($result) {
            $row = $result->fetch_assoc();
            $seccion_id = $row['seccion_id'] ?? null;
            $result->free();
        }
    
        $stmt->close();
    
        // Si no se pudo obtener el ID de la sección, lanzar un error
        if (!$seccion_id) {
            throw new Exception("No se pudo crear la sección");
        }
    
        return $seccion_id;
    }
    
    /**
     * Modifica una sección utilizando el procedimiento almacenado SP_modificarSeccion.
     *
     * @param int|null $seccion_id ID de la sección a modificar.
     * @param int|null $docente_id Nuevo ID de docente (o NULL para no modificar).
     * @param int|null $aula_id Nuevo ID de aula (o NULL para no modificar).
     * @param string|null $estado Nuevo estado ('ACTIVA' o 'CANCELADA') o NULL para no modificar.
     * @param string|null $motivo_cancelacion Motivo de cancelación (requerido si estado es 'CANCELADA').
     * @param int|null $cupos Nuevo número de cupos (o NULL para no modificar).
     * @param string|null $video_url Nueva URL del video (o NULL para no modificar).
     * @return string Mensaje de éxito.
     * @throws Exception Si ocurre un error durante la modificación.
     */
    public function modificarSeccion(
        int    $seccion_id,
        ?int   $docente_id        = null,
        ?int   $aula_id           = null,
        ?string $estado           = null,
        ?string $motivo_cancelacion = null,
        ?int   $cupos             = null,
        ?string $video_url        = null,
        ?string $hora_inicio      = null,
        ?string $hora_fin         = null,
        ?string $dias             = null
    ) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
        $sql = "CALL SP_modificarSeccion(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando consulta: " . $this->conn->error);
        }
    
        // bind con todos los parámetros
        $stmt->bind_param(
            "iiississss",
            $seccion_id,
            $docente_id,
            $aula_id,
            $estado,
            $motivo_cancelacion,
            $cupos,
            $video_url,
            $hora_inicio,
            $hora_fin,
            $dias
        );
    
        try {
            $stmt->execute();
            // liberar posibles result sets
            do {
                $stmt->store_result();
            } while ($stmt->more_results() && $stmt->next_result());
        } finally {
            $stmt->close();
        }
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
        // Validar que el ID de clase sea un número válido.
        if (!is_numeric($clase_id)) {
            throw new Exception("El ID de clase debe ser un número válido.");
        }

        // Consulta SQL para obtener las secciones con detalles del docente, aula, edificio y estado
        $query = "
             SELECT 
                s.seccion_id,
                s.hora_inicio,
                s.hora_fin,
                s.estado_seccion_id,
                es.nombre AS estado_seccion,  
                s.video_url,
                s.motivo_cancelacion,
                s.cupos,
                d.nombre AS docente_nombre,
                d.apellido AS docente_apellido,
                a.nombre AS aula_nombre,
                e.nombre AS edificio_nombre,
                pd.anio AS anio_periodo,
                np.nombre AS periodo_nombre
            FROM Seccion s
            LEFT JOIN Docente d ON s.docente_id = d.docente_id
            LEFT JOIN PeriodoAcademico pd ON s.periodo_academico_id = pd.periodo_academico_id
            LEFT JOIN NumeroPeriodo np ON pd.numero_periodo_id = np.numero_periodo_id
            LEFT JOIN Aula a ON s.aula_id = a.aula_id
            LEFT JOIN Edificio e ON a.edificio_id = e.edificio_id
            LEFT JOIN EstadoSeccion es ON s.estado_seccion_id = es.estado_seccion_id  
            WHERE s.clase_id = ?;
        ";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }

        // Vinculamos el parámetro
        $stmt->bind_param("i", $clase_id);
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando la consulta: " . $stmt->error);
        }

        // Obtener los resultados
        $result = $stmt->get_result();
        $secciones = [];

        // Recorrer los resultados y almacenarlos en el array
        while ($row = $result->fetch_assoc()) {
            $secciones[] = $row;
        }

        // Si no se encontraron secciones, retornar un mensaje más claro
        if (empty($secciones)) {
            return ['message' => 'No se encontraron secciones para esta clase.'];
        }

        $stmt->close();
        return $secciones;
    }

      /**
     * Obtiene las secciones de una clase en estado activo con los detalles del docente, aula y edificio.
     *
     * @param int $clase_id ID de la clase.
     * @return array Lista de secciones con sus detalles.
     * @throws Exception Si ocurre un error en la consulta.
     */
    public function obtenerSeccionesPorClaseMatricula($clase_id) {
        // Validar que el ID de clase sea un número válido.
        if (!is_numeric($clase_id)) {
            throw new Exception("El ID de clase debe ser un número válido.");
        }

        // Consulta SQL para obtener las secciones con detalles del docente, aula, edificio y estado y los cupos disponibles
        $query = "
                    SELECT
                            s.seccion_id,
                            s.hora_inicio,
                            s.hora_fin,
                            DATE_FORMAT(s.hora_inicio, '%H%i') AS seccion_codigo,
                            s.estado_seccion_id,
                            es.nombre AS estado_seccion,
                            s.cupos - IFNULL(
                                (SELECT COUNT(*) 
                                FROM Matricula m
                                JOIN EstadoMatricula em ON m.estado_matricula_id = em.estado_matricula_id
                                WHERE m.seccion_id = s.seccion_id AND em.nombre = 'Matriculado'), 0) AS cupos_disponibles,
                            d.nombre AS docente_nombre,
                            d.apellido AS docente_apellido,
                            a.nombre AS aula_nombre,
                            e.nombre AS edificio_nombre,
                            GROUP_CONCAT(ds.nombre ORDER BY ds.nombre ASC) AS dias_seccion  -- Obtener los días de la sección
                        FROM Seccion s
                        LEFT JOIN Docente d ON s.docente_id = d.docente_id
                        LEFT JOIN Aula a ON s.aula_id = a.aula_id
                        LEFT JOIN Edificio e ON a.edificio_id = e.edificio_id
                        LEFT JOIN EstadoSeccion es ON s.estado_seccion_id = es.estado_seccion_id
                        LEFT JOIN SeccionDia sd ON s.seccion_id = sd.seccion_id
                        LEFT JOIN DiaSemana ds ON sd.dia_id = ds.dia_id
                        WHERE es.nombre = 'ACTIVA' 
                        AND s.clase_id = ?
                        GROUP BY s.seccion_id, s.hora_inicio, s.hora_fin, es.nombre, s.video_url, s.motivo_cancelacion, 
                                d.nombre, d.apellido, a.nombre, e.nombre
        ";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }

        // Vinculamos el parámetro
        $stmt->bind_param("i", $clase_id);
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando la consulta: " . $stmt->error);
        }

        // Obtener los resultados
        $result = $stmt->get_result();
        $secciones = [];

        // Recorrer los resultados y almacenarlos en el array
        while ($row = $result->fetch_assoc()) {
            $secciones[] = $row;
        }

        // Si no se encontraron secciones, retornar un mensaje más claro
        if (empty($secciones)) {
            return ['message' => 'No se encontraron secciones para esta clase.'];
        }

        $stmt->close();
        return $secciones;
    }

    /*
    * Obtiene la lista de estudiantes de una sección específica.
    *
    * @param int $seccion_id
    * @throws Exception
    * @author Jose Vargas
    * @version 1.2
    */
    public function seccionListaEstudiantes($seccion_id) {
        $sql = "SELECT 
                    e.numero_cuenta,
                    e.nombre,
                    e.apellido,
                    e.correo_personal
                FROM Matricula m
                INNER JOIN Estudiante e ON m.estudiante_id = e.estudiante_id
                WHERE m.seccion_id = ?";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $seccion_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("No se encontraron estudiantes para la sección especificada");
        }
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /*
    * Actualiza la URL del video de una sección específica.
    *
    * @param int $seccion_id
    * @param string $video_url
    * @throws Exception
    * @author Jose Vargas
    * @version 1.0
    */
    public function actualizarUrlVideo($seccion_id, $video_url) {
        $sql = "UPDATE Seccion SET video_url = ? WHERE seccion_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $video_url, $seccion_id);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            throw new Exception("No se pudo actualizar la URL del video o la sección no existe");
        }
    }

      /**
     * Obtiene las secciones activas por departamento
     * 
     * @param int $deptId ID del departamento
     * @return array Datos de las secciones
     * @throws RuntimeException Si ocurre un error en la consulta SQL
     */
    public function getSeccionesByDepartamento(int $deptId): array
    {
        $sql = "SELECT
                    s.seccion_id,
                    LPAD(DATE_FORMAT(s.hora_inicio, '%H%i'), 4, '0') AS numero_seccion,
                    c.codigo AS codigo_clase,
                    c.nombre AS nombre_clase,
                    d.numero_empleado AS numero_empleado_docente,
                    CONCAT(d.nombre, ' ', d.apellido) AS docente_asignado,
                    (SELECT COUNT(*) 
                     FROM Matricula m
                     JOIN EstadoMatricula em ON m.estado_matricula_id = em.estado_matricula_id
                     WHERE m.seccion_id = s.seccion_id AND em.nombre = 'Matriculado') AS estudiantes_matriculados,
                    s.cupos AS cupos_habilitados,
                    e.nombre AS edificio,
                    a.nombre AS aula
                FROM 
                    Seccion s
                JOIN 
                    Clase c ON s.clase_id = c.clase_id
                JOIN 
                    Docente d ON s.docente_id = d.docente_id
                JOIN 
                    Aula a ON s.aula_id = a.aula_id
                JOIN 
                    Edificio e ON a.edificio_id = e.edificio_id
                WHERE 
                    s.estado_seccion_id = (SELECT estado_seccion_id FROM EstadoSeccion WHERE nombre = 'ACTIVA')
                AND 
                    c.dept_id = ?
                ORDER BY 
                    s.hora_inicio";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException("Error al preparar la consulta: " . $this->conn->error);
        }
        
        $stmt->bind_param("i", $deptId);
        if (!$stmt->execute()) {
            throw new RuntimeException("Error al ejecutar la consulta: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene el nombre de un departamento por su ID
     *
     * @param int $deptId
     * @return string
     */
    public function getNombreDepartamento(int $deptId): string
    {
        $sql = "SELECT nombre FROM Departamento WHERE dept_id = ?";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new RuntimeException("Error al preparar la consulta: " . $this->conn->error);
        }

        $stmt->bind_param("i", $deptId);
        if (!$stmt->execute()) {
            throw new RuntimeException("Error al ejecutar la consulta: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row ? $row['nombre'] : 'Departamento desconocido';
    }

}
?>
