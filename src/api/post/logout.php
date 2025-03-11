<?php
/**
 * API para el cierre de sesión de usuarios.
 *
 * Este script recibe la petición POST y llama a AuthController::logout().
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

// Llamar al controlador
$authController = new AuthController();
$authController->logout();
