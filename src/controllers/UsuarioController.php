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
    private $model;

    public function __construct() {
        $this->model = new Usuario();
    }

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

     /**
     * Maneja la solicitud de cambio de contraseña
     * @author Jose Vargas
     * 
     */
    public function cambiarPassword() {
        header('Content-Type: application/json');
        
        try {
            // Validar autenticación
            if (!isset($_SESSION['usuario_id'])) {
                throw new Exception("Debe iniciar sesión", 401);
            }
    
            // Validar token CSRF
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                throw new Exception("Token de seguridad inválido", 403);
            }
    
            // Validar campos
            $camposRequeridos = ['password_actual', 'nueva_password', 'confirmar_password'];
            foreach ($camposRequeridos as $campo) {
                if (empty($_POST[$campo])) {
                    throw new Exception("El campo $campo es requerido", 400);
                }
            }
    
            // Obtener datos
            $usuarioId = $_SESSION['usuario_id'];
            $passwordActual = $_POST['password_actual'];
            $nuevaPassword = $_POST['nueva_password'];
            $confirmarPassword = $_POST['confirmar_password'];
    
            // Validar coincidencia
            if ($nuevaPassword !== $confirmarPassword) {
                throw new Exception("Las contraseñas nuevas no coinciden", 400);
            }
    
            // Ejecutar cambio
            $this->model->cambiarPassword(
                $usuarioId,
                $nuevaPassword,
                $passwordActual
            );
    
            // Respuesta exitosa
            echo json_encode([
                'success' => true,
                'message' => 'Contraseña actualizada exitosamente'
            ]);
    
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
?>
