<?php
require_once __DIR__ . '/../modules/config/DataBase.php';
require_once __DIR__ . '/../mail/mail_sender.php';

/**
 * Clase Aspirante
 *
 * Maneja la inserción de un aspirante mediante el procedimiento almacenado SP_insertarAspirante.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.1
 * 
 */
class Aspirante {
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;

    /**
     * Constructor de la clase Aspirante.
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Inserta un aspirante utilizando el procedimiento almacenado SP_insertarAspirante.
     *
     * @param string $nombre
     * @param string $apellido
     * @param string $documento
     * @param string $telefono
     * @param string $correo
     * @param string $fotoRuta Ruta de la foto del aspirante.
     * @param string $fotodniRuta Ruta de la foto del DNI.
     * @param int $carrera_principal_id
     * @param int|null $carrera_secundaria_id
     * @param int $centro_id
     * @param string $certificadoRuta Ruta del certificado subido.
     * @param int $tipo_documento_id ID del tipo de documento del aspirante.
     * @return string Número de solicitud generado.
     * @throws Exception Si ocurre un error durante la inserción.
     */
    public function insertarAspirante($nombre, $apellido, $documento, $telefono, $correo, $fotoRuta, $fotodniRuta, $carrera_principal_id, $carrera_secundaria_id, $centro_id, $certificadoRuta, $tipo_documento_id) {
        // Se esperan 12 parámetros, por lo tanto 12 marcadores
        $stmt = $this->conn->prepare("CALL SP_insertarAspirante(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
    
        // La cadena de tipos es: 7 strings, 3 enteros, 1 string = "sssssssiiisi"
        if (!$stmt->bind_param("sssssssiiisi", 
            $nombre, 
            $apellido, 
            $documento, 
            $telefono, 
            $correo, 
            $fotoRuta,
            $fotodniRuta,
            $carrera_principal_id, 
            $carrera_secundaria_id, 
            $centro_id, 
            $certificadoRuta,
            $tipo_documento_id
        )) {
            throw new Exception("Error vinculando parámetros: " . $stmt->error);
        }
    
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando la consulta: " . $stmt->error);
        }
    
        $result = $stmt->get_result();
        $numSolicitud = null;
        if ($result) {
            $row = $result->fetch_assoc();
            $numSolicitud = $row['numSolicitud'] ?? null;
            $result->free();
        }
    
        $stmt->close();
    
        if (!$numSolicitud) {
            throw new Exception("No se obtuvo el número de solicitud");
        }
    
        // Enviar correo de confirmación
        $this->enviarCorreo($nombre, $apellido, $documento, $numSolicitud, $correo);
    
        return $numSolicitud;
    }
    
    /**
     * Función para enviar el correo de confirmación al aspirante.
     * 
     * @param string $nombre
     * @param string $apellido
     * @param string $documento
     * @param string $numSolicitud
     * @param string $correo
     */
    /*
    private function enviarCorreoDeConfirmacion($nombre, $apellido, $documento, $numSolicitud, $correo) {
        // Guardar el contenido del correo en variables
        $subject = 'Confirmación de Registro';
        $message = "
            <h3>Hola {$nombre} {$apellido},</h3>
            <p>Tu registro ha sido exitoso. Aquí están los detalles:</p>
            <p><strong>Documento:</strong> {$documento}</p>
            <p><strong>Número de Solicitud:</strong> {$numSolicitud}</p>
            <p>Por favor, guarda esta información ya que es importante para futuras consultas.</p>
        ";
        $altmess = "Hola {$nombre} {$apellido},\n\nTu registro ha sido exitoso. Aquí están los detalles:\nDocumento: 
        {$documento}\nNúmero de Solicitud: {$numSolicitud}\nPor favor, guarda esta información.";
    
        // Ejecutar en segundo plano después de la respuesta
        register_shutdown_function(function() use ($correo, $nombre, $apellido, $subject, $message, $altmess) {
            sendmail($correo, "{$nombre} {$apellido}", $subject, $message, $altmess);
        });
    }*/

