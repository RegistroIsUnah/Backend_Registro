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

        // Se utiliza el modelo Usuario para obtener los datos
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
        if ($password !== $user['password']) {
            http_response_code(401);
            echo json_encode(['error' => 'Credenciales inválidas']);
            exit;
        }

        // Obtener roles del usuario desde la tabla UsuarioRol
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
            $stmtDocente = $conn->prepare('SELECT docente_id, nombre, apellido, correo, foto FROM Docente WHERE usuario_id = ?');
            $stmtDocente->bind_param('i', $user['usuario_id']);
            $stmtDocente->execute();
            $resultDocente = $stmtDocente->get_result();
            if ($resultDocente->num_rows > 0) {
                $docenteData = $resultDocente->fetch_assoc();
                $userDetails['docente_id'] = $docenteData['docente_id']; // Agregamos el ID del docente
                $userDetails['docente'] = $docenteData;  // Agregamos todos los detalles del docente
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
            }
        }

        // Guardar en la sesión
        $_SESSION['user_id'] = $user['usuario_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['roles'] = $roles;
        $_SESSION['revisor_id'] = $revisorData['revisor_id'];
        
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

        // Validar si existe una sesión iniciada
        if (!isset($_SESSION['user_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'No hay sesión iniciada']);
            return;
        }

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
