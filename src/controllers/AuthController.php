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
        $stmt = $conn->prepare('SELECT usuario_id, username, password, rol_id FROM Usuario WHERE username = ?');
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

        $roles = [];
        $userDetails = [];

        // Ejemplo: obtener datos de Docente
        $stmtDocente = $conn->prepare('SELECT docente_id, nombre, apellido, correo, foto FROM Docente WHERE usuario_id = ?');
        $stmtDocente->bind_param('i', $user['usuario_id']);
        $stmtDocente->execute();
        $resultDocente = $stmtDocente->get_result();
        if ($resultDocente->num_rows > 0) {
            $roles[] = 'docente';
            $docenteData = $resultDocente->fetch_assoc();
            $userDetails['docente'] = $docenteData;

            // Verificar roles adicionales, por ejemplo, jefe o coordinador
            $stmtJefe = $conn->prepare('SELECT dept_id FROM Departamento WHERE jefe_docente_id = ?');
            $stmtJefe->bind_param('i', $docenteData['docente_id']);
            $stmtJefe->execute();
            $resultJefe = $stmtJefe->get_result();
            if ($resultJefe->num_rows > 0) {
                $roles[] = 'jefe_departamento';
            }
            $stmtCoord = $conn->prepare('SELECT carrera_id FROM Carrera WHERE coordinador_docente_id = ?');
            $stmtCoord->bind_param('i', $docenteData['docente_id']);
            $stmtCoord->execute();
            $resultCoord = $stmtCoord->get_result();
            if ($resultCoord->num_rows > 0) {
                $roles[] = 'coordinador';
            }
        }

        // Ejemplo: obtener datos de Estudiante
        $stmtEstudiante = $conn->prepare('SELECT estudiante_id, nombre, apellido, correo_personal, telefono, direccion FROM Estudiante WHERE usuario_id = ?');
        $stmtEstudiante->bind_param('i', $user['usuario_id']);
        $stmtEstudiante->execute();
        $resultEstudiante = $stmtEstudiante->get_result();
        if ($resultEstudiante->num_rows > 0) {
            $roles[] = 'estudiante';
            $estudianteData = $resultEstudiante->fetch_assoc();
            $userDetails['estudiante'] = $estudianteData;
        }

        // Ejemplo: verificar si es revisor
        $stmtRevisor = $conn->prepare('SELECT revisor_id FROM Revisor WHERE usuario_id = ?');
        $stmtRevisor->bind_param('i', $user['usuario_id']);
        $stmtRevisor->execute();
        $resultRevisor = $stmtRevisor->get_result();
        if ($resultRevisor->num_rows > 0) {
            $roles[] = 'revisor';
        }

        // Guardar en la sesión
        $_SESSION['user_id'] = $user['usuario_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['roles'] = $roles;
        
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