    private function enviarCorreo($nombre, $apellido, $documento, $numSolicitud, $correo) {
        // Guardar el contenido del correo en variables
        $subject = 'Confirmación de Registro';
        $message = "
            <h3>Hola {$nombre} {$apellido},</h3>
            <p>Tu registro ha sido exitoso. Aquí están los detalles:</p>
            <p><strong>Documento:</strong> {$documento}</p>
            <p><strong>Número de Solicitud:</strong> {$numSolicitud}</p>
            <p>Por favor, guarda esta información ya que es importante para futuras consultas.</p>
            <br>
            <p>Puede revisar el estado de su solicitud en esta pagina</p>
            <br>
            <a href=\"https://registroisunah.xyz/admisiones.php\">Ver solicitud</a>
        ";
        $altmess = "Hola {$nombre} {$apellido},\n\nTu registro ha sido exitoso. Aquí están los detalles:\nDocumento: 
        {$documento}\nNúmero de Solicitud: {$numSolicitud}\nPor favor, guarda esta información.";
    
        // Usar register_shutdown_function para ejecutar después de la respuesta
        register_shutdown_function(function() use ($correo, $nombre, $apellido, $subject, $message, $altmess) {
            // Pequeña pausa para asegurar que la respuesta se envió primero
           // usleep(100000); // 100ms (opcional)
            sendmail($correo, "{$nombre} {$apellido}", $subject, $message, $altmess);
        });
    }

     /**
     * Obtiene la lista de los aspirantes admitidos 
     * @return string JSON con la lista de los aspirantes admitidos.
     * LOS CAMPOS QUE SE MUESTRAN SON:
     * aspirante_id
     * documento
     * nombre
     * apellido
     * correo
     * telefono
     * numsolicitud
     * carrera_principal
     * carrera_secundaria
     * centro
     * tipo_documento
     * Los datos después de ser obtenidos se pasaron a un CSV
     */
    /*
        Ejemplo de respuesta:
            {
            "success": true,
            "data": [
                {
                    "aspirante_id": 1,
                    "documento": "0801199901234",
                    "nombre": "Juan",
                    "apellido": "Pérez",
                    "correo": "juan@example.com",
                    "telefono": "98765432",
                    "numSolicitud": "SOL-2023-001",
                    "carrera_principal": "Ingeniería en Sistemas",
                    "carrera_secundaria": null,
                    "centro": "Campus Central"
                },
                {
                    "aspirante_id": 2,
                    "documento": "0801199905678",
                    "nombre": "María",
                    "apellido": "García",
                    "correo": "maria@example.com",
                    "telefono": "98765433",
                    "numSolicitud": "SOL-2023-002",
                    "carrera_principal": "Medicina",
                    "carrera_secundaria": "Enfermería",
                    "centro": "Campus Norte"
                }
            ],
            "error": ""
        }
     */
    public function obtenerAspirantesAdmitidos() {
        // SQL actualizado para usar estado_aspirante_id y tipo_documento_id
        $sql = "SELECT 
                    A.aspirante_id,
                    A.documento,
                    A.nombre,
                    A.apellido,
                    A.correo,
                    A.telefono,
                    A.numSolicitud,
                    C_principal.nombre AS carrera_principal,
                    C_secundaria.nombre AS carrera_secundaria,
                    Cen.nombre AS centro,
                    T.nombre AS tipo_documento
                FROM Aspirante A
                INNER JOIN Carrera C_principal ON A.carrera_principal_id = C_principal.carrera_id
                LEFT JOIN Carrera C_secundaria ON A.carrera_secundaria_id = C_secundaria.carrera_id
                INNER JOIN Centro Cen ON A.centro_id = Cen.centro_id
                INNER JOIN TipoDocumento T ON A.tipo_documento_id = T.tipo_documento_id
                WHERE A.estado_aspirante_id = (SELECT estado_aspirante_id FROM EstadoAspirante WHERE nombre = 'ADMITIDO')";
        
        $result = $this->conn->query($sql);

        $response = [
            'success' => false,
            'data' => [],
            'error' => ''
        ];

        if (!$result) {
            $response['error'] = "Error en la consulta: " . $this->conn->error;
            return json_encode($response);
        }

        $aspirantes = [];
        while ($row = $result->fetch_assoc()) {
            $aspirantes[] = $row;
        }

        $response['success'] = true;
        $response['data'] = $aspirantes;

        return json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }


