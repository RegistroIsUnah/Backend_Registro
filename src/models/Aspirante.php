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
    function enviarCorreo($nombre, $apellido, $documento, $numSolicitud, $correo) {
        // Plantilla HTML como la que creamos anteriormente
        $subject = 'Admisiones';
        $message = '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Confirmación de Registro</title>
            <style>
                body {
                    font-family: \'Arial\', sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 20px;
                    line-height: 1.6;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: white;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                }
                .header {
                    background-color: #3498db;
                    color: white;
                    text-align: center;
                    padding: 20px;
                    border-radius: 10px 10px 0 0;
                }
                .content {
                    padding: 20px;
                }
                .details {
                    background-color: #f9f9f9;
                    border-left: 4px solid #3498db;
                    padding: 15px;
                    margin: 20px 0;
                }
                .btn {
                    display: inline-block;
                    background-color: #2ecc71;
                    color: white;
                    padding: 12px 25px;
                    text-decoration: none;
                    border-radius: 5px;
                    margin-top: 20px;
                    transition: background-color 0.3s ease;
                }
                .btn:hover {
                    background-color: #27ae60;
                }
                .footer {
                    text-align: center;
                    color: #7f8c8d;
                    margin-top: 20px;
                    font-size: 0.9em;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Confirmación de Registro</h1>
                </div>
                <div class="content">
                    <h3>Hola ' . htmlspecialchars($nombre) . ' ' . htmlspecialchars($apellido) . ',</h3>
                    
                    <p>Tu registro ha sido exitoso. Aquí están los detalles:</p>
                    
                    <div class="details">
                        <p><strong>Documento:</strong> ' . htmlspecialchars($documento) . '</p>
                        <p><strong>Número de Solicitud:</strong> ' . htmlspecialchars($numSolicitud) . '</p>
                    </div>
                    
                    <p>Por favor, guarda esta información ya que es importante para futuras consultas.</p>
                    
                    <div style="text-align: center;">
                        <a href="https://registroisunah.xyz/admisiones.php" class="btn">Ver Solicitud</a>
                    </div>
                </div>
                
                <div class="footer">
                    <p>© 2025 UNAH | Admisiones</p>
                </div>
            </div>
        </body>
        </html>
        ';
         // Texto plano para clientes de correo que no soporten HTML
        $altmess = "Hola {$nombre} {$apellido},\n\nTu registro ha sido exitoso. Aquí están los detalles:\n"
                 . "Documento: {$documento}\n"
                 . "Número de Solicitud: {$numSolicitud}\n"
                 . "Por favor, guarda esta información.\n"
                 . "Puedes ver tu solicitud en: https://registroisunah.xyz/admisiones.php";
    
        // Usar register_shutdown_function para ejecutar después de la respuesta
        register_shutdown_function(function() use ($correo, $nombre, $apellido, $subject, $message, $altmess) {
            // Crear una instancia de MailSender
            $emailService = new \Mail\MailSender();
            
            // Enviar el correo utilizando el método sendMail
            $result = $emailService->sendMail($correo, "{$nombre} {$apellido}", $subject, $message, $altmess);
            
            if (!$result) {
                error_log("Error al enviar el correo a {$correo}");
            }
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
     * Obtiene la lista de los aspirantes admitidos con carreras aprobadas en formato CSV
     * @param resource $file El manejador de archivo para escribir los resultados en el CSV
     */
    public function exportarAspirantesAdmitidosCSV($file) {
        // Consulta SQL para obtener los aspirantes admitidos con estado_aspirante_id
        // Incluye solo las carreras aprobadas y retorna solo la carrera en la que aprobaron
        $sql = "
        SELECT 
            a.nombre, 
            a.apellido, 
            a.documento, 
            a.correo, 
            a.telefono, 
            a.centro_id,
            -- Carrera principal
            cp.nombre AS carrera_principal,
            -- Carrera secundaria (si existe)
            cs.nombre AS carrera_secundaria
        FROM Aspirante a
        INNER JOIN Carrera cp ON a.carrera_principal_id = cp.carrera_id
        LEFT JOIN Carrera cs ON a.carrera_secundaria_id = cs.carrera_id
        INNER JOIN EstadoAspirante ea ON a.estado_aspirante_id = ea.estado_aspirante_id
        WHERE ea.nombre = 'ADMITIDO'";

        // Ejecutar la consulta
        $result = $this->conn->query($sql);

        if (!$result) {
            throw new Exception("Error en la consulta: " . $this->conn->error);
        }

        // Escribir la cabecera del CSV
        fputcsv($file, [
            'nombre',
            'apellido',
            'documento',
            'correo',
            'telefono',
            'centro_id',
            'carrera_principal',
            'carrera_secundaria'
        ]);

        // Escribir los datos de los aspirantes
        while ($row = $result->fetch_assoc()) {
            fputcsv($file, $row);
        }
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
     * Obtiene una solicitud pendiente o corregida SIN asignarla a ningún revisor
     * 
     * @return array|null Datos de la solicitud o NULL si no hay pendientes
     * @throws Exception Si ocurre un error en la consulta
     * @author Jose Vargas
     * @version 1.2
     */
    public function obtenerYAsignarSolicitud($revisor_id) {
        try {
            // Consulta optimizada para obtener solicitudes y liberarlas después de 5 minutos
            $sql = "SELECT 
                        aspirante_id, 
                        nombre, 
                        apellido, 
                        documento, 
                        telefono, 
                        correo, 
                        foto, 
                        fotodni, 
                        numSolicitud, 
                        carrera_principal_id, 
                        carrera_secundaria_id, 
                        centro_id, 
                        certificado_url, 
                        estado_aspirante_id, 
                        fecha_solicitud, 
                        revisor_id, 
                        fecha_asignacion
                    FROM Aspirante
                    WHERE (revisor_id IS NULL OR 
                        (revisor_id IS NOT NULL AND TIMESTAMPDIFF(SECOND, fecha_asignacion, NOW()) > 60)) 
                    AND estado_aspirante_id IN (SELECT estado_aspirante_id FROM EstadoAspirante WHERE nombre IN ('PENDIENTE', 'CORREGIDO_PENDIENTE'))
                    ORDER BY fecha_solicitud ASC
                    LIMIT 1";
    
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error en preparación de consulta: " . $this->conn->error);
            }
    
            $stmt->execute();
            $result = $stmt->get_result();
            $solicitud = $result->fetch_assoc();
            $stmt->close();
            
            // Si no hay solicitud disponible
            return $solicitud ?: null;
    
        } catch (Exception $e) {
            throw new Exception("Error al obtener solicitud: " . $e->getMessage());
        }
    }
    
    public function asignarRevisor($aspirante_id, $revisor_id) {
        try {
            // Asignar un revisor a una solicitud
            $sql = "UPDATE Aspirante 
                    SET revisor_id = ?, fecha_asignacion = NOW() 
                    WHERE aspirante_id = ? AND (revisor_id IS NULL OR TIMESTAMPDIFF(SECOND, fecha_asignacion, NOW()) > 60)";
    
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error preparando la consulta: " . $this->conn->error);
            }
    
            $stmt->bind_param("ii", $revisor_id, $aspirante_id);
            $stmt->execute();
    
            if ($stmt->affected_rows > 0) {
                $stmt->close();
                return true;  // Se asignó correctamente
            } else {
                $stmt->close();
                return false;  // No se pudo asignar
            }
    
        } catch (Exception $e) {
            throw new Exception("Error al asignar revisor: " . $e->getMessage());
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
     * Reenvía el correo de confirmación a un aspirante usando su correo electrónico.
     * 
     * @param string $correo Correo electrónico del aspirante.
     * @return bool True si el correo se envió correctamente.
     * @throws Exception Si no se encuentra el aspirante o falla el envío.
     */
    public function reenviarCorreoPorEmail($correo) {
        // 1. Buscar al aspirante por correo
        $query = "SELECT nombre, apellido, documento, numSolicitud, correo 
                FROM Aspirante 
                WHERE correo = ?";
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $this->conn->error);
        }

        $stmt->bind_param("s", $correo);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }

        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("No se encontró ningún aspirante con el correo: $correo");
        }
        
        $aspirante = $result->fetch_assoc();
        $stmt->close();
        
        // 2. Enviar correo con todos los datos
        return $this->enviarCorreo(
            $aspirante['nombre'],
            $aspirante['apellido'],
            $aspirante['documento'],
            $aspirante['numSolicitud'],
            $aspirante['correo'] // Ahora usamos el correo de la BD por seguridad
        );
    }

     /**
     * Obtiene el resultado de un aspirante mediante el documento
     */
    public function obtenerAspiranteResultado($documento) {
        $query = "SELECT a.*, 
                    cp.nombre as carrera_principal_nombre,
                    cs.nombre as carrera_secundaria_nombre,
                    a.correo  
                FROM Aspirante a
                LEFT JOIN Carrera cp ON a.carrera_principal_id = cp.carrera_id
                LEFT JOIN Carrera cs ON a.carrera_secundaria_id = cs.carrera_id
                WHERE a.documento = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $documento);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $aspirante = $result->fetch_assoc();

        // Verifica si el correo está disponible
        if (empty($aspirante['correo'])) {
            throw new Exception("Correo del aspirante no disponible para {$aspirante['nombre']} {$aspirante['apellido']}");
        }

        return $aspirante;
    }

    /**
     * Obtiene el ID de tipo de examen por nombre usando SP
     */
    public function obtenerTipoExamenId($nombre_examen) {
        $query = "CALL SP_obtener_tipo_examen(?, @tipo_examen_id, @nota_minima)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $nombre_examen);
        $stmt->execute();
        
        $result = $this->conn->query("SELECT @tipo_examen_id as tipo_examen_id, @nota_minima as nota_minima");
        return $result->fetch_assoc();
    }

    /**
     * Registra el resultado de un examen usando SP
     */
    public function registrarResultadoExamen($aspirante_id, $tipo_examen_id, $carrera_id, $nota) {
        $query = "CALL SP_registrar_resultado_examen(?, ?, ?, ?, @resultado_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iiid", $aspirante_id, $tipo_examen_id, $carrera_id, $nota);
        $stmt->execute();
        
        $result = $this->conn->query("SELECT @resultado_id as resultado_id");
        $row = $result->fetch_assoc();
        
        return $row['resultado_id'];
    }

    /**
     * Obtiene una carrera por nombre
     */
    public function obtenerCarreraPorNombre($nombre_carrera) {
        $query = "SELECT carrera_id, nombre FROM Carrera WHERE nombre = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $nombre_carrera);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Obtiene todos los exámenes de un aspirante para el correo
     */
    public function obtenerExamenesAspirante($aspirante_id) {
        $query = "CALL SP_obtener_examenes_aspirante(?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $aspirante_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Evalúa si un aspirante aprobó una carrera usando SP
     */
    public function verificarAprobacionCarrera($aspirante_id, $carrera_id) {
        $query = "CALL SP_evaluar_aprobacion_carrera(?, ?, @aprobado)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $aspirante_id, $carrera_id);
        $stmt->execute();
        
        $result = $this->conn->query("SELECT @aprobado as aprobado");
        $row = $result->fetch_assoc();
        
        return (bool)$row['aprobado'];
    }

    /**
     * Verifica si un examen pertenece a una carrera usando SP
     */
    public function esExamenDeCarrera($tipo_examen_id, $carrera_id) {
        $query = "CALL SP_es_examen_de_carrera(?, ?, @pertenece)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $tipo_examen_id, $carrera_id);
        $stmt->execute();
        
        $result = $this->conn->query("SELECT @pertenece as pertenece");
        $row = $result->fetch_assoc();
        
        return (bool)$row['pertenece'];
    }

    /**
     * Actualiza el estado de la carrera usando SP
     */
    public function actualizarEstadoCarrera($aspirante_id, $carrera_id, $aprobado) {
        $query = "CALL SP_actualizar_estado_carrera(?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $aprobado_int = $aprobado ? 1 : 0;
        $stmt->bind_param("iii", $aspirante_id, $carrera_id, $aprobado_int);
        return $stmt->execute();
    }

    public function enviarCorreoResultados($aspirante, $resultadosExamenes, $aprobado_principal, $aprobado_secundaria) {
        $subject = "Resultado Detallado de Exámenes de Admisión";
    
        // Definir colores y estilos
        $colorPrimario = "#3498db";
        $colorSecundario = "#2c3e50";
        $colorExito = "#2ecc71";
        $colorError = "#e74c3c";
        $colorFondo = "#f9f9f9";
        $colorBorde = "#dddddd";
        
        // Inicio del HTML con estilos CSS
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Resultados de Admisión</title>
            <style>
                body {
                    font-family: 'Helvetica Neue', Arial, sans-serif;
                    line-height: 1.6;
                    color: #333333;
                    background-color: #ffffff;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: {$colorFondo};
                    border-radius: 8px;
                    border: 1px solid {$colorBorde};
                }
                .header {
                    background-color: {$colorPrimario};
                    color: white;
                    padding: 20px;
                    text-align: center;
                    border-radius: 6px 6px 0 0;
                    margin: -20px -20px 20px -20px;
                }
                h1, h2, h3, h4 {
                    color: {$colorSecundario};
                    margin-top: 20px;
                    margin-bottom: 10px;
                }
                .header h1 {
                    color: white;
                    margin: 0;
                }
                .carrera {
                    margin: 20px 0;
                    padding: 15px;
                    background-color: white;
                    border-radius: 6px;
                    border-left: 5px solid {$colorPrimario};
                    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                }
                .resultado-lista {
                    list-style-type: none;
                    padding: 0;
                    margin: 15px 0;
                }
                .resultado-item {
                    padding: 10px;
                    margin-bottom: 8px;
                    background-color: white;
                    border-radius: 4px;
                    border: 1px solid {$colorBorde};
                }
                .aprobado {
                    color: {$colorExito};
                    font-weight: bold;
                }
                .no-aprobado {
                    color: {$colorError};
                    font-weight: bold;
                }
                .resultado-final {
                    margin: 15px 0;
                    padding: 12px;
                    text-align: center;
                    font-size: 18px;
                    font-weight: bold;
                    border-radius: 4px;
                }
                .aprobado-bg {
                    background-color: #e8f8f5;
                    border: 1px solid {$colorExito};
                    color: {$colorExito};
                }
                .no-aprobado-bg {
                    background-color: #fdedec;
                    border: 1px solid {$colorError};
                    color: {$colorError};
                }
                .mensaje-final {
                    padding: 15px;
                    margin-top: 20px;
                    text-align: center;
                    background-color: white;
                    border-radius: 6px;
                    border: 1px solid {$colorBorde};
                }
                .footer {
                    margin-top: 30px;
                    text-align: center;
                    font-size: 12px;
                    color: #777777;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Resultados de Exámenes de Admisión</h1>
                </div>
                
                <p>Hola <strong>{$aspirante['nombre']} {$aspirante['apellido']}</strong>,</p>
                
                <p>A continuación encontrarás los resultados detallados de tus exámenes de admisión:</p>";
        
        // Filtrar exámenes únicos por carrera
        $examenes_principal = [];
        $examenes_secundaria = [];
        
        foreach ($resultadosExamenes as $examen) {
            // Para carrera principal
            if ($examen['carrera_id'] == $aspirante['carrera_principal_id']) {
                $examenes_principal[$examen['tipo_examen_id']] = $examen;
            }
            // Para carrera secundaria (si existe)
            if ($aspirante['carrera_secundaria_id'] && $examen['carrera_id'] == $aspirante['carrera_secundaria_id']) {
                $examenes_secundaria[$examen['tipo_examen_id']] = $examen;
            }
        }
        
        // Mostrar resultados para carrera principal
        $message .= "
                <div class='carrera'>
                    <h3>Carrera Principal: {$aspirante['carrera_principal_nombre']}</h3>
                    <ul class='resultado-lista'>";
        
        foreach ($examenes_principal as $examen) {
            $clase_resultado = $examen['aprobado'] ? 'aprobado' : 'no-aprobado';
            $texto_resultado = $examen['aprobado'] ? 'APROBADO' : 'NO APROBADO';
            
            $message .= "
                        <li class='resultado-item'>
                            <strong>{$examen['tipo_examen']}:</strong> {$examen['nota']} 
                            <small>(Nota mínima: {$examen['nota_minima']})</small> - 
                            <span class='{$clase_resultado}'>{$texto_resultado}</span>
                        </li>";
        }
        
        $message .= "
                    </ul>";
        
        $clase_resultado_final = $aprobado_principal ? 'aprobado-bg' : 'no-aprobado-bg';
        $texto_resultado_final = $aprobado_principal ? 'APROBADO' : 'NO APROBADO';
        
        $message .= "
                    <div class='resultado-final {$clase_resultado_final}'>
                        Resultado final: {$texto_resultado_final}
                    </div>
                </div>";
        
        // Mostrar resultados para carrera secundaria (si aplica)
        if ($aspirante['carrera_secundaria_id'] && !empty($examenes_secundaria)) {
            $message .= "
                <div class='carrera'>
                    <h3>Carrera Secundaria: {$aspirante['carrera_secundaria_nombre']}</h3>
                    <ul class='resultado-lista'>";
            
            foreach ($examenes_secundaria as $examen) {
                $clase_resultado = $examen['aprobado'] ? 'aprobado' : 'no-aprobado';
                $texto_resultado = $examen['aprobado'] ? 'APROBADO' : 'NO APROBADO';
                
                $message .= "
                        <li class='resultado-item'>
                            <strong>{$examen['tipo_examen']}:</strong> {$examen['nota']} 
                            <small>(Nota mínima: {$examen['nota_minima']})</small> - 
                            <span class='{$clase_resultado}'>{$texto_resultado}</span>
                        </li>";
            }
            
            $message .= "
                    </ul>";
            
            $clase_resultado_final = $aprobado_secundaria ? 'aprobado-bg' : 'no-aprobado-bg';
            $texto_resultado_final = $aprobado_secundaria ? 'APROBADO' : 'NO APROBADO';
            
            $message .= "
                    <div class='resultado-final {$clase_resultado_final}'>
                        Resultado final: {$texto_resultado_final}
                    </div>
                </div>";
        }
        
        // Mensaje final
        if ($aprobado_principal || $aprobado_secundaria) {
            $message .= "
                <div class='mensaje-final' style='background-color: #e8f8f5; border-color: {$colorExito};'>
                    <p><strong>¡Felicidades!</strong> Has aprobado al menos una de tus carreras seleccionadas.</p>
                    <p>Recibirás un correo posterior con tus credenciales de acceso.</p>
                </div>";
        } else {
            $message .= "
                <div class='mensaje-final' style='background-color: #fdedec; border-color: {$colorError};'>
                    <p>Lamentablemente no has aprobado ninguna de las carreras seleccionadas.</p>
                    <p>Si tienes alguna duda, puedes comunicarte con el departamento de admisiones.</p>
                </div>";
        }
        
        // Pie de página
        $message .= "
                <div class='footer'>
                    <p>Este es un correo automático. Por favor no responda a este mensaje.</p>
                    <p>© " . date('Y') . " Sistema de Admisiones Universitarias</p>
                </div>
            </div>
        </body>
        </html>";
        
        // Versión alternativa para correo en texto plano
        $altmess = "Resultados de exámenes de admisión\n\n";
        $altmess .= "Hola {$aspirante['nombre']} {$aspirante['apellido']},\n\n";
        $altmess .= "Carrera Principal: {$aspirante['carrera_principal_nombre']}\n";
        
        foreach ($examenes_principal as $examen) {
            $aprobado = $examen['aprobado'] ? 'APROBADO' : 'NO APROBADO';
            $altmess .= "- {$examen['tipo_examen']}: {$examen['nota']} (Mínima: {$examen['nota_minima']}) - {$aprobado}\n";
        }
        
        $altmess .= "Resultado final: " . ($aprobado_principal ? "APROBADO" : "NO APROBADO") . "\n\n";
        
        if ($aspirante['carrera_secundaria_id']) {
            $altmess .= "Carrera Secundaria: {$aspirante['carrera_secundaria_nombre']}\n";
            
            foreach ($examenes_secundaria as $examen) {
                $aprobado = $examen['aprobado'] ? 'APROBADO' : 'NO APROBADO';
                $altmess .= "- {$examen['tipo_examen']}: {$examen['nota']} (Mínima: {$examen['nota_minima']}) - {$aprobado}\n";
            }
            
            $altmess .= "Resultado final: " . ($aprobado_secundaria ? "APROBADO" : "NO APROBADO") . "\n\n";
        }
        
        if ($aprobado_principal || $aprobado_secundaria) {
            $altmess .= "¡Felicidades! Has aprobado al menos una de tus carreras seleccionadas. ";
            $altmess .= "Recibirás un correo posterior con tus credenciales de acceso.\n";
        } else {
            $altmess .= "Lamentablemente no has aprobado ninguna de las carreras seleccionadas.\n";
            $altmess .= "Si tienes alguna duda, puedes comunicarte con el departamento de admisiones.\n";
        }
    
        // Verificar si el correo es válido antes de enviar
        if (empty($aspirante['correo'])) {
            throw new Exception("Correo del aspirante no disponible");
        }
    
        // Enviar el correo
        // Usar register_shutdown_function para ejecutar después de la respuesta
        register_shutdown_function(function() use ($aspirante, $subject, $message, $altmess) {
            // Crear una instancia de MailSender
            $emailService = new \Mail\MailSender();
            
            // Enviar el correo utilizando el método sendMail
            $result = $emailService->sendMail($aspirante['correo'], "{$aspirante['nombre']} {$aspirante['apellido']}", $subject, $message, $altmess);
            
            if (!$result) {
                error_log("Error al enviar el correo a {$aspirante['correo']}");
            }
        });
    }

    /**
     * Obtiene el ID de un estado por su nombre
     */
    private function obtenerIdEstado($nombreEstado) {
        $query = "SELECT estado_id FROM EstadoCorreo WHERE nombre = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $nombreEstado);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if (!$row) {
            throw new Exception("Estado '$nombreEstado' no encontrado en la tabla EstadoCorreo");
        }
        
        return $row['estado_id'];
    }

    /**
     * Guarda en una tabla para despues enviar el correo mediante un Cron Job
     */
    public function guardarParaEnvioProgramado($aspirante, $resultadosExamenes, $aprobado_principal, $aprobado_secundaria) {
        $subject = "Resultado Detallado de Exámenes de Admisión";
    
        // Definir colores y estilos
        $colorPrimario = "#3498db";
        $colorSecundario = "#2c3e50";
        $colorExito = "#2ecc71";
        $colorError = "#e74c3c";
        $colorFondo = "#f9f9f9";
        $colorBorde = "#dddddd";
        
        // Inicio del HTML con estilos CSS
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Resultados de Admisión</title>
            <style>
                body {
                    font-family: 'Helvetica Neue', Arial, sans-serif;
                    line-height: 1.6;
                    color: #333333;
                    background-color: #ffffff;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: {$colorFondo};
                    border-radius: 8px;
                    border: 1px solid {$colorBorde};
                }
                .header {
                    background-color: {$colorPrimario};
                    color: white;
                    padding: 20px;
                    text-align: center;
                    border-radius: 6px 6px 0 0;
                    margin: -20px -20px 20px -20px;
                }
                h1, h2, h3, h4 {
                    color: {$colorSecundario};
                    margin-top: 20px;
                    margin-bottom: 10px;
                }
                .header h1 {
                    color: white;
                    margin: 0;
                }
                .carrera {
                    margin: 20px 0;
                    padding: 15px;
                    background-color: white;
                    border-radius: 6px;
                    border-left: 5px solid {$colorPrimario};
                    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                }
                .resultado-lista {
                    list-style-type: none;
                    padding: 0;
                    margin: 15px 0;
                }
                .resultado-item {
                    padding: 10px;
                    margin-bottom: 8px;
                    background-color: white;
                    border-radius: 4px;
                    border: 1px solid {$colorBorde};
                }
                .aprobado {
                    color: {$colorExito};
                    font-weight: bold;
                }
                .no-aprobado {
                    color: {$colorError};
                    font-weight: bold;
                }
                .resultado-final {
                    margin: 15px 0;
                    padding: 12px;
                    text-align: center;
                    font-size: 18px;
                    font-weight: bold;
                    border-radius: 4px;
                }
                .aprobado-bg {
                    background-color: #e8f8f5;
                    border: 1px solid {$colorExito};
                    color: {$colorExito};
                }
                .no-aprobado-bg {
                    background-color: #fdedec;
                    border: 1px solid {$colorError};
                    color: {$colorError};
                }
                .mensaje-final {
                    padding: 15px;
                    margin-top: 20px;
                    text-align: center;
                    background-color: white;
                    border-radius: 6px;
                    border: 1px solid {$colorBorde};
                }
                .footer {
                    margin-top: 30px;
                    text-align: center;
                    font-size: 12px;
                    color: #777777;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Resultados de Exámenes de Admisión</h1>
                </div>
                
                <p>Hola <strong>{$aspirante['nombre']} {$aspirante['apellido']}</strong>,</p>
                
                <p>A continuación encontrarás los resultados detallados de tus exámenes de admisión:</p>";
        
        // Filtrar exámenes únicos por carrera
        $examenes_principal = [];
        $examenes_secundaria = [];
        
        foreach ($resultadosExamenes as $examen) {
            // Para carrera principal
            if ($examen['carrera_id'] == $aspirante['carrera_principal_id']) {
                $examenes_principal[$examen['tipo_examen_id']] = $examen;
            }
            // Para carrera secundaria (si existe)
            if ($aspirante['carrera_secundaria_id'] && $examen['carrera_id'] == $aspirante['carrera_secundaria_id']) {
                $examenes_secundaria[$examen['tipo_examen_id']] = $examen;
            }
        }
        
        // Mostrar resultados para carrera principal
        $message .= "
                <div class='carrera'>
                    <h3>Carrera Principal: {$aspirante['carrera_principal_nombre']}</h3>
                    <ul class='resultado-lista'>";
        
        foreach ($examenes_principal as $examen) {
            $clase_resultado = $examen['aprobado'] ? 'aprobado' : 'no-aprobado';
            $texto_resultado = $examen['aprobado'] ? 'APROBADO' : 'NO APROBADO';
            
            $message .= "
                        <li class='resultado-item'>
                            <strong>{$examen['tipo_examen']}:</strong> {$examen['nota']} 
                            <small>(Nota mínima: {$examen['nota_minima']})</small> - 
                            <span class='{$clase_resultado}'>{$texto_resultado}</span>
                        </li>";
        }
        
        $message .= "
                    </ul>";
        
        $clase_resultado_final = $aprobado_principal ? 'aprobado-bg' : 'no-aprobado-bg';
        $texto_resultado_final = $aprobado_principal ? 'APROBADO' : 'NO APROBADO';
        
        $message .= "
                    <div class='resultado-final {$clase_resultado_final}'>
                        Resultado final: {$texto_resultado_final}
                    </div>
                </div>";
        
        // Mostrar resultados para carrera secundaria (si aplica)
        if ($aspirante['carrera_secundaria_id'] && !empty($examenes_secundaria)) {
            $message .= "
                <div class='carrera'>
                    <h3>Carrera Secundaria: {$aspirante['carrera_secundaria_nombre']}</h3>
                    <ul class='resultado-lista'>";
            
            foreach ($examenes_secundaria as $examen) {
                $clase_resultado = $examen['aprobado'] ? 'aprobado' : 'no-aprobado';
                $texto_resultado = $examen['aprobado'] ? 'APROBADO' : 'NO APROBADO';
                
                $message .= "
                        <li class='resultado-item'>
                            <strong>{$examen['tipo_examen']}:</strong> {$examen['nota']} 
                            <small>(Nota mínima: {$examen['nota_minima']})</small> - 
                            <span class='{$clase_resultado}'>{$texto_resultado}</span>
                        </li>";
            }
            
            $message .= "
                    </ul>";
            
            $clase_resultado_final = $aprobado_secundaria ? 'aprobado-bg' : 'no-aprobado-bg';
            $texto_resultado_final = $aprobado_secundaria ? 'APROBADO' : 'NO APROBADO';
            
            $message .= "
                    <div class='resultado-final {$clase_resultado_final}'>
                        Resultado final: {$texto_resultado_final}
                    </div>
                </div>";
        }
        
        // Mensaje final
        if ($aprobado_principal || $aprobado_secundaria) {
            $message .= "
                <div class='mensaje-final' style='background-color: #e8f8f5; border-color: {$colorExito};'>
                    <p><strong>¡Felicidades!</strong> Has aprobado al menos una de tus carreras seleccionadas.</p>
                    <p>Recibirás un correo posterior con tus credenciales de acceso.</p>
                </div>";
        } else {
            $message .= "
                <div class='mensaje-final' style='background-color: #fdedec; border-color: {$colorError};'>
                    <p>Lamentablemente no has aprobado ninguna de las carreras seleccionadas.</p>
                    <p>Si tienes alguna duda, puedes comunicarte con el departamento de admisiones.</p>
                </div>";
        }
        
        // Pie de página
        $message .= "
                <div class='footer'>
                    <p>Este es un correo automático. Por favor no responda a este mensaje.</p>
                    <p>© " . date('Y') . " Sistema de Admisiones Universitarias</p>
                </div>
            </div>
        </body>
        </html>";
        
        // Versión alternativa para correo en texto plano
        $altmess = "Resultados de exámenes de admisión\n\n";
        $altmess .= "Hola {$aspirante['nombre']} {$aspirante['apellido']},\n\n";
        $altmess .= "Carrera Principal: {$aspirante['carrera_principal_nombre']}\n";
        
        foreach ($examenes_principal as $examen) {
            $aprobado = $examen['aprobado'] ? 'APROBADO' : 'NO APROBADO';
            $altmess .= "- {$examen['tipo_examen']}: {$examen['nota']} (Mínima: {$examen['nota_minima']}) - {$aprobado}\n";
        }
        
        $altmess .= "Resultado final: " . ($aprobado_principal ? "APROBADO" : "NO APROBADO") . "\n\n";
        
        if ($aspirante['carrera_secundaria_id']) {
            $altmess .= "Carrera Secundaria: {$aspirante['carrera_secundaria_nombre']}\n";
            
            foreach ($examenes_secundaria as $examen) {
                $aprobado = $examen['aprobado'] ? 'APROBADO' : 'NO APROBADO';
                $altmess .= "- {$examen['tipo_examen']}: {$examen['nota']} (Mínima: {$examen['nota_minima']}) - {$aprobado}\n";
            }
            
            $altmess .= "Resultado final: " . ($aprobado_secundaria ? "APROBADO" : "NO APROBADO") . "\n\n";
        }
        
        if ($aprobado_principal || $aprobado_secundaria) {
            $altmess .= "¡Felicidades! Has aprobado al menos una de tus carreras seleccionadas. ";
            $altmess .= "Recibirás un correo posterior con tus credenciales de acceso.\n";
        } else {
            $altmess .= "Lamentablemente no has aprobado ninguna de las carreras seleccionadas.\n";
            $altmess .= "Si tienes alguna duda, puedes comunicarte con el departamento de admisiones.\n";
        }
    
        try {
            // Obtener ID del estado PENDIENTE
            $estadoPendienteId = $this->obtenerIdEstado('PENDIENTE');
            
            // Crear variable para nombre completo
            $nombreCompleto = $aspirante['nombre'] . ' ' . $aspirante['apellido'];
            
            // Insertar en la tabla de cola de correos
            $query = "INSERT INTO ColaCorreosAspirantes (
                        destinatario, 
                        nombre_destinatario,
                        asunto, 
                        cuerpo_html, 
                        cuerpo_texto, 
                        fecha_creacion,
                        estado_id
                    ) VALUES (?, ?, ?, ?, ?, NOW(), ?)";
            
            $stmt = $this->conn->prepare($query);
            
            if ($stmt === false) {
                throw new Exception("Error al preparar la consulta: " . $this->conn->error);
            }
            
            $stmt->bind_param("sssssi", 
                $aspirante['correo'],
                $nombreCompleto,  // Usamos la variable previamente creada
                $subject,
                $message,
                $altmess,
                $estadoPendienteId
            );
            
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Error al guardar correo en cola: " . $e->getMessage());
            return false;
        }
    }
}
?>
