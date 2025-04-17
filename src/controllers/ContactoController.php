<?php
require_once __DIR__ . '/../models/Contacto.php';

class ContactoController {
    private $model;
    
    /**
     * Constructor del controlador.
     */
    public function __construct() {
        $this->model = new Contacto();
    }
    
    /**
     * Envía una solicitud de contacto
     * 
     * @param array $data Datos de la solicitud (debe contener numero_cuenta_solicitante y numero_cuenta_destino)
     * @return array Resultado de la operación
     */
    public function enviarSolicitud($data) {
        // Validar datos de entrada
        if (empty($data['numero_cuenta_solicitante']) || empty($data['numero_cuenta_destino'])) {
            return ['success' => false, 'error' => 'Números de cuenta requeridos'];
        }
        
        $motivo = $data['motivo'] ?? null;
        
        return $this->model->enviarSolicitudContacto(
            $data['numero_cuenta_solicitante'],
            $data['numero_cuenta_destino'],
            $motivo
        );
    }
    
    /**
     * Responde a una solicitud de contacto
     * 
     * @param array $data Datos de la respuesta (debe contener solicitud_id, numero_cuenta_destino y aceptar)
     * @return array Resultado de la operación
     */
    public function responderSolicitud($data) {
        // Validar datos de entrada
        if (empty($data['solicitud_id']) || empty($data['numero_cuenta_destino']) || !isset($data['aceptar'])) {
            return ['success' => false, 'error' => 'Datos incompletos'];
        }
        
        return $this->model->responderSolicitudContacto(
            $data['solicitud_id'],
            $data['numero_cuenta_destino'],
            $data['aceptar']
        );
    }
    
    /**
     * Obtiene las solicitudes pendientes de un estudiante
     * 
     * @param string $numeroCuenta Número de cuenta del estudiante
     * @return array Lista de solicitudes o error
     */
    public function obtenerSolicitudesPendientes($numeroCuenta) {
        if (empty($numeroCuenta)) {
            return ['error' => 'Número de cuenta requerido'];
        }
        
        return $this->model->obtenerSolicitudesPendientes($numeroCuenta);
    }

    /**
     * Obtiene los contactos de un estudiante
     * @param string $numeroCuenta Número de cuenta del estudiante
     * @return array Lista de contactos o error
     */
    public function obtenerContactos($numeroCuenta) {
        if (empty($numeroCuenta)) {
            return ['error' => 'Número de cuenta es requerido', 'code' => 400];
        }

        return $this->model->obtenerContactos($numeroCuenta);
    }

    /**
     * Elimina un contacto mutuo
     * @param string $numeroCuenta Número de cuenta del estudiante
     * @param string $contactoNumeroCuenta Número de cuenta del contacto a eliminar
     * @return array Resultado de la operación
     */
    public function eliminarContacto($numeroCuenta, $contactoNumeroCuenta) {
        if (empty($numeroCuenta) || empty($contactoNumeroCuenta)) {
            return ['success' => false, 'error' => 'Datos incompletos', 'code' => 400];
        }

        return $this->model->eliminarContacto($numeroCuenta, $contactoNumeroCuenta);
    }
}
?>