    /**
     * Obtiene la lista de los aspirantes admitidos en formato CSV
     * Usando como base la funcion obtenerAspirantesAdmitidos
     * @return string CSV con la lista de los aspirantes admitidos.
     */
    public function exportarAspirantesAdmitidosCSV() {
        // Consulta SQL para obtener los aspirantes admitidos con estado_aspirante_id
        $sql = "SELECT 
                    A.aspirante_id,
                    A.documento,
                    A.nombre,
                    A.apellido,
                    A.correo,
                    A.telefono,
                    A.numSolicitud,
                    C_principal.nombre AS carrera_principal,
                    C_secundaria.nombre AS carrera_secundaria,
                    Cen.nombre AS centro
                FROM Aspirante A
                INNER JOIN Carrera C_principal ON A.carrera_principal_id = C_principal.carrera_id
                LEFT JOIN Carrera C_secundaria ON A.carrera_secundaria_id = C_secundaria.carrera_id
                INNER JOIN Centro Cen ON A.centro_id = Cen.centro_id
                WHERE A.estado_aspirante_id = (SELECT estado_aspirante_id FROM EstadoAspirante WHERE nombre = 'ADMITIDO')";

        // Ejecutar la consulta
        $result = $this->conn->query($sql);

        if (!$result) {
            throw new Exception("Error en la consulta: " . $this->conn->error);
        }

        // Configurar salida directa a PHP output
        $output = fopen('php://output', 'w');

        // Escribir la cabecera del CSV
        fputcsv($output, [
            'aspirante_id',
            'documento',
            'nombre',
            'apellido',
            'correo',
            'telefono',
            'numSolicitud',
            'carrera_principal',
            'carrera_secundaria',
            'centro'
        ]);

        // Escribir los datos de los aspirantes
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }

