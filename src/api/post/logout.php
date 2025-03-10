<?php

// Archivo: src/api/post/logout.php

/**
 * API para cerrar sesión de usuario.
 *
 * @author Ruben Diaz
 * @version 1.0
 * 
 * Este script maneja la destrucción de la sesión del usuario autenticado.
 *
 * Métodos soportados:
 * - `POST`: Finaliza la sesión activa.
 *
 * Respuestas HTTP:
 * - `200 OK`: Cierre de sesión exitoso.
 * - `405 Method Not Allowed`: Método HTTP no permitido.
 * 
 * Ejemplo respuesta
 * 
 * {
 *  "message": "Cierre de sesión exitoso"
 * }
 */


header('Content-Type: application/json');
session_start();

// Validar que el método sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

/**
 * Destruye la sesión actual del usuario.
 *
 * @return void
 */
session_destroy();

/**
 * Devuelve una respuesta en JSON confirmando el cierre de sesión.
 */
echo json_encode(['message' => 'Cierre de sesión exitoso']);
?>
