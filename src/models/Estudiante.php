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
    public function obtenerPerfilEstudiante($estudianteId) {
        $sql = "SELECT 
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
                    GROUP_CONCAT(ca.nombre SEPARATOR ', ') AS carreras
                FROM Estudiante e
                INNER JOIN Usuario u ON e.usuario_id = u.usuario_id
                INNER JOIN Centro c ON e.centro_id = c.centro_id
                LEFT JOIN EstudianteCarrera ec ON e.estudiante_id = ec.estudiante_id
                LEFT JOIN Carrera ca ON ec.carrera_id = ca.carrera_id
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




<<<<<<< Updated upstream
=======
    /**
     * Registra una evaluación de docente realizada por el estudiante
     * 
     * @param int $docenteId
     * @param int $periodoId
     * @param array $respuestas
     * @return bool
     * @throws Exception
     * @author Jose Vargas
     * @version 1.0
     */
    public function registrarEvaluacionDocente($docenteId, $periodoId, $respuestas) {
        $this->conn->begin_transaction();
        
        try {
            // 1. Insertar evaluación principal
            $sqlEvaluacion = "INSERT INTO EvaluacionDocente (
                docente_id, 
                estudiante_id, 
                periodo_academico_id, 
                fecha, 
                estado_evaluacion_id
            ) VALUES (?, ?, ?, NOW(), 1)";
            
            $stmt = $this->conn->prepare($sqlEvaluacion);
            $stmt->bind_param("iiii", $docenteId, $estudianteId, $periodoId); // Usar parámetro
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

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollback();
            throw new Exception("Error al guardar evaluación: " . $e->getMessage());
        }
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
     * @return int ID del estudiante creado.
     */
    public function registrarEstudiante($usuario_id, $identidad, $nombre, $apellido, $correo, $telefono, $centro_id) {
        // Inserción en la tabla Estudiante
        $sqlCheck = "SELECT centro_id FROM Centro WHERE centro_id = ?";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bind_param("i", $centro_id);
        $stmtCheck->execute();
        
        if ($stmtCheck->get_result()->num_rows == 0) {
            throw new Exception("El centro con ID $centro_id no existe");
        }
        $sql = "INSERT INTO Estudiante (usuario_id, identidad, nombre, apellido, correo_personal, telefono, direccion, centro_id, indice_global, indice_periodo) 
                VALUES (?, ?, ?, ?, ?, ?, 'No disponible', ?, 100, 0)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issssii", $usuario_id, $identidad, $nombre, $apellido, $correo, $telefono, $centro_id);
        $stmt->execute();

        // Obtener el ID del estudiante
        return $stmt->insert_id;
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
    public function enviarCorreoConCredenciales($correo, $nombre, $apellido, $username, $password) {
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
                    . "Contraseña temporal: $password\n\n"
                    . "IMPORTANTE: Debe cambiar esta contraseña después de su primer acceso.\n\n"
                    . "Acceso al sistema: https://registroisunah.xyz\n\n"
                    . "Atentamente,\nDepartamento de Registro";
    
        // Envío asíncrono
        register_shutdown_function(function() use ($correo, $nombreCompleto, $subject, $message, $altMessage) {
            sendmail($correo, $nombreCompleto, $subject, $message, $altMessage);
        });
    }
>>>>>>> Stashed changes
}
?>
