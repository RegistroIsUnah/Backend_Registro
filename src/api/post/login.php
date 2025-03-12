<?php
/**
 * API para el inicio de sesión de usuarios.
 *
 * Este script recibe la petición POST, valida los datos y llama a AuthController::login().
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
 *   "aulas": [
 *       {
 *           "aula_id": 3,
 *           "nombre": "Aula 201",
 *           "capacidad": 60
 *       },
 *       {
 *           "aula_id": 4,
 *           "nombre": "Aula 202",
 *           "capacidad": 55
 *       }
 *   ]
 * }
 * 
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../controllers/AuthController.php';

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Obtener datos del JSON
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['username']) || !isset($input['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan datos de usuario o contraseña']);
    exit;
}

// Llamar al controlador
$authController = new AuthController();
$authController->login($input['username'], $input['password']);
?>
