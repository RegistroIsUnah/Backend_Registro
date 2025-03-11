<?php
/**
 * API para el inicio de sesión de usuarios.
 *
 * Este script recibe la petición POST, valida los datos y llama a AuthController::login().
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
