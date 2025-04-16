<?php
require_once __DIR__ . '/../modules/config/DataBase.php';
require_once __DIR__ . '/../mail/mail_sender.php';

/**
 * Clase Estudiante
 *
 * Maneja operaciones relacionadas con el estudiante.
 *
 * @package Models
 * @author JOse Vargas
 * @version 1.0
 * 
 */
class Estudiante {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtiene los docentes de las clases en las que está matriculado el estudiante
     * 
     * @param int $estudianteId
     * @return array
     */

    /*
    Ejemplo de respuesta
        {
    "success": true,
    "data": [
        {
            "clase_id": 15,
            "codigo_clase": "MAT-101",
            "nombre_clase": "Matemáticas Básicas",
            "docente_id": 23,
            "nombre_docente": "María",
            "apellido_docente": "González",
            "correo_docente": "maria.gonzalez@universidad.edu"
        },
        {
            "clase_id": 18,
            "codigo_clase": "FIS-201",
            "nombre_clase": "Física Moderna",
            "docente_id": 45,
            "nombre_docente": "Carlos",
            "apellido_docente": "Martínez",
            "correo_docente": "carlos.martinez@universidad.edu"
        }
    ]
}
    */
    public function obtenerDocentesDeClases($estudianteId) {
        $sql = "SELECT 
                    c.clase_id,
                    c.codigo AS codigo_clase,
                    c.nombre AS nombre_clase,
                    d.docente_id,
                    d.nombre AS nombre_docente,
                    d.apellido AS apellido_docente,
                    d.correo AS correo_docente
                FROM Matricula m
                INNER JOIN Seccion s ON m.seccion_id = s.seccion_id
                INNER JOIN Clase c ON s.clase_id = c.clase_id
                INNER JOIN Docente d ON s.docente_id = d.docente_id
                WHERE m.estudiante_id = ?
                GROUP BY c.clase_id, d.docente_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $estudianteId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $docentes = [];
        
        while ($row = $result->fetch_assoc()) {
            $docentes[] = $row;
        }
        
        return $docentes;
    }


    /**
     * Obtiene el perfil del estudiante
     * 
     * @param int $estudianteId
     * @return array
     * @throws Exception
     * @author Jose Vargas
     * @version 1.3
     */


