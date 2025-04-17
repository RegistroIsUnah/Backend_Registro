<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Clase Docente
 *
 * Maneja operaciones relacionadas con el docente.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class Docente {
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;

    /**
     * Constructor de la clase Docente.
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /** EN DESHUSO
     * Asigna un usuario a un docente utilizando el procedimiento almacenado SP_asignarUsuarioDocente.
     *
     * @param int $docente_id
     * @param string $username
     * @param string $password
     * @return array Resultado con el mensaje de éxito.
     * @throws Exception Si ocurre un error.
     */
    public function asignarUsuario($docente_id, $username, $password) {
        $stmt = $this->conn->prepare("CALL SP_asignarUsuarioDocente(?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
        $stmt->bind_param("iss", $docente_id, $username, $password);
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando la consulta: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $mensaje = null;
        if ($result) {
            $row = $result->fetch_assoc();
            $mensaje = $row['mensaje'] ?? null;
            $result->free();
        }
        $stmt->close();
        if (!$mensaje) {
            throw new Exception("No se obtuvo respuesta del procedimiento");
        }
        return ['mensaje' => $mensaje];
    }

    /*
        * Obtiene las clases activas de un docente.
        *
        * @param int $docente_id ID del docente.
        * @return array Lista de clases activas del docente.
        * @throws Exception Si ocurre un error o no se encuentra el docente.
        @Author Jose Vargas
    */
    public function obtenerClasesActDocente($docente_id) {
        $sql = "SELECT 
                    c.clase_id,
                    c.codigo AS codigo_clase,
                    c.nombre AS nombre_clase,
                    c.creditos,
                    c.tiene_laboratorio,
                    s.seccion_id,
                    s.hora_inicio,
                    s.hora_fin,
                    GROUP_CONCAT(DISTINCT sd.dia_id ORDER BY sd.dia_id SEPARATOR ', ') AS lista_dia_ids,
                    GROUP_CONCAT(DISTINCT ds.nombre ORDER BY sd.dia_id SEPARATOR ', ') AS nombres_dias,
                    e.nombre AS edificio,
                    a.nombre AS aula,
                    pa.anio,
                    pa.numero_periodo_id
                FROM Seccion s
                INNER JOIN Clase c ON s.clase_id = c.clase_id
                INNER JOIN PeriodoAcademico pa ON s.periodo_academico_id = pa.periodo_academico_id
                INNER JOIN Aula a ON s.aula_id = a.aula_id
                INNER JOIN Edificio e ON a.edificio_id = e.edificio_id
                INNER JOIN EstadoProceso ep ON pa.estado_proceso_id = ep.estado_proceso_id
                LEFT JOIN SeccionDia sd ON s.seccion_id = sd.seccion_id
                LEFT JOIN DiaSemana ds ON sd.dia_id = ds.dia_id
                WHERE 
                    s.docente_id = ?  -- ID del docente
                    AND ep.estado_proceso_id = 1  -- Periodo activo
                GROUP BY 
                    c.clase_id, c.codigo, c.nombre, c.creditos, c.tiene_laboratorio,
                    s.seccion_id, s.hora_inicio, s.hora_fin,
                    e.nombre, a.nombre, pa.anio, pa.numero_periodo_id
                ORDER BY 
                    s.seccion_id, s.hora_inicio, s.hora_fin";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $docente_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Docente no encontrado");
        }
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

     /**
     * Obtiene todos los docentes por departamento con los roles asignados (ID y nombre) y el nombre del departamento.
     *
     * @param int $dept_id ID del departamento
     * @return array Lista de docentes con roles y nombre del departamento
     */
    public function obtenerDocentesConRoles($dept_id)
    {
        $query = "
            SELECT
                d.docente_id,
                d.nombre,
                d.apellido,
                d.correo,
                d.numero_empleado,
                GROUP_CONCAT(CONCAT(r.rol_id, ':', r.nombre) SEPARATOR ',') AS roles,
                dep.nombre AS nombre_departamento
            FROM Docente d
            JOIN Usuario u ON u.usuario_id = d.usuario_id
            JOIN UsuarioRol ur ON ur.usuario_id = u.usuario_id
            JOIN Rol r ON ur.rol_id = r.rol_id
            JOIN Departamento dep ON dep.dept_id = d.dept_id
            WHERE d.dept_id = ?
            GROUP BY d.docente_id, dep.nombre
        ";

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            throw new Exception("Error al preparar la consulta: " . $this->conn->error);
        }

        $stmt->bind_param("i", $dept_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $docentes = [];
        
        while ($row = $result->fetch_assoc()) {
            $roles = [];
            if (!empty($row['roles'])) {
                foreach (explode(',', $row['roles']) as $role) {
                    list($roleId, $roleName) = explode(':', $role);
                    $roles[] = [
                        'rol_id' => (int)$roleId,  // Convertir a entero
                        'nombre' => $roleName
                    ];
                }
            }

            $docentes[] = [
                'docente_id' => (int)$row['docente_id'],
                'nombre' => $row['nombre'],
                'apellido' => $row['apellido'],
                'correo' => $row['correo'],
                'numero_empleado' => $row['numero_empleado'],
                'roles' => $roles,
                'nombre_departamento' => $row['nombre_departamento']
            ];
        }
        
        $stmt->close();
        return $docentes;
    }




   /**
     * Registra una calificación con observación validando permisos del docente
     * 
     * @param int $docente_id
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function calificarEstudiante($data) {
        // 1. Buscar estudiante_id a partir del numero_cuenta
        $sql_est = "SELECT estudiante_id FROM Estudiante WHERE numero_cuenta = ?";
        $stmt_est = $this->conn->prepare($sql_est);
        $stmt_est->bind_param("s", $data['numero_cuenta']);
        $stmt_est->execute();
        $res = $stmt_est->get_result();
    
        if ($res->num_rows === 0) {
            throw new Exception("Estudiante no encontrado con el número de cuenta proporcionado.", 404);
        }
    
        $row = $res->fetch_assoc();
        $estudiante_id = $row['estudiante_id'];
    
        // 2. Verificar si ya existe una calificación para ese estudiante y sección
        $sql_check = "SELECT historial_id FROM HistorialEstudiante WHERE estudiante_id = ? AND seccion_id = ?";
        $stmt_check = $this->conn->prepare($sql_check);
        $stmt_check->bind_param("ii", $estudiante_id, $data['seccion_id']);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();
    
        if ($res_check->num_rows > 0) {
            throw new Exception("Ya existe una calificación registrada para este estudiante en la sección proporcionada.", 409);
        }
    
        // 3. Insertar la calificación
        $sql = "INSERT INTO HistorialEstudiante 
                (estudiante_id, seccion_id, calificacion, observacion, fecha, estado_curso_id)
                VALUES (?, ?, ?, ?, NOW(), ?)";
    
        $estado_curso_id = $data['estado_curso_id'] ?? 1;
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iidsi", 
            $estudiante_id,
            $data['seccion_id'],
            $data['calificacion'],
            $data['observacion'],
            $estado_curso_id
        );
    
        if (!$stmt->execute()) {
            throw new Exception("Error al registrar calificación: " . $stmt->error);
        }
    
        return [
            'historial_id' => $stmt->insert_id,
            'fecha' => date('Y-m-d H:i:s')
        ];
    }


    
    /**
     * Obtiene los datos del docente a partir del ID de la sección.
     *
     * @param int $seccion_id ID de la sección
     * @return array Datos del docente (id, nombre, apellido, correo, foto, dept_id, nombre del departamento)
     * @throws Exception Si ocurre un error en la consulta
     */
    public function obtenerDocentePorSeccion($seccion_id) {
        $query = "
            SELECT 
                d.docente_id,
                d.nombre AS docente_nombre,
                d.apellido AS docente_apellido,
                d.correo AS docente_correo,
                d.foto AS docente_foto,
                d.dept_id,
                dep.nombre AS departamento_nombre
            FROM Seccion s
            JOIN Docente d ON s.docente_id = d.docente_id
            JOIN Departamento dep ON d.dept_id = dep.dept_id
            WHERE s.seccion_id = ?
        ";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }

        // Vincular el parámetro de la consulta (ID de la sección)
        $stmt->bind_param("i", $seccion_id);

        // Ejecutar la consulta
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando la consulta: " . $stmt->error);
        }

        // Obtener el resultado
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        $stmt->close();

        // Verificar si se encontró un docente para la sección
        if (!$data) {
            throw new Exception("No se encontró el docente para la sección con ID: $seccion_id");
        }

        return $data;
    }

    /**
     * Obtiene los datos completos de un docente por su ID.
     *
     * @param int $docente_id ID del docente a consultar
     * @return array Datos del docente con departamento y centro
     * @throws Exception Si no se encuentra el docente
     */
    public function obtenerDocenteCompleto($docente_id) {
        $query = "SELECT 
                    d.*,
                    dept.nombre AS nombre_departamento,
                    dept.facultad_id,
                    c.nombre AS nombre_centro
                  FROM Docente d
                  JOIN Departamento dept ON d.dept_id = dept.dept_id
                  JOIN Centro c ON d.centro_id = c.centro_id
                  WHERE d.docente_id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        if ($stmt === false) {
            throw new Exception("Error al preparar la consulta: " . $this->conn->error);
        }
        
        $stmt->bind_param("i", $docente_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("No se encontró el docente con ID: " . $docente_id);
        }
    
        // Obtener los datos como array asociativo
        $docente = $result->fetch_assoc();
        
        // Cerrar el statement
        $stmt->close();
        
        return $docente;
    }

    /**
    * Actualiza una calificación con observación validando permisos del docente
    * 
    * @param array $data
    * @return array
    * @throws Exception
    **/
    public function actualizarCalificacionEstudiante($data) {
        // 1. Buscar estudiante_id a partir del numero_cuenta
        $sql_est = "SELECT estudiante_id FROM Estudiante WHERE numero_cuenta = ?";
        $stmt_est = $this->conn->prepare($sql_est);
        $stmt_est->bind_param("s", $data['numero_cuenta']);
        $stmt_est->execute();
        $res = $stmt_est->get_result();

        if ($res->num_rows === 0) {
            throw new Exception("Estudiante no encontrado con el número de cuenta proporcionado.", 404);
        }

        $row = $res->fetch_assoc();
        $estudiante_id = $row['estudiante_id'];

        // 2. Actualizar la calificación
        $sql = "UPDATE HistorialEstudiante 
                SET calificacion = ?, observacion = ?, fecha = NOW(), estado_curso_id = ?
                WHERE estudiante_id = ? AND seccion_id = ?";

        $estado_curso_id = $data['estado_curso_id'] ?? 1;

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("dsiii", 
            $data['calificacion'],
            $data['observacion'],
            $estado_curso_id,
            $estudiante_id,
            $data['seccion_id']
        );

        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar calificación: " . $stmt->error);
        }

        if ($stmt->affected_rows === 0) {
            throw new Exception("No se encontró un registro para actualizar con el número de cuenta y sección especificados.", 404);
        }

        return [
            'mensaje' => 'Calificación actualizada correctamente',
            'fecha' => date('Y-m-d H:i:s')
        ];
    }



    /**
     * Obtiene un resumen de la evaluación por sección.
     *
     * @param int $seccionId ID de la sección
     * @return array Resumen de la evaluación por sección
     * @author Jose Vargas
     * @version 1.0
     * 
     */
    public function obtenerResumenEvaluacionPorSeccion($seccionId) {
        $sql = "
            SELECT 
                ed.docente_id,
                ed.seccion_id,
                re.pregunta_id,
                re.respuesta
            FROM EvaluacionDocente ed
            INNER JOIN RespuestaEvaluacion re ON ed.evaluacion_id = re.evaluacion_id
            WHERE ed.seccion_id = ?
            ORDER BY ed.docente_id, re.pregunta_id
        ";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $seccionId);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $resumen = [];
    
        while ($row = $result->fetch_assoc()) {
            $docenteId = $row['docente_id'];
            $preguntaId = $row['pregunta_id'];
            $respuesta = $row['respuesta'];
    
            if (!isset($resumen[$docenteId])) {
                $resumen[$docenteId] = [
                    'docente_id' => $docenteId,
                    'seccion_id' => $row['seccion_id'],
                    'resumen_respuestas' => []
                ];
            }
    
            $preguntas = &$resumen[$docenteId]['resumen_respuestas'];
            $respuestaKey = "respuestas-{$preguntaId}"; // Aquí generamos la clave dinámica para cada respuesta
    
            $found = false;
    
            // Recorremos las respuestas por pregunta
            foreach ($preguntas as &$p) {
                if ($p['pregunta_id'] == $preguntaId) {
                    $p[$respuestaKey][] = $respuesta; // Asignamos la respuesta a la clave dinámica
                    $found = true;
                    break;
                }
            }
    
            // Si no se encontró la pregunta, creamos una nueva entrada
            if (!$found) {
                $preguntas[] = [
                    'pregunta_id' => $preguntaId,
                    $respuestaKey => [$respuesta] // Usamos la clave dinámica para las respuestas
                ];
            }
        }
    
        return array_values($resumen);
    }


}
?>
