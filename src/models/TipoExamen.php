<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Clase TipoExamen
 *
 * Maneja operaciones relacionadas con los tipos de examen.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class TipoExamen {
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;

    /**
     * Constructor que establece la conexión a la base de datos.
     */
    public function __construct(){
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Crea un nuevo tipo de examen.
     *
     * @param string $nombre Nombre del tipo de examen.
     * @param float $nota_minima Nota mínima para aprobar el examen.
     * @return array Detalles del tipo de examen creado, incluyendo el ID.
     * @throws Exception Si ocurre un error al ejecutar la consulta.
     */
    public function crearTipoExamen($nombre, $nota_minima) {
        $sql = "INSERT INTO TipoExamen (nombre, nota_minima) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sd", $nombre, $nota_minima);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            return [
                'message' => 'Tipo de examen creado exitosamente.',
                'tipo_examen_id' => $stmt->insert_id
            ];
        } else {
            throw new Exception("Error al crear el tipo de examen.");
        }
    }

    /**
     * Asocia un examen con una carrera.
     * 
     * @param int $examen_id ID del examen
     * @param int $carrera_id ID de la carrera
     * @return array Resultado de la operación
     */
    public function asociarExamenCarrera($examen_id, $carrera_id) {
        // Verificar que el examen y la carrera existan
        $sqlExamen = "SELECT * FROM Examen WHERE examen_id = ?";
        $stmtExamen = $this->conn->prepare($sqlExamen);
        $stmtExamen->bind_param("i", $examen_id);
        $stmtExamen->execute();
        $resultExamen = $stmtExamen->get_result();

        if ($resultExamen->num_rows == 0) {
            throw new Exception("El examen con ID $examen_id no existe.");
        }

        $sqlCarrera = "SELECT * FROM Carrera WHERE carrera_id = ?";
        $stmtCarrera = $this->conn->prepare($sqlCarrera);
        $stmtCarrera->bind_param("i", $carrera_id);
        $stmtCarrera->execute();
        $resultCarrera = $stmtCarrera->get_result();

        if ($resultCarrera->num_rows == 0) {
            throw new Exception("La carrera con ID $carrera_id no existe.");
        }

        // Insertar la asociación
        $sql = "INSERT INTO ExamenCarrera (examen_id, carrera_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $examen_id, $carrera_id);
        $stmt->execute();

        return [
            'message' => 'Examen asociado a la carrera correctamente.',
            'examen_id' => $examen_id,
            'carrera_id' => $carrera_id
        ];
    }

    /**
     * Modifica los detalles de un examen.
     * 
     * @param int $examen_id ID del examen.
     * @param string $nombre Nuevo nombre del examen (opcional).
     * @param float $nota_minima Nueva nota mínima (opcional).
     * @return array Resultado de la operación.
     */
    public function modificarExamen($examen_id, $nombre = null, $nota_minima = null) {
        // Verificar si el examen existe
        $sqlExamen = "SELECT * FROM Examen WHERE examen_id = ?";
        $stmtExamen = $this->conn->prepare($sqlExamen);
        $stmtExamen->bind_param("i", $examen_id);
        $stmtExamen->execute();
        $resultExamen = $stmtExamen->get_result();

        if ($resultExamen->num_rows == 0) {
            throw new Exception("El examen con ID $examen_id no existe.");
        }

        // Si se pasa un nombre nuevo, actualizamos el nombre
        if ($nombre !== null) {
            $sqlUpdateNombre = "UPDATE Examen SET nombre = ? WHERE examen_id = ?";
            $stmtUpdateNombre = $this->conn->prepare($sqlUpdateNombre);
            $stmtUpdateNombre->bind_param("si", $nombre, $examen_id);
            $stmtUpdateNombre->execute();
        }

        // Si se pasa una nueva nota mínima, la actualizamos
        if ($nota_minima !== null) {
            $sqlUpdateNota = "UPDATE Examen SET nota_minima = ? WHERE examen_id = ?";
            $stmtUpdateNota = $this->conn->prepare($sqlUpdateNota);
            $stmtUpdateNota->bind_param("di", $nota_minima, $examen_id);
            $stmtUpdateNota->execute();
        }

        return [
            'message' => 'Examen actualizado correctamente.',
            'examen_id' => $examen_id
        ];
    }

    /**
     * Desasocia múltiples exámenes de una carrera.
     *
     * @param array $examen_ids Array de IDs de los exámenes.
     * @param int $carrera_id ID de la carrera.
     * @return array Resultado de la operación.
     */
    public function desasociarExamenesDeCarrera($examen_ids, $carrera_id) {
        // Comprobar que hay exámenes a desasociar
        if (empty($examen_ids)) {
            throw new Exception("Debe proporcionar al menos un examen para desasociar.");
        }

        // Crear el marcador de lugar para la consulta IN
        $placeholders = implode(',', array_fill(0, count($examen_ids), '?'));

        // Verificar que todas las relaciones existan antes de intentar eliminarlas
        $sqlCheck = "SELECT * FROM ExamenCarrera WHERE examen_id IN ($placeholders) AND carrera_id = ?";
        
        // Combinar los IDs de los exámenes y el ID de la carrera en un solo array
        $params = array_merge($examen_ids, [$carrera_id]);

        // Preparamos la consulta y vinculamos los parámetros
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bind_param(str_repeat('i', count($examen_ids)) . 'i', ...$params);
        $stmtCheck->execute();
        $result = $stmtCheck->get_result();

        if ($result->num_rows == 0) {
            throw new Exception("No existen relaciones entre estos exámenes y la carrera especificada.");
        }

        // Eliminar las relaciones entre los exámenes y la carrera
        $sqlDelete = "DELETE FROM ExamenCarrera WHERE examen_id IN ($placeholders) AND carrera_id = ?";
        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->bind_param(str_repeat('i', count($examen_ids)) . 'i', ...$params);
        $stmtDelete->execute();

        return [
            'message' => 'Exámenes desasociados correctamente de la carrera.',
            'examen_ids' => $examen_ids,
            'carrera_id' => $carrera_id
        ];
    }
}
?>