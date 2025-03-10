<?php

// Archivo: src/api/post/login.php

/**
 * API para el inicio de sesión de usuarios.
 * 
 * @author Ruben Diaz
 * @version 1.0
 *
 * Este script maneja la autenticación de usuarios, verificando sus credenciales y asignando roles según su tipo.
 *
 * Métodos soportados:
 * - `POST`: Requiere un JSON con `username` y `password`.
 *
 * Respuestas HTTP:
 * - `200 OK`: Inicio de sesión exitoso.
 * - `400 Bad Request`: Faltan datos en la solicitud.
 * - `401 Unauthorized`: Credenciales incorrectas.
 * - `405 Method Not Allowed`: Método HTTP no permitido.
 * 
 * Ejemplo envio de datos
 * 
 * {
 * "username": "docente2",
 * "password": "docente345"
 * }
 * 
 * Ejemplo respuesta
 * 
 * {
 *    "token": "q9d32bet0lbba1n3c6tl4dh7r9",
 *    "user": {
 *        "id": 7,
 *        "username": "docente2",
 *        "roles": [
 *            "docente",
 *            "coordinador"
 *        ],
 *        "details": {
 *            "docente": {
 *                "docente_id": 2,
 *                "nombre": "Alex",
 *                "apellido": "Diaz",
 *                "correo": "alex.diaz@unah.hn",
 *                "foto": "alex.jpg"
 *            }
 *        }
 *    },
 *    "message": "Inicio de sesión exitoso"
 * }
 */

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../modules/config/Environments.php';
require_once __DIR__ . '/../../modules/config/DataBase.php';

// Validar que el método sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Decodificar el JSON recibido
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['username']) || !isset($input['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan datos de usuario o contraseña']);
    exit;
}

$username = trim($input['username']);
$password = trim($input['password']);

// Conectar a la base de datos
$db = new DataBase();
$conn = $db->getConnection();

/**
 * Verifica si el usuario existe y obtiene su información.
 *
 * @param mysqli $conn Conexión a la base de datos.
 * @param string $username Nombre de usuario.
 * @return array|null Datos del usuario si existe, `null` si no se encuentra.
 */
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

// Comparación de contraseña en texto plano (se usara otra cosa en produccion)
if ($password !== $user['password']) {
    http_response_code(401);
    echo json_encode(['error' => 'Credenciales inválidas']);
    exit;
}

// Inicializar el array de roles
$roles = [];

// Inicializar array para información adicional del usuario
$userDetails = [];

/**
 * Verifica si el usuario es docente y obtiene sus datos.
 *
 * @param mysqli $conn Conexión a la base de datos.
 * @param int $usuario_id ID del usuario.
 * @return array|null Datos del docente si existe, `null` si no.
 */
$stmtDocente = $conn->prepare('SELECT docente_id, nombre, apellido, correo, foto FROM Docente WHERE usuario_id = ?');
$stmtDocente->bind_param('i', $user['usuario_id']);
$stmtDocente->execute();
$resultDocente = $stmtDocente->get_result();
if ($resultDocente->num_rows > 0) {
    $roles[] = 'docente';
    $docenteData = $resultDocente->fetch_assoc();
    $userDetails['docente'] = $docenteData;

    // Verificar si es jefe de departamento
    $stmtJefe = $conn->prepare('SELECT dept_id FROM Departamento WHERE jefe_docente_id = ?');
    $stmtJefe->bind_param('i', $docenteData['docente_id']);
    $stmtJefe->execute();
    $resultJefe = $stmtJefe->get_result();
    if ($resultJefe->num_rows > 0) {
        $roles[] = 'jefe_departamento';
    }

    // Verificar si es coordinador
    $stmtCoord = $conn->prepare('SELECT carrera_id FROM Carrera WHERE coordinador_docente_id = ?');
    $stmtCoord->bind_param('i', $docenteData['docente_id']);
    $stmtCoord->execute();
    $resultCoord = $stmtCoord->get_result();
    if ($resultCoord->num_rows > 0) {
        $roles[] = 'coordinador';
    }
}

/**
 * Verifica si el usuario es estudiante y obtiene sus datos.
 *
 * @param mysqli $conn Conexión a la base de datos.
 * @param int $usuario_id ID del usuario.
 * @return array|null Datos del estudiante si existe, `null` si no.
 */
$stmtEstudiante = $conn->prepare('SELECT estudiante_id, nombre, apellido, correo_personal, telefono, direccion FROM Estudiante WHERE usuario_id = ?');
$stmtEstudiante->bind_param('i', $user['usuario_id']);
$stmtEstudiante->execute();
$resultEstudiante = $stmtEstudiante->get_result();
if ($resultEstudiante->num_rows > 0) {
    $roles[] = 'estudiante';
    $estudianteData = $resultEstudiante->fetch_assoc();
    $userDetails['estudiante'] = $estudianteData;
}

/**
 * Verifica si el usuario es revisor.
 *
 * @param mysqli $conn Conexión a la base de datos.
 * @param int $usuario_id ID del usuario.
 * @return bool `true` si el usuario es revisor, `false` si no.
 */
$stmtRevisor = $conn->prepare('SELECT revisor_id FROM Revisor WHERE usuario_id = ?');
$stmtRevisor->bind_param('i', $user['usuario_id']);
$stmtRevisor->execute();
$resultRevisor = $stmtRevisor->get_result();
if ($resultRevisor->num_rows > 0) {
    $roles[] = 'revisor';
}

// Almacenar los roles en la sesión
$_SESSION['user_id'] = $user['usuario_id'];
$_SESSION['username'] = $user['username'];
$_SESSION['roles'] = $roles;

// Generar el token de sesión
$token = session_id();

/**
 * Prepara y envía la respuesta en formato JSON.
 *
 * @param string $token Token de sesión generado.
 * @param array $user Datos básicos del usuario autenticado.
 * @param array $roles Roles asignados al usuario.
 * @param array $userDetails Información adicional del usuario (docente/estudiante).
 */
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
?>
