<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Clase Estudiante
 *
 * Maneja operaciones relacionadas con el estudiante.
 *
 * @package Models
 * @author JOse Vargas
 * @version 1.0
 * 
 */
class Estudiante {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtiene los docentes de las clases en las que está matriculado el estudiante
     * 
     * @param int $estudianteId
     * @return array
     */

    /*
    Ejemplo de respuesta
        {
    "success": true,
    "data": [
        {
            "clase_id": 15,
            "codigo_clase": "MAT-101",
            "nombre_clase": "Matemáticas Básicas",
            "docente_id": 23,
            "nombre_docente": "María",
            "apellido_docente": "González",
            "correo_docente": "maria.gonzalez@universidad.edu"
        },
        {
            "clase_id": 18,
            "codigo_clase": "FIS-201",
            "nombre_clase": "Física Moderna",
            "docente_id": 45,
            "nombre_docente": "Carlos",
            "apellido_docente": "Martínez",
            "correo_docente": "carlos.martinez@universidad.edu"
        }
    ]
}
    */
    public function obtenerDocentesDeClases($estudianteId) {
        $sql = "SELECT 
                    c.clase_id,
                    c.codigo AS codigo_clase,
                    c.nombre AS nombre_clase,
                    d.docente_id,
                    d.nombre AS nombre_docente,
                    d.apellido AS apellido_docente,
                    d.correo AS correo_docente
                FROM Matricula m
                INNER JOIN Seccion s ON m.seccion_id = s.seccion_id
                INNER JOIN Clase c ON s.clase_id = c.clase_id
                INNER JOIN Docente d ON s.docente_id = d.docente_id
                WHERE m.estudiante_id = ?
                GROUP BY c.clase_id, d.docente_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $estudianteId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $docentes = [];
        
        while ($row = $result->fetch_assoc()) {
            $docentes[] = $row;
        }
        
        return $docentes;
    }
    public function obtenerPerfilEstudiante($estudianteId) {
        $sql = "SELECT 
                    e.estudiante_id,
                    e.nombre,
                    e.apellido,
                    e.identidad,
                    e.correo_personal,
                    e.telefono,
                    e.direccion,
                    e.indice_global,
                    e.indice_periodo,
                    c.nombre AS centro,
                    u.username,
                    GROUP_CONCAT(ca.nombre SEPARATOR ', ') AS carreras
                FROM Estudiante e
                INNER JOIN Usuario u ON e.usuario_id = u.usuario_id
                INNER JOIN Centro c ON e.centro_id = c.centro_id
                LEFT JOIN EstudianteCarrera ec ON e.estudiante_id = ec.estudiante_id
                LEFT JOIN Carrera ca ON ec.carrera_id = ca.carrera_id
                WHERE e.estudiante_id = ?
                GROUP BY e.estudiante_id";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $estudianteId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Estudiante no encontrado");
        }
        
        return $result->fetch_assoc();
    }
}
?>
