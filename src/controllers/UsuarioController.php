<?php
/**
 * Controlador para Usuario.
 *
 * Se encarga de coordinar la logica de Usuario.
 *
 * @package Controllers
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

require_once __DIR__ . '/../models/Usuario.php';

class UsuarioController {
    /**
     * Obtiene la lista de usuarios con sus roles y envía la respuesta en formato JSON.
     *
     * @return void
     */
    public function listarUsuariosConRoles() {
        try {
            $usuarioModel = new Usuario();
            $usuarios = $usuarioModel->listarUsuariosConRoles();
            http_response_code(200);
            echo json_encode($usuarios);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
?>
