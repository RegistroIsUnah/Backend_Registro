<?php
require_once __DIR__ . '/../models/ChatModel.php';

class ChatController {
    private $model;
    
    /**
     * Constructor del controlador.
     */
    public function __construct() {
        $this->model = new Chat();
    }
    
    /**
     * Crear un nuevo chat
     */
    public function crearChat($esGrupal, $nombre, $participantes) {
        if ($esGrupal && empty($nombre)) {
            return ['success' => false, 'error' => 'Los grupos deben tener nombre'];
        }
        
        if (count($participantes) < ($esGrupal ? 2 : 1)) {
            return ['success' => false, 'error' => $esGrupal ? 'Se necesitan al menos 2 participantes' : 'Se necesita el participante'];
        }
        
        return $this->model->crearChat($esGrupal, $nombre, $participantes);
    }
    
    /**
     * Enviar mensaje
     */
    public function enviarMensaje($chatId, $numeroCuenta, $contenido, $archivos = []) {
        if (empty($contenido) && empty($archivos)) {
            return ['success' => false, 'error' => 'El mensaje no puede estar vacío'];
        }
        
        return $this->model->enviarMensaje($chatId, $numeroCuenta, $contenido, $archivos);
    }
    
    /**
     * Obtener chats de un estudiante
     */
    public function obtenerChats($numeroCuenta) {
        return $this->model->obtenerChats($numeroCuenta);
    }
    
    /**
     * Obtener mensajes de un chat
     */
    public function obtenerMensajes($chatId, $numeroCuenta, $limit = 50, $offset = 0) {
        return $this->model->obtenerMensajes($chatId, $numeroCuenta, $limit, $offset);
    }
    
    /**
     * Obtener participantes de un chat
     */
    public function obtenerParticipantes($chatId) {
        return $this->model->obtenerParticipantes($chatId);
    }
    
    /**
     * Agregar participantes a un chat grupal
     */
    public function agregarParticipantes($chatId, $participantes) {
        if (empty($participantes)) {
            return ['success' => false, 'error' => 'Debes especificar participantes'];
        }
        
        return $this->model->agregarParticipantes($chatId, $participantes);
    }

    public function descargarArchivo($archivoId, $numeroCuenta) {
        // 1. Obtener el ID del estudiante desde el número de cuenta
        $estudianteId = $this->model->obtenerIdPorNumeroCuenta($numeroCuenta);
        if (!$estudianteId) {
            return [
                'success' => false,
                'error' => 'Estudiante no encontrado',
                'code' => 404
            ];
        }
    
        // 2. Validar permisos y obtener metadatos del archivo
        $archivo = $this->model->obtenerArchivoParaUsuario($archivoId, $estudianteId);
        if (!$archivo) {
            return [
                'success' => false,
                'error' => 'Acceso denegado o archivo no existe',
                'code' => 403
            ];
        }
    
        // 3. Construir ruta física (consistente con guardarArchivo())
        $rutaRelativa = $archivo['archivo_url']; // Ej: "chat/chat_123/abc123.pdf"
        $rutaCompleta = realpath(__DIR__ . '/../../uploads/' . $rutaRelativa);
    
        // 4. Validar existencia del archivo (con protección contra directory traversal)
        if (!$rutaCompleta || !file_exists($rutaCompleta)) {
            return [
                'success' => false,
                'error' => 'Archivo no encontrado en el servidor',
                'code' => 404
            ];
        }
    
        // 5. Obtener tipo MIME real 
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $tipoReal = $finfo->file($rutaCompleta);
    
        // 6. Sanitizar nombre original 
        $nombreOriginal = preg_replace('/[^a-zA-Z0-9._-]/', '', basename($archivo['archivo_url']));
    
        return [
            'success' => true,
            'ruta' => $rutaCompleta,
            'tipo_mime' => $tipoReal,
            'nombre_original' => $nombreOriginal
        ];
    }

}