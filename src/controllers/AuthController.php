<?php
require_once __DIR__ . '/../models/Usuario.php';

    /**
     * Controlador de Autenticación
     *
     * Maneja el proceso de autenticación y gestión de sesiones.
     * 
     * @package controllers
     * @author Ruben Diaz
     * @version 1.0
     * 
     */
    class AuthController {
    /**
     * Inicia sesión de un usuario verificando sus credenciales.
     *
     * @param string $username Nombre de usuario.
     * @param string $password Contraseña del usuario.
     * @return void Responde con JSON (token y datos del usuario o error).
     */
    public function login($username, $password) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    
        $username = trim($username);
        $password = trim($password);
    
        $db = new DataBase();
        $conn = $db->getConnection();
    
        // Obtener los datos del usuario
        $stmt = $conn->prepare('SELECT usuario_id, username, password FROM Usuario WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows === 0) {
            http_response_code(401);
            echo json_encode(['error' => 'Credenciales inválidas']);
            exit;
        }
    
        $user = $result->fetch_assoc();
        $storedPassword = $user['password'];
    
        // Verificar si la contraseña almacenada está hasheada
        if (password_get_info($storedPassword)['algo']) {
            // Si la contraseña está hasheada, utilizar password_verify()
            if (!password_verify($password, $storedPassword)) {
                http_response_code(401);
                echo json_encode(['error' => 'Credenciales inválidas']);
                exit;
            }
        } else {
            // Si la contraseña no está hasheada, comparar directamente
            if ($password !== $storedPassword) {
                http_response_code(401);
                echo json_encode(['error' => 'Credenciales inválidas']);
                exit;
            }
        }
    
        // Obtener roles del usuario
        $roles = [];
        $stmtRoles = $conn->prepare('SELECT r.nombre FROM Rol r
                                    INNER JOIN UsuarioRol ur ON r.rol_id = ur.rol_id
                                    WHERE ur.usuario_id = ?');
        $stmtRoles->bind_param('i', $user['usuario_id']);
        $stmtRoles->execute();
        $resultRoles = $stmtRoles->get_result();
    
        while ($role = $resultRoles->fetch_assoc()) {
            $roles[] = $role['nombre'];
        }
    
        $userDetails = [];
        $userDetails['user_id'] = $user['usuario_id']; // Siempre se incluye el usuario_id
    
        // Obtener datos de Docente (si aplica)
        if (in_array('Docente', $roles)) {
            $stmtDocente = $conn->prepare('SELECT d.docente_id, d.nombre, d.apellido, d.correo, d.foto, dept.dept_id, dept.nombre AS departamento 
                                           FROM Docente d 
                                           INNER JOIN Departamento dept ON d.dept_id = dept.dept_id 
                                           WHERE d.usuario_id = ?');
            $stmtDocente->bind_param('i', $user['usuario_id']);
            $stmtDocente->execute();
            $resultDocente = $stmtDocente->get_result();
            
            if ($resultDocente->num_rows > 0) {
                $docenteData = $resultDocente->fetch_assoc();
                $userDetails['docente_id'] = $docenteData['docente_id']; // Agregamos el ID del docente
                $userDetails['docente'] = $docenteData; // Agregamos todos los detalles del docente
    
                // Verificar si es Jefe de Departamento
                if (in_array('Jefe de Departamento', $roles)) {
                    $stmtJefe = $conn->prepare('SELECT dept.dept_id, dept.nombre AS departamento 
                                              FROM Departamento dept 
                                              WHERE dept.jefe_docente_id = ?');
                    $stmtJefe->bind_param('i', $docenteData['docente_id']);
                    $stmtJefe->execute();
                    $resultJefe = $stmtJefe->get_result();
    
                    if ($resultJefe->num_rows > 0) {
                        $jefeData = $resultJefe->fetch_assoc();
                        $userDetails['jefe_departamento'] = [
                            'dept_id' => $jefeData['dept_id'],
                            'nombre_departamento' => $jefeData['departamento']
                        ];
                    }
                }
    
                // Verificar si es Coordinador
                if (in_array('Coordinador', $roles)) {
                    $stmtCoordinador = $conn->prepare('SELECT c.carrera_id, c.nombre AS carrera 
                                                      FROM Carrera c 
                                                      WHERE c.coordinador_docente_id = ?');
                    $stmtCoordinador->bind_param('i', $docenteData['docente_id']);
                    $stmtCoordinador->execute();
                    $resultCoordinador = $stmtCoordinador->get_result();
    
                    if ($resultCoordinador->num_rows > 0) {
                        $coordinadorData = $resultCoordinador->fetch_assoc();
                        $userDetails['coordinador_carrera'] = [
                            'carrera_id' => $coordinadorData['carrera_id'],
                            'nombre_carrera' => $coordinadorData['carrera']
                        ];
                    }
                }
            }
        }
    
        // Obtener datos de Estudiante (si aplica)
        if (in_array('Estudiante', $roles)) {
            $stmtEstudiante = $conn->prepare('SELECT estudiante_id, nombre, apellido, correo_personal, telefono, direccion 
                                             FROM Estudiante WHERE usuario_id = ?');
            $stmtEstudiante->bind_param('i', $user['usuario_id']);
            $stmtEstudiante->execute();
            $resultEstudiante = $stmtEstudiante->get_result();
            if ($resultEstudiante->num_rows > 0) {
                $estudianteData = $resultEstudiante->fetch_assoc();
                $userDetails['estudiante_id'] = $estudianteData['estudiante_id']; // Agregar estudiante_id
                $userDetails['estudiante'] = $estudianteData; // Agregar los detalles del estudiante
            }
        }
    
        // Verificar si es revisor
        if (in_array('Revisor', $roles)) {
            $stmtRevisor = $conn->prepare('SELECT revisor_id FROM Revisor WHERE usuario_id = ?');
            $stmtRevisor->bind_param('i', $user['usuario_id']);
            $stmtRevisor->execute();
            $resultRevisor = $stmtRevisor->get_result();
            if ($resultRevisor->num_rows > 0) {
                $revisorData = $resultRevisor->fetch_assoc();
                $userDetails['revisor_id'] = $revisorData['revisor_id']; // Agregar revisor_id
            } else {
                $userDetails['error'] = 'El usuario no es revisor'; // Si no es revisor
            }
        }
    
        // Guardar en la sesión
        $_SESSION['user_id'] = $user['usuario_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['roles'] = $roles;
        $_SESSION['revisor_id'] = isset($revisorData['revisor_id']) ? $revisorData['revisor_id'] : null;
    
        $token = session_id();
    
        $response = [
            'token' => $token,
            'user' => [
                'id'       => $user['usuario_id'],
                'username' => $user['username'],
                'roles'    => $roles,
                'details'  => $userDetails
            ],
            'message' => 'Inicio de sesión exitoso'
        ];
    
        echo json_encode($response);
    }
    
    /**
     * Cierra la sesión del usuario actual.
     *
     * @return void Responde con Json con confirmacion de cierre o con error
     */
    public function logout() {
        
        // Iniciar la sesión si aún no está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        /*
        // Validar si existe una sesión iniciada
        if (!isset($_SESSION['user_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'No hay sesión iniciada']);
            return;
        }
        */

        // Destruir la sesión
        session_destroy();

        // (Opcional) Eliminar cookie de sesión, si se quiere ser más exhaustivo:
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Respuesta
        http_response_code(200);
        echo json_encode(['message' => 'Cierre de sesión exitoso']);
    }
}
?>
