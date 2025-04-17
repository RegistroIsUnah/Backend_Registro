<?php

require_once __DIR__ . '/../models/ResetPassword.php';

/**
 * Controlador de PasswordReset
 *
 * Maneja la lógica de PasswordReset
 *
 * @package Controllers
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class PasswordResetController {
    private $model;

    /**
     * Constructor del controlador.
     */
    public function __construct() {
        $this->model = new ResetPassword();
    }

    /**
     * Maneja la solicitud de restablecimiento de contraseña
     * Método: POST
     * Ruta: /password-reset/request
     * Body: { "email": "usuario@ejemplo.com" }
     */
    public function requestReset() {
        header('Content-Type: application/json');
        
        try {
            // Obtener datos del cuerpo de la petición
            $data = json_decode(file_get_contents('php://input'), true);
            $email = $data['email'] ?? null;
            
            // Validar entrada
            if (!$email) {
                throw new Exception('El correo electrónico es requerido');
            }
            
            // Procesar la solicitud (el modelo maneja todo)
            if (!$this->model->createAndSendResetRequest($email)) {
                throw new Exception('Error al procesar la solicitud');
            }
            
            // Respuesta exitosa
            echo json_encode([
                'success' => true,
                'message' => 'Se ha enviado un correo con instrucciones para restablecer tu contraseña'
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Maneja la validación del token y actualización de contraseña
     * Método: POST
     * Ruta: /password-reset/confirm
     * Body: { "token": "token_recibido", "password": "nueva_contraseña" }
     */
    public function confirmReset() {
        header('Content-Type: application/json');
        
        try {
            // Obtener datos del cuerpo de la petición
            $data = json_decode(file_get_contents('php://input'), true);
            $token = $data['token'] ?? null;
            $newPassword = $data['password'] ?? null;
            
            // Validar entrada
            if (!$token || !$newPassword) {
                throw new Exception('Token y nueva contraseña son requeridos');
            }
            
            // Validar fortaleza de la contraseña (opcional)
            if (strlen($newPassword) < 8) {
                throw new Exception('La contraseña debe tener al menos 8 caracteres');
            }
            
            // Validar token y actualizar contraseña
            $request = $this->model->validateToken($token);
            
            if (!$request) {
                throw new Exception('Token inválido o expirado');
            }
            
            // Actualizar contraseña
            if (!$this->model->updatePassword($request['usuario_id'], $newPassword)) {
                throw new Exception('Error al actualizar la contraseña');
            }
            
            // Marcar token como usado
            $this->model->markTokenAsUsed($token);
            
            // Respuesta exitosa
            echo json_encode([
                'success' => true,
                'message' => 'Contraseña actualizada correctamente'
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Maneja la validación de un token (para el frontend)
     * Método: GET
     * Ruta: /password-reset/validate-token?token=abc123
     */
    public function validateToken() {
        header('Content-Type: application/json');
        
        try {
            // Obtener token de los parámetros de consulta
            $token = $_GET['token'] ?? null;
            
            if (!$token) {
                throw new Exception('Token no proporcionado');
            }
            
            // Validar token
            $request = $this->model->validateToken($token);
            
            if (!$request) {
                throw new Exception('Token inválido o expirado');
            }
            
            // Respuesta exitosa
            echo json_encode([
                'success' => true,
                'message' => 'Token válido',
                'username' => $request['username']
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}