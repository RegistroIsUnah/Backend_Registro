<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Clase Aspirante
 *
 * Maneja la inserción de un aspirante mediante el procedimiento almacenado SP_insertarAspirante.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.1
 * 
 */
class Aspirante {
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;

    /**
     * Constructor de la clase Aspirante.
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Inserta un aspirante utilizando el procedimiento almacenado SP_insertarAspirante.
     *
     * @param string $nombre
     * @param string $apellido
     * @param string $identidad
     * @param string $telefono
     * @param string $correo
     * @param string $fotoRuta Ruta de la foto del aspirante.
     * @param string $fotodniRuta Ruta de la foto del DNI.
     * @param int $carrera_principal_id
     * @param int|null $carrera_secundaria_id
     * @param int $centro_id
     * @param string $certificadoRuta Ruta del certificado subido.
     * @return string Número de solicitud generado.
     * @throws Exception Si ocurre un error durante la inserción.
     */
    public function insertarAspirante($nombre, $apellido, $identidad, $telefono, $correo, $fotoRuta, $fotodniRuta, $carrera_principal_id, $carrera_secundaria_id, $centro_id, $certificadoRuta) {
        // Se esperan 11 parámetros, por lo tanto 11 marcadores
        $stmt = $this->conn->prepare("CALL SP_insertarAspirante(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
        // La cadena de tipos es: 7 strings, 3 enteros, 1 string = "sssssssiiis"
        if (!$stmt->bind_param("sssssssiiis", 
            $nombre, 
            $apellido, 
            $identidad, 
            $telefono, 
            $correo, 
            $fotoRuta,
            $fotodniRuta,
            $carrera_principal_id, 
            $carrera_secundaria_id, 
            $centro_id, 
            $certificadoRuta
        )) {
            throw new Exception("Error vinculando parámetros: " . $stmt->error);
        }
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando la consulta: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $numSolicitud = null;
        if ($result) {
            $row = $result->fetch_assoc();
            $numSolicitud = $row['numSolicitud'] ?? null;
            $result->free();
        }
        $stmt->close();
        if (!$numSolicitud) {
            throw new Exception("No se obtuvo el número de solicitud");
        }
        return $numSolicitud;
    }
}
?>
