<?php
require_once __DIR__ . '/../config/DataBase.php';

/**
 * Clase Chat
 *
 * Maneja la interacción con la tabla Chat de la base de datos.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class Chat {
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;
    
     /**
     * Constructor de la clase Departamento.
     *
     * Establece la conexión con la base de datos.
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Crear un nuevo chat (individual o grupal)
     */
    public function crearChat($esGrupal, $nombre = null, $participantes) {
        $this->conn->begin_transaction();
        
        try {
            // Crear el chat
            $stmt = $this->conn->prepare("INSERT INTO Chat (es_grupal, nombre, fecha_creacion) VALUES (?, ?, NOW())");
            $stmt->bind_param("is", $esGrupal, $nombre);
            $stmt->execute();
            $chatId = $this->conn->insert_id;
            
            // Agregar participantes
            foreach ($participantes as $numeroCuenta) {
                $estudianteId = $this->obtenerIdPorNumeroCuenta($numeroCuenta);
                if (!$estudianteId) continue;
                
                $stmt = $this->conn->prepare("INSERT INTO ChatParticipante (chat_id, estudiante_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $chatId, $estudianteId);
                $stmt->execute();
            }
            
            $this->conn->commit();
            return ['success' => true, 'chat_id' => $chatId];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Enviar un mensaje
     */
    public function enviarMensaje($chatId, $numeroCuenta, $contenido, $archivos = []) {
        $this->conn->begin_transaction();
        
        try {
            $estudianteId = $this->obtenerIdPorNumeroCuenta($numeroCuenta);
            if (!$estudianteId) {
                throw new Exception("Estudiante no encontrado");
            }
            
            // Insertar mensaje
            $stmt = $this->conn->prepare("INSERT INTO Mensaje (chat_id, estudiante_id, contenido, fecha_envio) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("iis", $chatId, $estudianteId, $contenido);
            $stmt->execute();
            $mensajeId = $this->conn->insert_id;
            
            // Procesar archivos adjuntos
            foreach ($archivos as $archivo) {
                $ruta = $this->guardarArchivo($archivo, $chatId); // Pasamos el chatId aquí
                $stmt = $this->conn->prepare("INSERT INTO ArchivoChat (mensaje_id, archivo_url, tipo) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $mensajeId, $ruta, $archivo['type']);
                $stmt->execute();
            }
            
            $this->conn->commit();
            return ['success' => true, 'mensaje_id' => $mensajeId];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Obtener chats de un estudiante
     */
    public function obtenerChats($numeroCuenta) {
        $estudianteId = $this->obtenerIdPorNumeroCuenta($numeroCuenta);
        if (!$estudianteId) {
            return ['error' => 'Estudiante no encontrado'];
        }
        
        $query = "SELECT c.chat_id, c.es_grupal, c.nombre, c.fecha_creacion, 
                  MAX(m.fecha_envio) as ultima_actividad
                  FROM ChatParticipante cp
                  JOIN Chat c ON cp.chat_id = c.chat_id
                  LEFT JOIN Mensaje m ON c.chat_id = m.chat_id
                  WHERE cp.estudiante_id = ?
                  GROUP BY c.chat_id
                  ORDER BY ultima_actividad DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $estudianteId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $chats = [];
        while ($row = $result->fetch_assoc()) {
            $chats[] = $row;
        }
        
        return $chats;
    }
    
    /**
     * Obtener mensajes de un chat
     */
    public function obtenerMensajes($chatId, $numeroCuenta, $limit = 50, $offset = 0) {
        $estudianteId = $this->obtenerIdPorNumeroCuenta($numeroCuenta);
        if (!$estudianteId) {
            return ['error' => 'Estudiante no encontrado'];
        }
        
        // Verificar que el estudiante pertenece al chat
        $stmt = $this->conn->prepare("SELECT 1 FROM ChatParticipante WHERE chat_id = ? AND estudiante_id = ?");
        $stmt->bind_param("ii", $chatId, $estudianteId);
        $stmt->execute();
        
        if (!$stmt->get_result()->num_rows) {
            return ['error' => 'No tienes acceso a este chat'];
        }
        
        // Obtener mensajes
        $query = "SELECT m.mensaje_id, m.estudiante_id, e.numero_cuenta, e.nombre, e.apellido, 
                  m.contenido, m.fecha_envio, a.archivo_url, a.tipo
                  FROM Mensaje m
                  JOIN Estudiante e ON m.estudiante_id = e.estudiante_id
                  LEFT JOIN ArchivoChat a ON m.mensaje_id = a.mensaje_id
                  WHERE m.chat_id = ?
                  ORDER BY m.fecha_envio DESC
                  LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iii", $chatId, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $mensajes = [];
        while ($row = $result->fetch_assoc()) {
            $mensajes[] = $row;
        }
        
        return $mensajes;
    }
    
    /**
     * Obtener participantes de un chat
     */
    public function obtenerParticipantes($chatId) {
        $query = "SELECT e.estudiante_id, e.numero_cuenta, e.nombre, e.apellido
                  FROM ChatParticipante cp
                  JOIN Estudiante e ON cp.estudiante_id = e.estudiante_id
                  WHERE cp.chat_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $chatId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $participantes = [];
        while ($row = $result->fetch_assoc()) {
            $participantes[] = $row;
        }
        
        return $participantes;
    }
    
    /**
     * Agregar participantes a un chat grupal
     */
    public function agregarParticipantes($chatId, $participantes) {
        $this->conn->begin_transaction();
        
        try {
            foreach ($participantes as $numeroCuenta) {
                $estudianteId = $this->obtenerIdPorNumeroCuenta($numeroCuenta);
                if (!$estudianteId) continue;
                
                $stmt = $this->conn->prepare("INSERT INTO ChatParticipante (chat_id, estudiante_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $chatId, $estudianteId);
                $stmt->execute();
            }
            
            $this->conn->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    public function obtenerIdPorNumeroCuenta($numeroCuenta) {
        $stmt = $this->conn->prepare("SELECT estudiante_id FROM Estudiante WHERE numero_cuenta = ?");
        $stmt->bind_param("s", $numeroCuenta);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        $row = $result->fetch_assoc();
        return $row['estudiante_id'];
    }


    public function obtenerArchivoParaUsuario($archivoId, $usuarioId) {
        $query = $this->conn->prepare("
            SELECT a.archivo_url, a.tipo
            FROM ArchivoChat a
            JOIN Mensaje m ON a.mensaje_id = m.mensaje_id
            JOIN ChatParticipante cp ON m.chat_id = cp.chat_id
            WHERE a.archivo_id = ? AND cp.estudiante_id = ?
        ");
        $query->bind_param("ii", $archivoId, $usuarioId);
        $query->execute();
        $result = $query->get_result()->fetch_assoc();

        // Sanitizar la ruta (opcional, pero recomendado)
        if ($result && strpos($result['archivo_url'], '../') !== false) {
            return null; // Bloquea rutas maliciosas
        }

        return $result;
    }

    private function guardarArchivo(array $archivo, int $chatId): string {
        // Configuración de parámetros
        $config = [
            'uploadBaseDir' => __DIR__ . '/../../uploads/chat/',
            'allowedExtensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'mp4', 'mov'],
            'maxFileSize' => 10 * 1024 * 1024, // 10MB
        ];
    
        // 1. Validar archivo
        if (!isset($archivo['tmp_name'])) {
            throw new Exception("Archivo no recibido correctamente");
        }
    
        // 2. Validar extensión
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $config['allowedExtensions'])) {
            throw new Exception("Tipo de archivo no permitido. Formatos aceptados: " . implode(', ', $config['allowedExtensions']));
        }
    
        // 3. Validar tamaño
        if ($archivo['size'] > $config['maxFileSize']) {
            throw new Exception("El archivo excede el límite de " . ($config['maxFileSize'] / 1024 / 1024) . "MB");
        }
    
        // 4. Crear directorio del chat (si no existe)
        $chatDir = $config['uploadBaseDir'] . 'chat_' . $chatId . '/';
        if (!is_dir($chatDir)) {
            if (!mkdir($chatDir, 0755, true)) {
                throw new Exception("No se pudo crear el directorio para el chat");
            }
        }
    
        // 5. Generar nombre único y seguro
        $nombreUnico = bin2hex(random_bytes(8)) . '.' . $extension; // Más seguro que uniqid()
        $rutaCompleta = $chatDir . $nombreUnico;
    
        // 6. Mover el archivo
        if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            throw new Exception("Error al guardar el archivo en el servidor");
        }
    
        // 7. Retornar ruta relativa (para almacenar en BD)
        return 'chat/chat_' . $chatId . '/' . $nombreUnico;
    }
}
?>