     /*

        SELECT 
			e.estudiante_id,
            e.nombre,
            e.apellido,
            e.identidad,
            e.correo_personal,
            e.telefono,
            e.direccion,
            e.indice_global,
            e.indice_periodo,
            c.nombre AS centro,
            u.username,
            GROUP_CONCAT(DISTINCT ca.nombre SEPARATOR ', ') AS carreras,
            GROUP_CONCAT(DISTINCT fe.ruta_foto SEPARATOR ', ') AS fotos,
            (SELECT COUNT(*) 
            FROM Solicitud s 
            WHERE s.estudiante_id = e.estudiante_id 
            AND s.estado_solicitud_id = 1) AS solicitudes_pendientes
        FROM Estudiante e
        INNER JOIN Usuario u ON e.usuario_id = u.usuario_id
        INNER JOIN Centro c ON e.centro_id = c.centro_id
        LEFT JOIN EstudianteCarrera ec ON e.estudiante_id = ec.estudiante_id
        LEFT JOIN Carrera ca ON ec.carrera_id = ca.carrera_id
        LEFT JOIN FotosEstudiante fe ON e.estudiante_id = fe.estudiante_id
        WHERE e.estudiante_id = ?
        GROUP BY e.estudiante_id

     */
    public function obtenerPerfilEstudiante($estudianteId) {

        $sql = "SELECT 
                e.estudiante_id,
                e.numero_cuenta,
                e.nombre,
                e.apellido,
                e.identidad,
                e.correo_personal,
                e.telefono,
                e.direccion,
                e.indice_global,
                e.indice_periodo,
                c.centro_id,
                c.nombre AS centro,
                u.username,
                GROUP_CONCAT(DISTINCT ca.carrera_id SEPARATOR ', ') AS carrerasid,
                GROUP_CONCAT(DISTINCT ca.nombre SEPARATOR ', ') AS carreras,
                GROUP_CONCAT(DISTINCT fe.ruta_foto SEPARATOR ', ') AS fotos,
                (
                    SELECT COUNT(*) 
                    FROM Solicitud s 
                    WHERE s.estudiante_id = e.estudiante_id 
                    AND s.estado_solicitud_id = 1
                ) AS solicitudes_pendientes
            FROM Estudiante e
            INNER JOIN Usuario u ON e.usuario_id = u.usuario_id
            INNER JOIN Centro c ON e.centro_id = c.centro_id
            LEFT JOIN EstudianteCarrera ec ON e.estudiante_id = ec.estudiante_id
            LEFT JOIN Carrera ca ON ec.carrera_id = ca.carrera_id
            LEFT JOIN FotosEstudiante fe ON e.estudiante_id = fe.estudiante_id
            WHERE e.estudiante_id = ?
            GROUP BY e.estudiante_id";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $estudianteId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Estudiante no encontrado");
        }
        
        return $result->fetch_assoc();
    }

    /**
     * Actualiza los datos del perfil del estudiante
     * 
     * @param int $estudianteId
     * @param array $datosActualizados
     * @return bool
     * @throws Exception
     * @author Jose Vargas
     * @version 1.0
     */
    public function actualizarPerfil($estudianteId, $datosActualizados) {
        // Validar campos permitidos para actualización
        $camposPermitidos = [
            'telefono', 
            'direccion', 
            'correo_personal'
        ];
        
        $camposActualizar = [];
        $valores = [];
        $tipos = '';
        
        foreach ($datosActualizados as $campo => $valor) {
            if (in_array($campo, $camposPermitidos)) {
                $camposActualizar[] = "$campo = ?";
                $valores[] = $valor;
                $tipos .= 's'; // Todos los campos permitidos son strings
            }
        }
        
        if (empty($camposActualizar)) {
            throw new Exception("No hay campos válidos para actualizar");
        }
        
        // Construir la consulta SQL
        $sql = "UPDATE Estudiante SET " . implode(', ', $camposActualizar) . " WHERE estudiante_id = ?";
        $valores[] = $estudianteId;
        $tipos .= 'i';
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($tipos, ...$valores);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar perfil: " . $stmt->error);
        }
        
        return true;
    }


    /**
     * Registra una evaluación de docente realizada por el estudiante
     * 
     * @param int $estudianteId
     * @param int $docenteId
     * @param int $periodoId
     * @param array $respuestas
     * @return bool
     * @throws Exception
     * @author Jose Vargas
     * @version 2.0
     */
    public function registrarEvaluacionDocente($estudianteId, $docenteId, $periodoId, $respuestas) {
        // 1. Insertar evaluación principal
        $sqlEvaluacion = "INSERT INTO EvaluacionDocente (
            docente_id, 
            estudiante_id, 
            periodo_academico_id, 
            fecha, 
            estado_evaluacion_id
        ) VALUES (?, ?, ?, NOW(), 1)";
        
        $stmt = $this->conn->prepare($sqlEvaluacion);
        $stmt->bind_param("iii", $docenteId, $estudianteId, $periodoId);
        $stmt->execute();
        $evaluacionId = $stmt->insert_id;

        // 2. Insertar respuestas individuales
        foreach ($respuestas as $preguntaId => $respuesta) {
            $sqlRespuesta = "INSERT INTO RespuestaEvaluacion (
                evaluacion_id, 
                pregunta_id, 
                respuesta
            ) VALUES (?, ?, ?)";

            $stmtResp = $this->conn->prepare($sqlRespuesta);
            $stmtResp->bind_param("iis", $evaluacionId, $preguntaId, $respuesta);
            $stmtResp->execute();
        }

        return true;
    }

    /**
     * Registra una solicitud de cambio de carrera
     * 
     * @param int $estudianteId
     * @param int $carreraActualId
     * @param int $carreraSolicitadaId
     * @param string $motivo
     * @return bool
     * @throws Exception
     * @author Jose Vargas
     * @version 1.0
     */
    public function solicitarCambioCarrera($estudianteId, $carreraActualId, $carreraSolicitadaId, $motivo = null) {
        $sql = "INSERT INTO Solicitud (
                    estudiante_id,
                    tipo_solicitud_id,
                    carrera_actual_id,
                    carrera_solicitada_id,
                    motivo_id,
                    fecha_solicitud,
                    estado_solicitud_id
                ) VALUES (
                    ?,
                    (SELECT tipo_solicitud_id FROM TipoSolicitud WHERE nombre = 'Cambio de Carrera'),
                    ?,
                    ?,
                    ?,
                    CURDATE(),
                    (SELECT estado_solicitud_id FROM EstadoSolicitud WHERE nombre = 'Pendiente')
                )";

        $stmt = $this->conn->prepare($sql);
        
        // Si no hay motivo, insertar NULL
        $motivoId = !empty($motivo) ? $motivo : null;
        
        $stmt->bind_param("iiii", 
            $estudianteId,
            $carreraActualId,
            $carreraSolicitadaId,
            $motivoId
        );

        if (!$stmt->execute()) {
            throw new Exception("Error al registrar solicitud: " . $stmt->error);
        }
        
        return true;
    }
    
    /**
     * Obtiene las carreras del estudiante
     * 
     * @param int $estudianteId
     * @return array
     */
    public function obtenerCarrerasEstudiante($estudianteId) {
        $sql = "SELECT c.carrera_id, c.nombre 
                FROM EstudianteCarrera ec
                INNER JOIN Carrera c ON ec.carrera_id = c.carrera_id
                WHERE ec.estudiante_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $estudianteId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    //Prueba

    /**
     * Crea un nuevo usuario con username formato: primerNombre.primerApellido + 3 dígitos
     * 
     * @param string $nombre Nombre completo del estudiante
     * @param string $apellido Apellido completo del estudiante
     * @return array Datos del usuario creado
     */
    public function crearUsuarioEstudiante($nombre, $apellido) {
        // Extraer solo el primer nombre y primer apellido
        $primerNombre = $this->extraerPrimerNombre($nombre);
        $primerApellido = $this->extraerPrimerApellido($apellido);
        
        // Generar username (ej: ruben.diaz123)
        $username = strtolower($primerNombre . '.' . $primerApellido . rand(100, 999));
        
        // Generar contraseña segura
        $password = $this->generarPasswordTemporal();
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insertar usuario
        $sql = "INSERT INTO Usuario (username, password) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $username, $hashedPassword);
        $stmt->execute();

        // Asignar rol de estudiante
        $usuario_id = $stmt->insert_id;
        $this->asignarRolEstudiante($usuario_id);

        return [
            'usuario_id' => $usuario_id,
            'username' => $username,
            'password' => $password
        ];
    }

    /**
     * Extrae el primer nombre de un nombre completo
     */
    private function extraerPrimerNombre($nombreCompleto) {
        $nombres = explode(' ', trim($nombreCompleto));
        return $nombres[0]; // Devuelve el primer elemento del array
    }

    /**
     * Extrae el primer apellido de un apellido completo
     */
    private function extraerPrimerApellido($apellidoCompleto) {
        $apellidos = explode(' ', trim($apellidoCompleto));
        return $apellidos[0]; // Devuelve el primer apellido
    }

    /**
     * Genera una contraseña temporal más segura
     */
    private function generarPasswordTemporal() {
        $prefix = 'Unah@'; // Prefijo institucional
        $random = bin2hex(random_bytes(2)); // 4 caracteres aleatorios
        return $prefix . $random; // Ej: Unah@a3f5
    }

    /**
     * Asigna el rol de estudiante a un usuario
     */
    private function asignarRolEstudiante($usuario_id) {
        $sql = "SELECT rol_id FROM Rol WHERE nombre = 'Estudiante' LIMIT 1";
        $result = $this->conn->query($sql);
        
        if ($result->num_rows > 0) {
            $rol_id = $result->fetch_assoc()['rol_id'];
            $stmt = $this->conn->prepare("INSERT INTO UsuarioRol (usuario_id, rol_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $usuario_id, $rol_id);
            $stmt->execute();
        } else {
            throw new Exception("Rol 'Estudiante' no encontrado");
        }
    }


    /**
     * Inserta el estudiante en la tabla Estudiante.
     *
     * @param int $usuario_id ID del usuario.
     * @param string $identidad Documento del estudiante.
     * @param string $nombre Nombre del estudiante.
     * @param string $apellido Apellido del estudiante.
     * @param string $correo Correo del estudiante.
     * @param string $telefono Teléfono del estudiante.
     * @param int $centro_id ID del centro.
     * @return array Datos del estudiante creado.
     */
    public function registrarEstudiante($usuario_id, $identidad, $nombre, $apellido, $correo, $telefono, $centro_id) {
        // Generar el número de cuenta único
        $numeroCuenta = $this->generarNumeroCuenta();

        // Verificar que el centro existe
        $sqlCheck = "SELECT centro_id FROM Centro WHERE centro_id = ?";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bind_param("i", $centro_id);
        $stmtCheck->execute();
        
        if ($stmtCheck->get_result()->num_rows == 0) {
            throw new Exception("El centro con ID $centro_id no existe");
        }

        // Consulta SQL corregida con todos los parámetros
        $sql = "INSERT INTO Estudiante 
                (usuario_id, identidad, nombre, apellido, correo_personal, telefono, direccion, centro_id, indice_global, indice_periodo, numero_cuenta) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        
        // Valores por defecto
        $direccion = 'No disponible';
        $indice_global = 100;
        $indice_periodo = 0;
        
        // Vincular todos los parámetros en el orden correcto
        $stmt->bind_param(
            "issssssiids", // Tipos de datos: i=entero, s=string, d=double
            $usuario_id,
            $identidad,
            $nombre,
            $apellido,
            $correo,
            $telefono,
            $direccion,
            $centro_id,
            $indice_global,
            $indice_periodo,
            $numeroCuenta
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Error al registrar estudiante: " . $stmt->error);
        }

        return [
            'estudiante_id' => $stmt->insert_id,
            'numero_cuenta' => $numeroCuenta
        ];
    }

    /**
     * Genera un número de cuenta único
     * 
     * @return string Número de cuenta generado
     */
    private function generarNumeroCuenta() {
        // Año actual
        $year = date("Y");
        
        // Generar 7 números aleatorios únicos
        $randomNumbers = '';
        while (strlen($randomNumbers) < 7) {
            $num = rand(0, 9);
            if (strpos($randomNumbers, (string)$num) === false) { // Asegura que el número no se repita
                $randomNumbers .= $num;
            }
        }

        // Concatenar el año con los 7 números aleatorios
        return $year . " " . $randomNumbers;
    }

    /**
     * Relaciona al estudiante con las carreras que tiene asignadas.
     *
     * @param int $estudiante_id ID del estudiante.
     * @param array $carreras Arreglo de IDs de carreras a asignar.
     */
    public function relacionarEstudianteConCarreras($estudiante_id, $carreras) {
        $sql = "INSERT INTO EstudianteCarrera (estudiante_id, carrera_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
    
        foreach ($carreras as $nombre_carrera) {
            // Buscar el ID de la carrera por nombre
            $sqlCarrera = "SELECT carrera_id FROM Carrera WHERE nombre = ?";
            $stmtCarrera = $this->conn->prepare($sqlCarrera);
            $stmtCarrera->bind_param("s", $nombre_carrera);
            $stmtCarrera->execute();
            $result = $stmtCarrera->get_result();
            
            if ($result->num_rows > 0) {
                $carrera_id = $result->fetch_assoc()['carrera_id'];
                $stmt->bind_param("ii", $estudiante_id, $carrera_id);
                $stmt->execute();
            } else {
                // Registrar error: carrera no encontrada
            }
        }
    }

    /**
     * Envía un correo con las credenciales del estudiante de forma clara
     * 
     * @param string $correo Correo de destino
     * @param string $nombre Nombre del estudiante
     * @param string $apellido Apellido del estudiante
     * @param string $username Nombre de usuario (sin nombre/apellido)
     * @param string $password Contraseña generada
     */
    public function enviarCorreoConCredenciales($correo, $nombre, $apellido, $username, $password, $numeroCuenta) {
        $nombreCompleto = trim("$nombre $apellido");
        $subject = 'Credenciales de Acceso al Sistema Universitario';
        
        // Versión HTML mejorada con CSS más atractivo
        $message = "
            <html>
            <head>
                <style>
                    body { 
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                        line-height: 1.6;
                        color: #333;
                        background-color: #f5f5f5;
                        margin: 0;
                        padding: 0;
                    }
                    .container {
                        max-width: 600px;
                        margin: 0 auto;
                        padding: 20px;
                        background-color: #ffffff;
                        border-radius: 8px;
                        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
                    }
                    .header {
                        text-align: center;
                        padding: 20px 0;
                        border-bottom: 2px solid #4a6fdc;
                    }
                    .header img {
                        max-height: 60px;
                        margin-bottom: 10px;
                    }
                    .header h1 {
                        color: #4a6fdc;
                        margin: 0;
                        font-size: 24px;
                    }
                    .content {
                        padding: 20px 0;
                    }
                    .card { 
                        background: #f8faff; 
                        border-left: 4px solid #4a6fdc;
                        border-radius: 4px; 
                        padding: 25px;
                        margin: 25px 0;
                        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
                    }
                    .credential-item { 
                        margin-bottom: 15px; 
                        padding-bottom: 15px; 
                        border-bottom: 1px solid #eaedf7;
                        display: flex;
                        align-items: center;
                    }
                    .credential-item:last-child {
                        border-bottom: none;
                        margin-bottom: 0;
                        padding-bottom: 0;
                    }
                    .label { 
                        font-weight: bold; 
                        color: #4a6fdc;
                        min-width: 150px;
                        display: inline-block;
                    }
                    .value {
                        font-family: 'Courier New', monospace;
                        padding: 5px 8px;
                        background-color: #f0f4ff;
                        border-radius: 3px;
                    }
                    .important { 
                        background-color: #fff5f5;
                        border-left: 4px solid #e53e3e;
                        color: #e53e3e; 
                        padding: 12px 15px;
                        margin-top: 20px;
                        border-radius: 4px;
                        font-weight: 500;
                    }
                    .button {
                        display: inline-block;
                        background-color: #4a6fdc;
                        color: white;
                        text-decoration: none;
                        padding: 12px 25px;
                        border-radius: 4px;
                        margin: 20px 0;
                        font-weight: bold;
                        text-align: center;
                        transition: background-color 0.3s;
                    }
                    .button:hover {
                        background-color: #3a5cbc;
                    }
                    .footer {
                        text-align: center;
                        padding-top: 20px;
                        border-top: 1px solid #eaedf7;
                        color: #666;
                        font-size: 14px;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Sistema de Gestión Universitaria</h1>
                    </div>
                    
                    <div class='content'>
                        <p>Estimado/a <strong>$nombreCompleto</strong>,</p>
                        
                        <p>Le damos la bienvenida al Sistema de Gestión Universitaria. A continuación, encontrará sus credenciales de acceso:</p>
                        
                        <div class='card'>
                            <div class='credential-item'>
                                <span class='label'>Nombre completo:</span>
                                <span class='value'>$nombreCompleto</span>
                            </div>
                            <div class='credential-item'>
                                <span class='label'>Usuario:</span>
                                <span class='value'>$username</span>
                            </div>
                            <div class='credential-item'>
                                <span class='label'>Contraseña temporal:</span>
                                <span class='value'>$password</span>
                            </div>
                            <div class='credential-item'>
                                <span class='label'>Número de cuenta:</span>
                                <span class='value'>$numeroCuenta</span>
                            </div>
                        </div>
                        
                        <div class='important'>
                            <strong>IMPORTANTE:</strong> Por seguridad, debe cambiar esta contraseña después de su primer acceso al sistema.
                        </div>
                        
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='https://registroisunah.xyz' class='button'>Acceder al Portal Estudiantil</a>
                        </div>
                        
                        <p>Si tiene alguna duda o inconveniente, no dude en contactar con nuestro equipo de soporte técnico.</p>
                    </div>
                    
                    <div class='footer'>
                        <p>Atentamente,<br><strong>Departamento de Registro</strong></p>
                        <p>© 2025 Universidad. Todos los derechos reservados.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        // Versión texto plano clara
        $altMessage = "Credenciales de Acceso - $nombreCompleto\n\n"
                    . "Nombre completo: $nombreCompleto\n"
                    . "Usuario del sistema: $username\n"
                    . "Contraseña temporal: $password\n"
                    . "Numero de Cuenta: $numeroCuenta\n\n"
                    . "IMPORTANTE: Debe cambiar esta contraseña después de su primer acceso.\n\n"
                    . "Acceso al sistema: https://registroisunah.xyz\n\n"
                    . "Atentamente,\nDepartamento de Registro";
    
        // Envío asíncrono
        register_shutdown_function(function() use ($correo, $nombreCompleto, $subject, $message, $altMessage) {
            // Crear una instancia de la clase MailSender
            $emailService = new \Mail\MailSender();
            
            // Enviar el correo utilizando el método sendMail
            $result = $emailService->sendMail($correo, $nombreCompleto, $subject, $message, $altMessage);
            
            // Verificar si el correo fue enviado exitosamente
            if (!$result) {
                error_log("Error al enviar el correo a $correo");
            }
        });
    }

    /**
     * Obtiene el historial de un estudiante basado en su ID.
     * 
     * @param int $estudiante_id ID del estudiante para el cual se obtiene el historial.
     * @return array El historial del estudiante.
     * @throws Exception Si ocurre un error en la consulta.
     */
    public function obtenerHistorialEstudiante($estudiante_id) {
        try {
            // Consulta SQL para obtener el historial del estudiante
            $sql = "
                SELECT 
                    c.codigo AS codigo,
                    c.nombre AS asignatura,
                    c.creditos AS creditos,
                    DATE_FORMAT(s.hora_inicio, '%H%i') AS seccion,
                    p.anio,
					p.numero_periodo_id,
                    h.calificacion,
                    ec.nombre AS observacion
                FROM HistorialEstudiante h
                JOIN Seccion s ON h.seccion_id = s.seccion_id
                JOIN Clase c ON s.clase_id = c.clase_id
                JOIN PeriodoAcademico p ON s.periodo_academico_id = p.periodo_academico_id
                JOIN EstadoCurso ec ON h.estado_curso_id = ec.estado_curso_id
                WHERE h.estudiante_id = ?
            ";
            
            // Preparar y ejecutar la consulta
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $estudiante_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Si no hay resultados, lanzar una excepción
            if ($result->num_rows == 0) {
                throw new Exception("No se encontraron registros para este estudiante.");
            }

            // Obtener los resultados
            $historial = [];
            while ($row = $result->fetch_assoc()) {
                $historial[] = $row;
            }

            $stmt->close();
            return $historial;
        } catch (Exception $e) {
            throw new Exception("Error al obtener el historial del estudiante: " . $e->getMessage());
        }
    }

    /**
     * Obtiene los estudiantes matriculados en una sección específica.
     *
     * @param int $seccion_id ID de la sección.
     * @return array Lista de estudiantes matriculados en la sección.
     * @throws Exception Si ocurre un error en la consulta.
     */
    public function obtenerEstudiantesPorSeccion($seccion_id) {
        $sql = "
            SELECT
                e.estudiante_id,
                e.nombre,
                e.apellido,
                e.numero_cuenta,
                e.correo_personal,
                GROUP_CONCAT(c.nombre ORDER BY c.nombre ASC) AS carreras,  -- Obtener las carreras asociadas
                GROUP_CONCAT(c.carrera_id ORDER BY c.carrera_id ASC) AS carrera_ids  -- Obtener los IDs de las carreras
            FROM Estudiante e
            INNER JOIN Matricula m ON e.estudiante_id = m.estudiante_id
            LEFT JOIN EstudianteCarrera ec ON e.estudiante_id = ec.estudiante_id
            LEFT JOIN Carrera c ON ec.carrera_id = c.carrera_id
            WHERE m.seccion_id = ?
            GROUP BY e.estudiante_id, e.nombre, e.apellido, e.numero_cuenta, e.correo_personal
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }

        $stmt->bind_param("i", $seccion_id);
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando la consulta: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $estudiantes = [];
        while ($row = $result->fetch_assoc()) {
            $estudiantes[] = $row;
        }
        $stmt->close();
        return $estudiantes;
    }

   /**
     * Obtiene los estudiantes matriculados en una sección para generar un CSV.
     *
     * @param int $seccion_id ID de la sección
     * @return array Lista de estudiantes matriculados
     */
    public function obtenerEstudiantesPorSeccionCSV($seccion_id) {
        $sql = "
            SELECT
                e.nombre,
                e.apellido,
                e.numero_cuenta,
                e.correo_personal,
                GROUP_CONCAT(c.nombre ORDER BY c.nombre ASC) AS carreras
            FROM
                Matricula m
            JOIN
                Estudiante e ON m.estudiante_id = e.estudiante_id
            JOIN
                EstudianteCarrera ec ON e.estudiante_id = ec.estudiante_id
            JOIN
                Carrera c ON ec.carrera_id = c.carrera_id
            WHERE
                m.seccion_id = ?
            GROUP BY
                e.estudiante_id
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $seccion_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $estudiantes = [];
        while ($row = $result->fetch_assoc()) {
            // No es necesario usar implode() porque GROUP_CONCAT ya devuelve una cadena separada por comas
            $row['carreras'] = $row['carreras']; // Simplemente deja el valor tal como está
            $estudiantes[] = $row;
        }
        
        $stmt->close();
        
        return $estudiantes;
    }

    /**
     * Genera un archivo CSV con los estudiantes matriculados en una sección.
     *
     * @param int $seccion_id ID de la sección
     * @return string Ruta del archivo generado
     */
    public function generarCSVEstudiantesPorSeccion($seccion_id) {
        // Obtener los estudiantes
        $estudiantes = $this->obtenerEstudiantesPorSeccionCSV($seccion_id);

        if (empty($estudiantes)) {
            throw new Exception('No hay estudiantes matriculados en esta sección');
        }

        // Obtener los detalles de la sección
        $seccion = $this->obtenerDetallesSeccion($seccion_id);
        $clase_nombre = $seccion['nombre_clase']; // Nombre de la clase
        $hora_inicio = $seccion['hora_inicio'];
        $codigo_seccion = date('Hi', strtotime($hora_inicio)); // Convertir hora a formato 0800

        // Crear el nombre del archivo CSV
        $fileName = __DIR__ . "/../../uploads/listado_estudiantes/{$clase_nombre}_{$codigo_seccion}.csv";

        // Verificar si la carpeta existe, si no, crearla con permisos adecuados
        $uploadsDir = __DIR__ . '/../../uploads/listado_estudiantes/';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);  // Crear directorio con permisos 0755
        }

        // Abrir el archivo CSV para escritura
        $file = fopen($fileName, 'w');

        // Verificar si el archivo se ha abierto correctamente
        if ($file === false) {
            throw new Exception('No se pudo crear el archivo CSV');
        }

        // Escribir los encabezados en el archivo CSV
        fputcsv($file, ['Nombre', 'Apellido', 'Número de Cuenta', 'Correo', 'Carreras']); 

        // Escribir los datos de los estudiantes
        foreach ($estudiantes as $estudiante) {
            // Las carreras ya están separadas por comas, así que no necesitamos usar implode
            fputcsv($file, [
                $estudiante['nombre'],
                $estudiante['apellido'],
                $estudiante['numero_cuenta'],
                $estudiante['correo_personal'],
                $estudiante['carreras'] // Ya es una cadena separada por comas
            ]);
        }

        fclose($file);

        return $fileName;
    }


    /**
     * Obtiene los detalles de la sección, incluyendo nombre de clase y hora de inicio.
     *
     * @param int $seccion_id ID de la sección.
     * @return array Detalles de la sección.
     */
    private function obtenerDetallesSeccion($seccion_id) {
        $sql = "SELECT c.nombre AS nombre_clase, s.hora_inicio
                FROM Seccion s
                INNER JOIN Clase c ON s.clase_id = c.clase_id
                WHERE s.seccion_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $seccion_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $seccion = $result->fetch_assoc();
        $stmt->close();

        return $seccion;
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
     * Envía un correo con las credenciales del estudiante de forma clara
     * 
     * @param string $correo Correo de destino
     * @param string $nombre Nombre del estudiante
     * @param string $apellido Apellido del estudiante
     * @param string $username Nombre de usuario (sin nombre/apellido)
     * @param string $password Contraseña generada
     */
    public function guardarCredencialesParaEnvio($correo, $nombre, $apellido, $username, $password, $numeroCuenta) {
        $nombreCompleto = trim("$nombre $apellido");
        $subject = 'Credenciales de Acceso al Sistema Universitario';
        
        // Versión HTML mejorada con CSS más atractivo
        $message = "
            <html>
            <head>
                <style>
                    body { 
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                        line-height: 1.6;
                        color: #333;
                        background-color: #f5f5f5;
                        margin: 0;
                        padding: 0;
                    }
                    .container {
                        max-width: 600px;
                        margin: 0 auto;
                        padding: 20px;
                        background-color: #ffffff;
                        border-radius: 8px;
                        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
                    }
                    .header {
                        text-align: center;
                        padding: 20px 0;
                        border-bottom: 2px solid #4a6fdc;
                    }
                    .header img {
                        max-height: 60px;
                        margin-bottom: 10px;
                    }
                    .header h1 {
                        color: #4a6fdc;
                        margin: 0;
                        font-size: 24px;
                    }
                    .content {
                        padding: 20px 0;
                    }
                    .card { 
                        background: #f8faff; 
                        border-left: 4px solid #4a6fdc;
                        border-radius: 4px; 
                        padding: 25px;
                        margin: 25px 0;
                        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
                    }
                    .credential-item { 
                        margin-bottom: 15px; 
                        padding-bottom: 15px; 
                        border-bottom: 1px solid #eaedf7;
                        display: flex;
                        align-items: center;
                    }
                    .credential-item:last-child {
                        border-bottom: none;
                        margin-bottom: 0;
                        padding-bottom: 0;
                    }
                    .label { 
                        font-weight: bold; 
                        color: #4a6fdc;
                        min-width: 150px;
                        display: inline-block;
                    }
                    .value {
                        font-family: 'Courier New', monospace;
                        padding: 5px 8px;
                        background-color: #f0f4ff;
                        border-radius: 3px;
                    }
                    .important { 
                        background-color: #fff5f5;
                        border-left: 4px solid #e53e3e;
                        color: #e53e3e; 
                        padding: 12px 15px;
                        margin-top: 20px;
                        border-radius: 4px;
                        font-weight: 500;
                    }
                    .button {
                        display: inline-block;
                        background-color: #4a6fdc;
                        color: white;
                        text-decoration: none;
                        padding: 12px 25px;
                        border-radius: 4px;
                        margin: 20px 0;
                        font-weight: bold;
                        text-align: center;
                        transition: background-color 0.3s;
                    }
                    .button:hover {
                        background-color: #3a5cbc;
                    }
                    .footer {
                        text-align: center;
                        padding-top: 20px;
                        border-top: 1px solid #eaedf7;
                        color: #666;
                        font-size: 14px;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Sistema de Gestión Universitaria</h1>
                    </div>
                    
                    <div class='content'>
                        <p>Estimado/a <strong>$nombreCompleto</strong>,</p>
                        
                        <p>Le damos la bienvenida al Sistema de Gestión Universitaria. A continuación, encontrará sus credenciales de acceso:</p>
                        
                        <div class='card'>
                            <div class='credential-item'>
                                <span class='label'>Nombre completo:</span>
                                <span class='value'>$nombreCompleto</span>
                            </div>
                            <div class='credential-item'>
                                <span class='label'>Usuario:</span>
                                <span class='value'>$username</span>
                            </div>
                            <div class='credential-item'>
                                <span class='label'>Contraseña temporal:</span>
                                <span class='value'>$password</span>
                            </div>
                            <div class='credential-item'>
                                <span class='label'>Número de cuenta:</span>
                                <span class='value'>$numeroCuenta</span>
                            </div>
                        </div>
                        
                        <div class='important'>
                            <strong>IMPORTANTE:</strong> Por seguridad, debe cambiar esta contraseña después de su primer acceso al sistema.
                        </div>
                        
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='https://registroisunah.xyz' class='button'>Acceder al Portal Estudiantil</a>
                        </div>
                        
                        <p>Si tiene alguna duda o inconveniente, no dude en contactar con nuestro equipo de soporte técnico.</p>
                    </div>
                    
                    <div class='footer'>
                        <p>Atentamente,<br><strong>Departamento de Registro</strong></p>
                        <p>© 2025 Universidad. Todos los derechos reservados.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        // Versión texto plano clara
        $altMessage = "Credenciales de Acceso - $nombreCompleto\n\n"
                    . "Nombre completo: $nombreCompleto\n"
                    . "Usuario del sistema: $username\n"
                    . "Contraseña temporal: $password\n"
                    . "Numero de Cuenta: $numeroCuenta\n\n"
                    . "IMPORTANTE: Debe cambiar esta contraseña después de su primer acceso.\n\n"
                    . "Acceso al sistema: https://registroisunah.xyz\n\n"
                    . "Atentamente,\nDepartamento de Registro";
    
        try {
            // Obtener ID del estado PENDIENTE por nombre
            $estadoPendienteId = $this->obtenerIdEstado('PENDIENTE');
            
            $query = "INSERT INTO ColaCorreosEstudiantes (
                        destinatario, 
                        nombre_destinatario,
                        asunto, 
                        cuerpo_html, 
                        cuerpo_texto, 
                        fecha_creacion,
                        estado_id
                    ) VALUES (?, ?, ?, ?, ?, NOW(), ?)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("sssssi", 
                $correo,
                $nombreCompleto,
                $subject,
                $message,
                $altMessage,
                $estadoPendienteId
            );
            
            if ($stmt->execute()) {
                return true;
            } else {
                throw new Exception("Error al insertar en cola de correos: " . $stmt->error);
            }
            
        } catch (Exception $e) {
            error_log("Error en guardarCredencialesParaEnvio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca estudiantes con filtros específicos
     * 
     * @param string|null $nombre
     * @param string|null $no_cuenta
     * @param string|null $centro
     * @param string|null $carrera
     * @return array
     * @throws Exception
     */
    public function buscarEstudiante($filtros) {
        $sql = "SELECT 
            e.estudiante_id,
            e.numero_cuenta,
            CONCAT(e.nombre, ' ', e.apellido) AS nombre_completo,
            e.correo_personal,
            d.nombre AS departamento,
            ca.nombre AS carrera
        FROM Estudiante e
        INNER JOIN EstudianteCarrera ec ON e.estudiante_id = ec.estudiante_id
        INNER JOIN Carrera ca ON ec.carrera_id = ca.carrera_id
        INNER JOIN Departamento d ON ca.dept_id = d.dept_id
        WHERE 1=1";
    
        $params = [];
        $types = '';
        
        // Mapeamos filtros a condiciones SQL
        $condiciones = [
            'nombre' => " AND CONCAT(e.nombre, ' ', e.apellido) LIKE ? ",
            'no_cuenta' => " AND e.numero_cuenta LIKE ? ",
            'carrera' => " AND ca.nombre LIKE ? ",
            'departamento' => " AND d.nombre LIKE ? "
        ];
    
        foreach ($condiciones as $key => $condition) {
            if (!empty($filtros[$key])) {
                $sql .= $condition;
                $params[] = "%{$filtros[$key]}%";
                $types .= 's';
            }
        }
    
        $sql .= " ORDER BY e.nombre";
    
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
    
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    




       /**
     * Obtiene el índice global del estudiante.
     *
     * @param int $estudianteId
     * @return float|null
     */
    public function obtenerIndiceGlobal(int $estudianteId): ?float
    {
        $stmt = $this->conn->prepare("SELECT indice_global FROM Estudiante WHERE estudiante_id = ?");
        $stmt->bind_param("i", $estudianteId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();

        return $fila ? (float) $fila['indice_global'] : null;
    }

    /**
     * Retorna el proceso de matrícula activo si existe.
     *
     * @return array|null
     */
    public function obtenerProcesoActivo(): ?array
    {
        $sql = "
            SELECT pm.fecha_inicio, pm.fecha_fin, tp.nombre AS tipo
            FROM ProcesoMatricula pm
            JOIN TipoProcesoMatricula tp ON tp.tipo_proceso_id = pm.tipo_proceso_id
            WHERE NOW() BETWEEN pm.fecha_inicio AND pm.fecha_fin
              AND pm.estado_proceso_id = (
                  SELECT estado_proceso_id FROM EstadoProceso WHERE nombre = 'ACTIVO'
              )
            LIMIT 1
        ";

        $resultado = $this->conn->query($sql);
        return $resultado && $resultado->num_rows > 0 ? $resultado->fetch_assoc() : null;
    }



    /**
     * Obtiene las clases activas de un estudiante (incluyendo laboratorio y datos del docente)
     *
     * @param int $estudiante_id ID del estudiante
     * @return array Lista de clases activas del estudiante
     * @throws Exception Si no se encuentran resultados
     * @author Jose Vargas
     */
    public function obtenerClasesActEstudiante($estudiante_id) {
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
                    pa.numero_periodo_id,

                    d.docente_id,
                    d.numero_empleado,
                    d.nombre AS nombre_docente,
                    d.apellido AS apellido_docente,
                    d.correo AS correo_docente,

                    l.laboratorio_id,
                    l.codigo_laboratorio,
                    l.hora_inicio AS hora_inicio_lab,
                    l.hora_fin AS hora_fin_lab,
                    GROUP_CONCAT(DISTINCT ld.dia_id ORDER BY ld.dia_id SEPARATOR ', ') AS lista_dia_ids_lab,
                    GROUP_CONCAT(DISTINCT dsl.nombre ORDER BY ld.dia_id SEPARATOR ', ') AS nombres_dias_lab,
                    al.nombre AS aula_lab,
                    el.nombre AS edificio_lab

                FROM Matricula m
                INNER JOIN Seccion s ON m.seccion_id = s.seccion_id
                INNER JOIN Clase c ON s.clase_id = c.clase_id
                INNER JOIN PeriodoAcademico pa ON s.periodo_academico_id = pa.periodo_academico_id
                INNER JOIN Aula a ON s.aula_id = a.aula_id
                INNER JOIN Edificio e ON a.edificio_id = e.edificio_id
                INNER JOIN Docente d ON s.docente_id = d.docente_id
                INNER JOIN EstadoProceso ep ON pa.estado_proceso_id = ep.estado_proceso_id
                LEFT JOIN SeccionDia sd ON s.seccion_id = sd.seccion_id
                LEFT JOIN DiaSemana ds ON sd.dia_id = ds.dia_id

                LEFT JOIN Laboratorio l ON m.laboratorio_id = l.laboratorio_id
                LEFT JOIN LaboratorioDia ld ON l.laboratorio_id = ld.laboratorio_id
                LEFT JOIN DiaSemana dsl ON ld.dia_id = dsl.dia_id
                LEFT JOIN Aula al ON l.aula_id = al.aula_id
                LEFT JOIN Edificio el ON al.edificio_id = el.edificio_id

                WHERE 
                    m.estudiante_id = ?
                    AND ep.estado_proceso_id = 1

                GROUP BY 
                    c.clase_id, c.codigo, c.nombre, c.creditos, c.tiene_laboratorio,
                    s.seccion_id, s.hora_inicio, s.hora_fin,
                    e.nombre, a.nombre, pa.anio, pa.numero_periodo_id,
                    d.numero_empleado, d.nombre, d.apellido, d.correo,
                    l.laboratorio_id, l.codigo_laboratorio, l.hora_inicio, l.hora_fin,
                    al.nombre, el.nombre

                ORDER BY s.seccion_id, s.hora_inicio, s.hora_fin";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $estudiante_id);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("No se encontraron clases activas para el estudiante");
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Guarda la información de la foto en la base de datos
     */
    public function guardarFoto($estudiante_id, $ruta_foto) {
        try {
            $query = "INSERT INTO FotosEstudiante (estudiante_id, ruta_foto) VALUES (?, ?)";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Error en preparación de consulta: " . $this->conn->error);
            }

            $stmt->bind_param("is", $estudiante_id, $ruta_foto);
            $result = $stmt->execute();
            $stmt->close();

            return $result;
        } catch (Exception $e) {
            error_log("Error al guardar foto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todas las fotos de un estudiante
     */
    public function obtenerFotos($estudiante_id) {
        try {
            $query = "SELECT foto_id, ruta_foto FROM FotosEstudiante WHERE estudiante_id = ?";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Error en preparación de consulta: " . $this->conn->error);
            }

            $stmt->bind_param("i", $estudiante_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $fotos = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $fotos;
        } catch (Exception $e) {
            error_log("Error al obtener fotos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Elimina una foto del estudiante
     */
    public function eliminarFoto($foto_id, $estudiante_id) {
        try {
            // Primero obtenemos la ruta del archivo
            $query = "SELECT ruta_foto FROM FotosEstudiante WHERE foto_id = ? AND estudiante_id = ?";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Error en preparación de consulta: " . $this->conn->error);
            }

            $stmt->bind_param("ii", $foto_id, $estudiante_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $foto = $result->fetch_assoc();
            $stmt->close();

            if (!$foto) {
                throw new Exception("Foto no encontrada");
            }

            // Eliminar de la base de datos
            $query = "DELETE FROM FotosEstudiante WHERE foto_id = ? AND estudiante_id = ?";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Error en preparación de consulta: " . $this->conn->error);
            }

            $stmt->bind_param("ii", $foto_id, $estudiante_id);
            $result = $stmt->execute();
            $stmt->close();

            if (!$result) {
                throw new Exception("Error al eliminar de la base de datos");
            }

            // Eliminar archivo físico
            $filepath = __DIR__ . '/../../' . $foto['ruta_foto'];
            if (file_exists($filepath)) {
                if (!unlink($filepath)) {
                    throw new Exception("Error al eliminar el archivo físico");
                }
            }

            return true;
        } catch (Exception $e) {
            error_log("Error al eliminar foto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si un estudiante existe
     */
    public function estudianteExiste($estudiante_id) {
        try {
            $query = "SELECT estudiante_id FROM Estudiante WHERE estudiante_id = ?";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Error en preparación de consulta: " . $this->conn->error);
            }

            $stmt->bind_param("i", $estudiante_id);
            $stmt->execute();
            $stmt->store_result();
            $exists = $stmt->num_rows > 0;
            $stmt->close();

            return $exists;
        } catch (Exception $e) {
            error_log("Error al verificar estudiante: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si una foto pertenece a un estudiante
     */
    public function fotoPerteneceAEstudiante($foto_id, $estudiante_id) {
        try {
            $query = "SELECT foto_id FROM FotosEstudiante WHERE foto_id = ? AND estudiante_id = ?";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Error en preparación de consulta: " . $this->conn->error);
            }

            $stmt->bind_param("ii", $foto_id, $estudiante_id);
            $stmt->execute();
            $stmt->store_result();
            $exists = $stmt->num_rows > 0;
            $stmt->close();

            return $exists;
        } catch (Exception $e) {
            error_log("Error al verificar foto: " . $e->getMessage());
            return false;
        }
    }
}
?>