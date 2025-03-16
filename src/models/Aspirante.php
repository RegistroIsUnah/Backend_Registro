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


    /**
     *Obtiene la lista de los aspirantes admitidos 
     * @return string JSON con la lista de los aspirantes admitidos.
     * LOS CAMPOS QUE SE MUESTRAN SON:
     * aspirante_id
     *identidad
     *nombre
     *apellido
     *correo
     *telefono
     *numsolicitud
     *carrera_principal
     *carrera_secundaria
     *centro
     *Los datos despues de ser obtenidos se pasaron a un csv
     */
    /*
        Ejemplo de respuesta:
            {
            "success": true,
            "data": [
                {
                    "aspirante_id": 1,
                    "identidad": "0801199901234",
                    "nombre": "Juan",
                    "apellido": "Pérez",
                    "correo": "juan@example.com",
                    "telefono": "98765432",
                    "numSolicitud": "SOL-2023-001",
                    "carrera_principal": "Ingeniería en Sistemas",
                    "carrera_secundaria": null,
                    "centro": "Campus Central"
                },
                {
                    "aspirante_id": 2,
                    "identidad": "0801199905678",
                    "nombre": "María",
                    "apellido": "García",
                    "correo": "maria@example.com",
                    "telefono": "98765433",
                    "numSolicitud": "SOL-2023-002",
                    "carrera_principal": "Medicina",
                    "carrera_secundaria": "Enfermería",
                    "centro": "Campus Norte"
                }
            ],
            "error": ""
        }
     */

    public function obtenerAspirantesAdmitidos() {
        $sql = "SELECT 
                    A.aspirante_id,
                    A.identidad,
                    A.nombre,
                    A.apellido,
                    A.correo,
                    A.telefono,
                    A.numSolicitud,
                    C_principal.nombre AS carrera_principal,
                    C_secundaria.nombre AS carrera_secundaria,
                    Cen.nombre AS centro
                FROM Aspirante A
                INNER JOIN Carrera C_principal ON A.carrera_principal_id = C_principal.carrera_id
                LEFT JOIN Carrera C_secundaria ON A.carrera_secundaria_id = C_secundaria.carrera_id
                INNER JOIN Centro Cen ON A.centro_id = Cen.centro_id
                WHERE A.estado = 'ADMITIDO'";
        
        $result = $this->conn->query($sql);
        
        $response = [
            'success' => false,
            'data' => [],
            'error' => ''
        ];
        
        if (!$result) {
            $response['error'] = "Error en la consulta: " . $this->conn->error;
            return json_encode($response);
        }
        
        $aspirantes = [];
        while ($row = $result->fetch_assoc()) {
            $aspirantes[] = $row;
        }
        
        $response['success'] = true;
        $response['data'] = $aspirantes;
        
        return json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Obtiene la lista de los aspirantes admitidos en formato CSV
     * Usando como base la funcion obtenerAspirantesAdmitidos
     * @return string CSV con la lista de los aspirantes admitidos.
     */

     public function exportarAspirantesAdmitidosCSV() {
        // Consulta SQL para obtener los aspirantes admitidos
        $sql = "SELECT 
                    A.aspirante_id,
                    A.identidad,
                    A.nombre,
                    A.apellido,
                    A.correo,
                    A.telefono,
                    A.numSolicitud,
                    C_principal.nombre AS carrera_principal,
                    C_secundaria.nombre AS carrera_secundaria,
                    Cen.nombre AS centro
                FROM Aspirante A
                INNER JOIN Carrera C_principal ON A.carrera_principal_id = C_principal.carrera_id
                LEFT JOIN Carrera C_secundaria ON A.carrera_secundaria_id = C_secundaria.carrera_id
                INNER JOIN Centro Cen ON A.centro_id = Cen.centro_id
                WHERE A.estado = 'ADMITIDO'";
    
        // Ejecutar la consulta
        $result = $this->conn->query($sql);
    
        if (!$result) {
            throw new Exception("Error en la consulta: " . $this->conn->error);
        }
    
        // Configurar salida directa a PHP output
        $output = fopen('php://output', 'w');
    
        // Escribir la cabecera del CSV
        fputcsv($output, [
            'aspirante_id',
            'identidad',
            'nombre',
            'apellido',
            'correo',
            'telefono',
            'numSolicitud',
            'carrera_principal',
            'carrera_secundaria',
            'centro'
        ]);
    
        // Escribir los datos de los aspirantes
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }
    
        // Cerrar el archivo
        fclose($output);
    }
    
}
?>