        // Cerrar el archivo
        fclose($output);
    }

    public function evaluarAspirante($aspiranteId) {
        // Obtener notas y datos del aspirante
        $sql = "SELECT 
                    a.aspirante_id,
                    a.carrera_principal_id,
                    a.carrera_secundaria_id,
                    c_principal.nombre AS carrera_principal,
                    c_secundaria.nombre AS carrera_secundaria,
                    re.calificacion,
                    t.nombre AS tipo_examen,
                    t.nota_minima
                FROM Aspirante a
                LEFT JOIN ResultadoExamen re ON a.aspirante_id = re.aspirante_id
                LEFT JOIN TipoExamen t ON re.tipo_examen_id = t.tipo_examen_id
                LEFT JOIN Carrera c_principal ON a.carrera_principal_id = c_principal.carrera_id
                LEFT JOIN Carrera c_secundaria ON a.carrera_secundaria_id = c_secundaria.carrera_id
                WHERE a.aspirante_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $aspiranteId);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows === 0) {
            throw new Exception("Aspirante no encontrado");
        }
    
        $datos = [
            'principal' => [],
            'secundaria' => []
        ];
    
        while ($row = $result->fetch_assoc()) {
            $datos['info_general'] = [
                'aspirante_id' => $row['aspirante_id'],
                'carrera_principal' => $row['carrera_principal'],
                'carrera_secundaria' => $row['carrera_secundaria']
            ];
    
            if ($row['calificacion']) {
                $datos['examenes'][] = [
                    'tipo' => $row['tipo_examen'],
                    'calificacion' => $row['calificacion'],
                    'nota_minima' => $row['nota_minima']
                ];
            }
        }
    
        // Obtener requisitos por carrera
        $requisitos = $this->obtenerRequisitosCarreras(
            $datos['info_general']['carrera_principal_id'],
            $datos['info_general']['carrera_secundaria_id']
        );
    
        // Realizar evaluación
        return $this->procesarEvaluacion($datos, $requisitos);
    }
    
    private function obtenerRequisitosCarreras($principalId, $secundariaId) {
        $sql = "SELECT carrera_id, GROUP_CONCAT(tipo_examen_id) AS examenes_requeridos 
                FROM CarreraExamen 
                WHERE carrera_id IN (?, ?)
                GROUP BY carrera_id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $principalId, $secundariaId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $requisitos = [];
        
        while ($row = $result->fetch_assoc()) {
            $requisitos[$row['carrera_id']] = explode(',', $row['examenes_requeridos']);
        }
        
        return $requisitos;
    }
    
    private function procesarEvaluacion($datos, $requisitos) {
        $resultado = [
            'decision' => 'RECHAZADO',
            'carrera_asignada' => null,
            'detalles' => []
        ];
    
        // Evaluar carrera principal
        if ($this->cumpleRequisitos($datos, $requisitos[$datos['info_general']['carrera_principal_id']])) {
            $resultado['decision'] = 'ADMITIDO';
            $resultado['carrera_asignada'] = $datos['info_general']['carrera_principal'];
            $this->actualizarEstadoAspirante($datos['info_general']['aspirante_id'], 'ADMITIDO');
            return $resultado;
        }
    
        // Evaluar carrera secundaria
        if ($datos['info_general']['carrera_secundaria'] && 
            $this->cumpleRequisitos($datos, $requisitos[$datos['info_general']['carrera_secundaria_id']])) {
            $resultado['decision'] = 'ADMITIDO';
            $resultado['carrera_asignada'] = $datos['info_general']['carrera_secundaria'];
            $this->actualizarEstadoAspirante($datos['info_general']['aspirante_id'], 'ADMITIDO');
            return $resultado;
        }
    
        $this->actualizarEstadoAspirante($datos['info_general']['aspirante_id'], 'RECHAZADO');
        return $resultado;
    }
    
    private function cumpleRequisitos($datos, $examenesRequeridos) {
        foreach ($examenesRequeridos as $examenId) {
            $aprobado = false;
            foreach ($datos['examenes'] as $examen) {
                if ($examen['tipo_examen_id'] == $examenId && 
                    $examen['calificacion'] >= $examen['nota_minima']) {
                    $aprobado = true;
                    break;
                }
            }
            if (!$aprobado) return false;
        }
        return true;
    }
    
    private function actualizarEstadoAspirante($aspiranteId, $estado) {
        $sql = "UPDATE Aspirante SET estado = ? WHERE aspirante_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $estado, $aspiranteId);
        $stmt->execute();
    }

    
    /**
     * Obtiene una solicitud pendiente o corregida y la asigna al revisor.
     *
     * Se selecciona una solicitud de la tabla Aspirante en la que:
     *   - estado IN ('PENDIENTE', 'CORREGIDO_PENDIENTE')
     * Se ordena por fecha_solicitud ascendente y se toma la primera.
     * Luego, se inserta un registro en RevisionAspirante con el ID del aspirante, el ID del revisor
     * y la fecha de revisión (NOW()). Si no hay solicitudes disponibles, se retorna NULL.
     *
     * @param int $revisor_id ID del revisor que solicita la revisión.
     * @return array|null Los datos de la solicitud asignada o NULL si no hay solicitudes pendientes.
     * @throws Exception Si ocurre un error durante la transacción.
     */
    public function obtenerYAsignarSolicitud($revisor_id) {
        $this->conn->begin_transaction();
        try {
            // Seleccionar una solicitud que esté pendiente o corregida (ahora filtramos por estado_aspirante_id)
            $sql = "SELECT A.aspirante_id, A.nombre, A.apellido, A.documento, A.telefono, A.correo, A.foto, A.fotodni, A.numSolicitud, 
                            A.carrera_principal_id, A.carrera_secundaria_id, A.centro_id, A.certificado_url, A.estado_aspirante_id, A.fecha_solicitud
                    FROM Aspirante A
                    INNER JOIN EstadoAspirante E ON A.estado_aspirante_id = E.estado_aspirante_id
                    WHERE E.nombre IN ('PENDIENTE', 'CORREGIDO_PENDIENTE')
                    ORDER BY A.fecha_solicitud ASC
                    LIMIT 1";
                    
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error preparando la consulta: " . $this->conn->error);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $solicitud = $result->fetch_assoc();
            $stmt->close();
    
            if (!$solicitud) {
                $this->conn->commit();
                return null; // No hay solicitudes pendientes
            }
    
            // Insertar la asignación de la revisión en RevisionAspirante
            $sqlInsert = "INSERT INTO RevisionAspirante (aspirante_id, revisor_usuario_id, fecha_revision)
                        VALUES (?, ?, NOW())";
            $stmt = $this->conn->prepare($sqlInsert);
            if (!$stmt) {
                throw new Exception("Error preparando la inserción en RevisionAspirante: " . $this->conn->error);
            }
            $stmt->bind_param("ii", $solicitud['aspirante_id'], $revisor_id);
            if (!$stmt->execute()) {
                throw new Exception("Error insertando en RevisionAspirante: " . $stmt->error);
            }
            $stmt->close();
    
            $this->conn->commit();
            return $solicitud;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
    
    /**
     * Procesa la revisión de una solicitud de aspirante.
     *
     * @param int $aspirante_id ID del aspirante.
     * @param int $revisor_id ID del revisor.
     * @param string $accion 'aceptar' o 'rechazar'.
     * @param array|null $motivos Array de motivo_id (numéricos) en caso de rechazo.
     * @return array Datos de la revisión (revision_id y la acción realizada).
     * @throws Exception Si ocurre algún error durante la transacción.
     */
    public function procesarRevision($aspirante_id, $revisor_id, $accion, $motivos = null) {
        $this->conn->begin_transaction();
        try {
            // Determinar el nuevo estado según la acción
            if (strtolower($accion) === 'aceptar') {
                $nuevoEstado = 'ADMITIDO';
            } elseif (strtolower($accion) === 'rechazar') {
                $nuevoEstado = 'RECHAZADO';
            } else {
                throw new Exception("La acción debe ser 'aceptar' o 'rechazar'");
            }
    
            // Obtener el estado_aspirante_id correspondiente a los estados 'ADMITIDO' y 'RECHAZADO'
            $estadoAspiranteId = $this->obtenerEstadoAspiranteId($nuevoEstado);
    
            // Actualizar el estado del aspirante
            $sqlUpdate = "UPDATE Aspirante SET estado_aspirante_id = ? WHERE aspirante_id = ?";
            $stmt = $this->conn->prepare($sqlUpdate);
            if (!$stmt) {
                throw new Exception("Error preparando actualización en Aspirante: " . $this->conn->error);
            }
            $stmt->bind_param("ii", $estadoAspiranteId, $aspirante_id);
            if (!$stmt->execute()) {
                throw new Exception("Error actualizando el estado del aspirante: " . $stmt->error);
            }
            $stmt->close();
    
            // Insertar registro en RevisionAspirante
            $sqlInsertRevision = "INSERT INTO RevisionAspirante (aspirante_id, revisor_usuario_id, fecha_revision) VALUES (?, ?, NOW())";
            $stmt = $this->conn->prepare($sqlInsertRevision);
            if (!$stmt) {
                throw new Exception("Error preparando inserción en RevisionAspirante: " . $this->conn->error);
            }
            $stmt->bind_param("ii", $aspirante_id, $revisor_id);
            if (!$stmt->execute()) {
                throw new Exception("Error insertando en RevisionAspirante: " . $stmt->error);
            }
            $revision_id = $stmt->insert_id;
            $stmt->close();
    
            // Si la acción es rechazar, registrar los motivos
            if (strtolower($accion) === 'rechazar') {
                if (empty($motivos) || !is_array($motivos)) {
                    throw new Exception("Al rechazar, se debe enviar al menos un motivo");
                }
                foreach ($motivos as $motivo_id) {
                    if (!is_numeric($motivo_id)) {
                        throw new Exception("El motivo_id '$motivo_id' debe ser numérico");
                    }
                    $motivo_id = (int)$motivo_id;
                    $sqlInsertMotivo = "INSERT INTO AspiranteMotivoRechazo (revision_id, motivo_id, fecha_rechazo) VALUES (?, ?, NOW())";
                    $stmt = $this->conn->prepare($sqlInsertMotivo);
                    if (!$stmt) {
                        throw new Exception("Error preparando inserción en AspiranteMotivoRechazo: " . $this->conn->error);
                    }
                    $stmt->bind_param("ii", $revision_id, $motivo_id);
                    if (!$stmt->execute()) {
                        throw new Exception("Error insertando en AspiranteMotivoRechazo: " . $stmt->error);
                    }
                    $stmt->close();
                }
            }
    
            $this->conn->commit();
            return ['revision_id' => $revision_id, 'accion' => strtolower($accion)];
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    /**
     * Obtiene el ID de estado aspirante correspondiente al nombre.
     * 
     * @param string $estado Nombre del estado (ej. 'ADMITIDO', 'RECHAZADO').
     * @return int El ID del estado aspirante.
     * @throws Exception Si no se encuentra el estado.
     */
    private function obtenerEstadoAspiranteId($estado) {
        $sql = "SELECT estado_aspirante_id FROM EstadoAspirante WHERE nombre = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
        $stmt->bind_param("s", $estado);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row) {
            return $row['estado_aspirante_id'];
        } else {
            throw new Exception("Estado de aspirante '$estado' no encontrado");
        }
    }

    /**
     * Obtiene los datos de un aspirante por su documento.
     *
     * @param string $documento Documento del aspirante.
     * @return array|null Datos del aspirante o null si no se encuentra.
     * @throws Exception Si ocurre un error en la consulta.
     */
    public function obtenerAspirantePorDocumento($documento) {
        $sql = "
        SELECT 
            a.aspirante_id,
            a.nombre,
            a.apellido,
            a.numSolicitud,
            a.documento,
            td.nombre AS tipo_documento,
            a.fecha_solicitud,
            c.nombre AS carrera_principal,
            c2.nombre AS carrera_secundaria     
        FROM Aspirante a
        LEFT JOIN Carrera c ON a.carrera_principal_id = c.carrera_id
        LEFT JOIN Carrera c2 ON a.carrera_secundaria_id = c2.carrera_id
        LEFT JOIN TipoDocumento td ON a.tipo_documento_id = td.tipo_documento_id
        WHERE a.documento = ?
                ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }

        $stmt->bind_param('s', $documento);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null; // Si no se encuentra el aspirante
        }

        $aspirante = $result->fetch_assoc();
        $stmt->close();

        return $aspirante;
    }

    /**
     * Obtiene los datos de un aspirante por su número de solicitud.
     *
     * @param string $numSolicitud Número de solicitud del aspirante.
     * @return array|null Datos del aspirante o null si no se encuentra.
     * @throws Exception Si ocurre un error en la consulta.
     */
    public function obtenerAspirantePorSolicitud($numSolicitud) {
        $sql = "
            SELECT 
                a.aspirante_id,
                a.nombre AS aspirante_nombre,
                a.apellido AS aspirante_apellido,
                a.documento,
                a.telefono,
                a.correo,
                a.foto,
                a.fotodni,
                a.numSolicitud,
                td.nombre AS tipo_documento,
                c.nombre AS carrera_principal,
                c2.nombre AS carrera_secundaria,
                e.nombre AS estado_aspirante,
                a.fecha_solicitud,
                r.fecha_revision,
                tr.nombre AS tipo_rechazo,
                mr.descripcion AS motivo_rechazo
            FROM Aspirante a
            LEFT JOIN TipoDocumento td ON a.tipo_documento_id = td.tipo_documento_id
            LEFT JOIN Carrera c ON a.carrera_principal_id = c.carrera_id
            LEFT JOIN Carrera c2 ON a.carrera_secundaria_id = c2.carrera_id
            LEFT JOIN EstadoAspirante e ON a.estado_aspirante_id = e.estado_aspirante_id
            LEFT JOIN RevisionAspirante r ON a.aspirante_id = r.aspirante_id
            LEFT JOIN AspiranteMotivoRechazo amr ON r.revision_id = amr.revision_id
            LEFT JOIN MotivoRechazoAspirante mr ON amr.motivo_id = mr.motivo_id
            LEFT JOIN TipoRechazoSolicitudAspirante tr ON mr.tipo_rechazo_id = tr.tipo_rechazo_id
            WHERE a.numSolicitud = ?
            ORDER BY tr.nombre, mr.descripcion;
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
    
        $stmt->bind_param('s', $numSolicitud);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows === 0) {
            return null;
        }
    
        // Procesar todas las filas primero
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    
        // Extraer datos básicos del aspirante (primera fila)
        $aspirante = $rows[0];
        
        // Limpiar campos de rechazo del objeto principal
        unset($aspirante['tipo_rechazo']);
        unset($aspirante['motivo_rechazo']);
    
        // Agrupar rechazos
        $aspirante['rechazos'] = [];
        foreach ($rows as $row) {
            if (!empty($row['tipo_rechazo'])) {
                $tipo = $row['tipo_rechazo'];
                $motivo = $row['motivo_rechazo'];
                
                // Buscar si ya existe el tipo de rechazo
                $index = array_search($tipo, array_column($aspirante['rechazos'], 'tipo_rechazo'));
                
                if ($index === false) {
                    // Nuevo tipo de rechazo
                    $aspirante['rechazos'][] = [
                        'tipo_rechazo' => $tipo,
                        'motivos' => [$motivo]
                    ];
                } else {
                    // Agregar motivo al tipo existente
                    if (!in_array($motivo, $aspirante['rechazos'][$index]['motivos'])) {
                        $aspirante['rechazos'][$index]['motivos'][] = $motivo;
                    }
                }
            }
        }
        return $aspirante;
    }

     /**
     * Actualiza los detalles de un aspirante por su número de solicitud.
     * También cambia el estado a CORREGIDO_PENDIENTE si el estado actual es RECHAZADO.
     *
     * @param string $numSolicitud Número de solicitud del aspirante.
     * @param array $data Datos a actualizar (campos a actualizar y archivos si es necesario).
     * @return bool Verdadero si la actualización fue exitosa, falso de lo contrario.
     * @throws Exception Si ocurre un error en la consulta.
     */
    public function actualizarAspirantePorSolicitud($numSolicitud, $data) {
        $setClause = [];
        $params = [];
    
        if (isset($data['aspirante_nombre'])) {
            $setClause[] = "nombre = ?";
            $params[] = $data['aspirante_nombre'];
        }
        
        if (isset($data['aspirante_apellido'])) {
            $setClause[] = "apellido = ?";
            $params[] = $data['aspirante_apellido'];
        }
    
        if (isset($data['documento'])) {
            $setClause[] = "documento = ?";
            $params[] = $data['documento'];
        }
    
        if (isset($data['telefono'])) {
            $setClause[] = "telefono = ?";
            $params[] = $data['telefono'];
        }
    
        if (isset($data['correo'])) {
            $setClause[] = "correo = ?";
            $params[] = $data['correo'];
        }
    
        // Aquí se actualizan los campos de archivos usando el valor ya procesado en el controlador
        if (isset($data['foto'])) {
            $setClause[] = "foto = ?";
            $params[] = $data['foto'];
        }
    
        if (isset($data['fotodni'])) {
            $setClause[] = "fotodni = ?";
            $params[] = $data['fotodni'];
        }
    
        if (isset($data['certificado_url'])) {
            $setClause[] = "certificado_url = ?";
            $params[] = $data['certificado_url'];
        }
    
        if (isset($data['tipo_documento'])) {
            $setClause[] = "tipo_documento_id = (SELECT tipo_documento_id FROM TipoDocumento WHERE nombre = ?)";
            $params[] = $data['tipo_documento'];
        }
    
        if (empty($setClause)) {
            throw new Exception('No hay datos para actualizar');
        }
    
        $sql = "UPDATE Aspirante SET " . implode(", ", $setClause) . " WHERE numSolicitud = ?";
        $params[] = $numSolicitud;
    
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
    
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
    
        // Se actualiza el estado si el aspirante estaba en RECHAZADO
        $this->actualizarEstadoSiRechazado($numSolicitud);
    
        return $affectedRows > 0;
    }

    /**
     * Cambia el estado del aspirante de RECHAZADO a CORREGIDO_PENDIENTE.
     *
     * @param string $numSolicitud Número de solicitud del aspirante.
     * @return void
     * @throws Exception Si ocurre un error en la consulta.
     */
    public function actualizarEstadoSiRechazado($numSolicitud) {
        $sql = "UPDATE Aspirante 
                SET estado_aspirante_id = (SELECT estado_aspirante_id FROM EstadoAspirante WHERE nombre = 'CORREGIDO_PENDIENTE') 
                WHERE numSolicitud = ? AND estado_aspirante_id = (SELECT estado_aspirante_id FROM EstadoAspirante WHERE nombre = 'RECHAZADO')";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }

        $stmt->bind_param('s', $numSolicitud);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Reenvía el correo de confirmación a un aspirante.
     * 
     * @param string $numSolicitud Número de solicitud (formato: SOL-XXXXX).
     * @return bool True si el correo se envió correctamente.
     * @throws Exception Si no se encuentra el aspirante o falla el envío.
     */
    public function reenviarCorreoPorSolicitud($numSolicitud) {
        // 1. Buscar al aspirante
        $query = "SELECT nombre, apellido, documento, correo FROM Aspirante WHERE numSolicitud = ?";
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $this->conn->error);
        }

        $stmt->bind_param("s", $numSolicitud);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }

        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("No se encontró el aspirante con número de solicitud: $numSolicitud");
        }
        
        $aspirante = $result->fetch_assoc();
        $stmt->close();
        
        // 2. Enviar correo
        return $this->enviarCorreo(
            $aspirante['nombre'],
            $aspirante['apellido'],
            $aspirante['documento'],
            $numSolicitud,
            $aspirante['correo']
        );
    }
}
?>
