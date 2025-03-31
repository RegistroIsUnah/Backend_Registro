<?php
require_once __DIR__ . '/../modules/config/DataBase.php';
/**
 * Clase Revisor
 *
 * Maneja la inserción de un aspirante mediante el procedimiento almacenado SP_insertarAspirante.
 *
 * @package Models
 * @author Jose Vargas
 * @version 1.0
 * 
 */
class Revisor {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Procesa solicitud para ser revisor de admisiones
     * @param int $estudianteId
     * @param int $carreraId
     * @return bool
     * @throws Exception
     */
    public function procesarSolicitudRevisor($estudianteId, $carreraId) {
        // Verificar si ya existe una solicitud
        $sqlVerificar = "SELECT * FROM AplicanteRevisor 
                        WHERE estudiante_id = ? AND carrera_id = ?";
        $stmt = $this->conn->prepare($sqlVerificar);
        $stmt->bind_param("ii", $estudianteId, $carreraId);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("Ya existe una solicitud pendiente para esta carrera");
        }

        // Insertar nueva solicitud
        $sqlInsertar = "INSERT INTO AplicanteRevisor 
                       (estudiante_id, carrera_id, fecha_solicitud, estado_solicitud) 
                       VALUES (?, ?, NOW(), 'APROBADO')";
        
        $stmt = $this->conn->prepare($sqlInsertar);
        $stmt->bind_param("ii", $estudianteId, $carreraId);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al procesar solicitud: " . $stmt->error);
        }
        
        // Otorgar permisos de revisor
        $sqlPermisos = "INSERT INTO Revisor (estudiante_id, fecha_aprobacion, usuario_id)
                       VALUES (?, NOW(), ?)";
        
        $stmt = $this->conn->prepare($sqlPermisos);
        $stmt->bind_param("ii", $estudianteId, $_SESSION['usuario_id']);
        
        return $stmt->execute();
    }
}
?>