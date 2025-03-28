<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Modelo para Libro.
 *
 * Encapsula la lógica para registrar un libro y sus asociaciones:
 * - Inserta el libro en la tabla Libro.
 * - Inserta/Obtiene los tags y los asocia en TagLibro.
 * - Inserta/Obtiene los autores y los asocia en LibroAutor.
 * - Asocia el libro a una clase mediante ClaseLibro.
 *
 * @package Models
 * @author Ruben Diaz
 * @version 1.0
 * 
 */
class Libro {
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $conn;
    
    public function __construct(){
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
/**
 * Registra un libro y sus asociaciones.
 *
 * @param string $titulo Título del libro.
 * @param string $editorial Editorial del libro.
 * @param string $libro_url Ruta donde se guarda el archivo.
 * @param string $fecha_publicacion Fecha de publicación (YYYY-MM-DD).
 * @param string $isbn_libro ISBN del libro.
 * @param string $descripcion Descripción del libro.
 * @param string $estado Estado del libro ('ACTIVO' o 'INACTIVO').
 * @param array $tags Array de tag IDs.
 * @param array $autores Array de arrays con claves 'nombre' y 'apellido'.
 * @param int $clase_id ID de la clase a asociar (0 si no se asocia).
 * @return int ID del libro insertado.
 * @throws Exception Si ocurre un error en la transacción.
 */
public function registrarLibro($titulo, $editorial, $libro_url, $fecha_publicacion, $isbn_libro, $descripcion, $estado, $tags, $autores, $clase_id) {
    $this->conn->begin_transaction();
    try {
        // Obtener el estado_libro_id para el estado proporcionado (ACTIVO o INACTIVO)
        $stmt = $this->conn->prepare("SELECT estado_libro_id FROM EstadoLibro WHERE nombre = ?");
        $stmt->bind_param("s", $estado);
        if (!$stmt->execute()) {
            throw new Exception("Error obteniendo estado del libro: " . $stmt->error);
        }
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            throw new Exception("Estado de libro no válido.");
        }
        $row = $result->fetch_assoc();
        $estado_libro_id = $row['estado_libro_id'];
        $stmt->close();

        // Insertar en la tabla Libro, ahora incluyendo el campo isbn_libro
        $stmt = $this->conn->prepare("INSERT INTO Libro (titulo, editorial, libro_url, fecha_publicacion, isbn_libro, descripcion, estado_libro_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparando inserción en Libro: " . $this->conn->error);
        }
        $stmt->bind_param("ssssssi", $titulo, $editorial, $libro_url, $fecha_publicacion, $isbn_libro, $descripcion, $estado_libro_id);
        if (!$stmt->execute()) {
            throw new Exception("Error insertando en Libro: " . $stmt->error);
        }
        $libro_id = $stmt->insert_id;
        $stmt->close();

        // Procesar Tags (se espera un array de tag IDs)
        if (!empty($tags) && is_array($tags)) {
            foreach ($tags as $tagId) {
                $tagId = (int)$tagId;
                $stmt = $this->conn->prepare("INSERT INTO TagLibro (libro_id, tag_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $libro_id, $tagId);
                if (!$stmt->execute()) {
                    throw new Exception("Error insertando en TagLibro: " . $stmt->error);
                }
                $stmt->close();
            }
        }

        // Procesar Autores
        if (!empty($autores) && is_array($autores)) {
            foreach ($autores as $autor) {
                if (!isset($autor['nombre']) || !isset($autor['apellido'])) continue;
                $nombre = trim($autor['nombre']);
                $apellido = trim($autor['apellido']);
                if (empty($nombre) || empty($apellido)) continue;
                
                $stmt = $this->conn->prepare("SELECT autor_id FROM Autor WHERE nombre = ? AND apellido = ?");
                $stmt->bind_param("ss", $nombre, $apellido);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($autor_id);
                    $stmt->fetch();
                } else {
                    $stmt->close();
                    $stmt = $this->conn->prepare("INSERT INTO Autor (nombre, apellido) VALUES (?, ?)");
                    $stmt->bind_param("ss", $nombre, $apellido);
                    if (!$stmt->execute()) {
                        throw new Exception("Error insertando Autor: " . $stmt->error);
                    }
                    $autor_id = $stmt->insert_id;
                }
                $stmt->close();
                
                $stmt = $this->conn->prepare("INSERT INTO LibroAutor (libro_id, autor_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $libro_id, $autor_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error insertando en LibroAutor: " . $stmt->error);
                }
                $stmt->close();
            }
        }

        // Asociar el libro a una clase (si se proporciona)
        if ($clase_id > 0) {
            $stmt = $this->conn->prepare("INSERT INTO ClaseLibro (clase_id, libro_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $clase_id, $libro_id);
            if (!$stmt->execute()) {
                throw new Exception("Error insertando en ClaseLibro: " . $stmt->error);
            }
            $stmt->close();
        }

        $this->conn->commit();
        return $libro_id;
    } catch (Exception $e) {
        $this->conn->rollback();
        throw $e;
    }
}

  /**
     * Actualiza un libro y sus asociaciones de forma parcial.
     *
     * Todos los parámetros (excepto $libro_id) son opcionales; solo se actualizan los que se proporcionen.
     * Para los tags se espera un array de números (tag IDs).
     *
     * @param int $libro_id ID del libro a actualizar.
     * @param string|null $titulo
     * @param string|null $editorial
     * @param string|null $isbn_libro  
     * @param string|null $libro_url (ruta del archivo, si se sube uno nuevo)
     * @param string|null $fecha_publicacion (YYYY-MM-DD)
     * @param string|null $descripcion
     * @param array|null $tags Array de tag IDs (números) para asociar.
     * @param array|null $autores Array de arrays con claves 'nombre' y 'apellido'.
     * @param int|null $clase_id ID de la clase a asociar.
     * @param string|null $estado Estado del libro ('ACTIVO' o 'INACTIVO')
     * @return bool True si la actualización se realizó correctamente.
     * @throws Exception Si ocurre un error en la transacción.
     */
    public function actualizarLibro($libro_id, $titulo = null, $editorial = null, $isbn_libro = null, $libro_url = null, $fecha_publicacion = null, $descripcion = null, $tags = null, $autores = null, $clase_id = null, $estado = null) {
        $this->conn->begin_transaction();
        try {
            // 1. Obtener estado_libro_id para el estado proporcionado
            $estado_libro_id = null;
            if ($estado !== null) {
                $stmt = $this->conn->prepare("SELECT estado_libro_id FROM EstadoLibro WHERE nombre = ?");
                $stmt->bind_param("s", $estado);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $estado_libro_id = $row['estado_libro_id'];
                } else {
                    throw new Exception("Estado del libro no válido.");
                }
                $stmt->close();
            }

            // 2. Actualizar la tabla Libro (solo los campos proporcionados)
            $updateFields = [];
            $params = [];
            $paramTypes = "";
            
            if ($titulo !== null && $titulo !== "") {
                $updateFields[] = "titulo = ?";
                $params[] = $titulo;
                $paramTypes .= "s";
            }
            if ($editorial !== null && $editorial !== "") {
                $updateFields[] = "editorial = ?";
                $params[] = $editorial;
                $paramTypes .= "s";
            }
            // Nuevo: Verificar y actualizar ISBN si se proporciona
            if ($isbn_libro !== null && $isbn_libro !== "") {
                // Verificar duplicidad: ningún otro libro (con distinto libro_id) debe tener este ISBN.
                $stmt = $this->conn->prepare("SELECT COUNT(*) AS count FROM Libro WHERE isbn_libro = ? AND libro_id <> ?");
                $stmt->bind_param("si", $isbn_libro, $libro_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                if ($row['count'] > 0) {
                    throw new Exception("El ISBN ya se encuentra registrado en otro libro.");
                }
                $stmt->close();

                $updateFields[] = "isbn_libro = ?";
                $params[] = $isbn_libro;
                $paramTypes .= "s";
            }
            if ($libro_url !== null && $libro_url !== "") {
                $updateFields[] = "libro_url = ?";
                $params[] = $libro_url;
                $paramTypes .= "s";
            }
            if ($fecha_publicacion !== null && $fecha_publicacion !== "") {
                $updateFields[] = "fecha_publicacion = ?";
                $params[] = $fecha_publicacion;
                $paramTypes .= "s";
            }
            if ($descripcion !== null && $descripcion !== "") {
                $updateFields[] = "descripcion = ?";
                $params[] = $descripcion;
                $paramTypes .= "s";
            }
            if ($estado_libro_id !== null) {
                $updateFields[] = "estado_libro_id = ?";
                $params[] = $estado_libro_id;
                $paramTypes .= "i";
            }
            
            if (!empty($updateFields)) {
                $sql = "UPDATE Libro SET " . implode(", ", $updateFields) . " WHERE libro_id = ?";
                $params[] = $libro_id;
                $paramTypes .= "i";
                $stmt = $this->conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Error preparando actualización de Libro: " . $this->conn->error);
                }
                $stmt->bind_param($paramTypes, ...$params);
                if (!$stmt->execute()) {
                    throw new Exception("Error actualizando Libro: " . $stmt->error);
                }
                $stmt->close();
            }
            
            // 3. Actualizar Tags si se proporcionan (se espera array de tag IDs)
            if ($tags !== null && is_array($tags)) {
                // Eliminar asociaciones previas
                $stmt = $this->conn->prepare("DELETE FROM TagLibro WHERE libro_id = ?");
                $stmt->bind_param("i", $libro_id);
                $stmt->execute();
                $stmt->close();
                // Insertar nuevas asociaciones
                foreach ($tags as $tag_id) {
                    if (!is_numeric($tag_id)) {
                        throw new Exception("El tag_id '$tag_id' no es válido.");
                    }
                    $tag_id = (int)$tag_id;
                    $stmt = $this->conn->prepare("INSERT INTO TagLibro (libro_id, tag_id) VALUES (?, ?)");
                    $stmt->bind_param("ii", $libro_id, $tag_id);
                    if (!$stmt->execute()) {
                        throw new Exception("Error insertando en TagLibro: " . $stmt->error);
                    }
                    $stmt->close();
                }
            }

            // 4. Actualizar Autores si se proporcionan
            if ($autores !== null && is_array($autores)) {
                // Eliminar asociaciones previas
                $stmt = $this->conn->prepare("DELETE FROM LibroAutor WHERE libro_id = ?");
                $stmt->bind_param("i", $libro_id);
                $stmt->execute();
                $stmt->close();
                // Insertar nuevas asociaciones
                foreach ($autores as $autor) {
                    if (!isset($autor['nombre']) || !isset($autor['apellido'])) continue;
                    $nombre = trim($autor['nombre']);
                    $apellido = trim($autor['apellido']);
                    if (empty($nombre) || empty($apellido)) continue;
                    
                    $stmt = $this->conn->prepare("SELECT autor_id FROM Autor WHERE nombre = ? AND apellido = ?");
                    $stmt->bind_param("ss", $nombre, $apellido);
                    $stmt->execute();
                    $stmt->store_result();
                    if ($stmt->num_rows > 0) {
                        $stmt->bind_result($autor_id);
                        $stmt->fetch();
                    } else {
                        $stmt->close();
                        $stmt = $this->conn->prepare("INSERT INTO Autor (nombre, apellido) VALUES (?, ?)");
                        $stmt->bind_param("ss", $nombre, $apellido);
                        if (!$stmt->execute()) {
                            throw new Exception("Error insertando Autor: " . $stmt->error);
                        }
                        $autor_id = $stmt->insert_id;
                    }
                    $stmt->close();
                    
                    $stmt = $this->conn->prepare("INSERT INTO LibroAutor (libro_id, autor_id) VALUES (?, ?)");
                    $stmt->bind_param("ii", $libro_id, $autor_id);
                    if (!$stmt->execute()) {
                        throw new Exception("Error insertando en LibroAutor: " . $stmt->error);
                    }
                    $stmt->close();
                }
            }

            // 5. Actualizar ClaseLibro si se proporciona clase_id
            if ($clase_id !== null && $clase_id > 0) {
                // Eliminar asociación previa (asumimos una sola asociación)
                $stmt = $this->conn->prepare("DELETE FROM ClaseLibro WHERE libro_id = ?");
                $stmt->bind_param("i", $libro_id);
                $stmt->execute();
                $stmt->close();
                // Insertar nueva asociación
                $stmt = $this->conn->prepare("INSERT INTO ClaseLibro (clase_id, libro_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $clase_id, $libro_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error insertando en ClaseLibro: " . $stmt->error);
                }
                $stmt->close();
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
        
    /**
     * Obtiene los detalles de un libro, incluidos sus autores y tags para Estudiante.
     *
     * @param int $libro_id ID del libro.
     * @return array Detalles del libro con sus autores y tags.
     * @throws Exception Si el libro no se encuentra o está inactivo.
     */
    public function obtenerLibro($libro_id) {
        // 1. Obtener estado_libro_id para 'ACTIVO'
        $stmt = $this->conn->prepare("SELECT estado_libro_id FROM EstadoLibro WHERE nombre = 'ACTIVO'");
        if (!$stmt) {
            throw new Exception("Error preparando la consulta de estado: " . $this->conn->error);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            throw new Exception("Estado 'ACTIVO' no encontrado en la base de datos.");
        }
        $row = $result->fetch_assoc();
        $estado_libro_id = $row['estado_libro_id'];
        $stmt->close();

        // 2. Obtener los datos principales del libro (solo si está ACTIVO)
        $sql = "SELECT libro_id, titulo, editorial, libro_url, fecha_publicacion, descripcion, estado_libro_id
                FROM Libro
                WHERE libro_id = ? AND estado_libro_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
        $stmt->bind_param("ii", $libro_id, $estado_libro_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $libro = $result->fetch_assoc();
        $stmt->close();
        
        if (!$libro) {
            throw new Exception("Libro no encontrado o inactivo.");
        }
        
        // 3. Obtener los autores asociados al libro
        $sqlAuthors = "SELECT a.autor_id, a.nombre, a.apellido
                    FROM LibroAutor la
                    INNER JOIN Autor a ON la.autor_id = a.autor_id
                    WHERE la.libro_id = ?";
        $stmt = $this->conn->prepare($sqlAuthors);
        $stmt->bind_param("i", $libro_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $autores = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $libro['autores'] = $autores;
        
        // 4. Obtener los tags asociados al libro
        $sqlTags = "SELECT t.tag_id, t.tag_nombre
                    FROM TagLibro tl
                    INNER JOIN Tag t ON tl.tag_id = t.tag_id
                    WHERE tl.libro_id = ?";
        $stmt = $this->conn->prepare($sqlTags);
        $stmt->bind_param("i", $libro_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $tags = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $libro['tags'] = $tags;
        
        return $libro;
    }


    /**
     * Obtiene los detalles de un libro, incluidos sus autores y tags, sin filtrar por estado.
     *
     * @param int $libro_id ID del libro.
     * @return array Detalles del libro con sus autores y tags.
     * @throws Exception Si el libro no se encuentra.
     */
    public function obtenerLibroCompleto($libro_id) {
        // 1. Obtener los datos principales del libro (sin filtrar por estado)
        $sql = "SELECT libro_id, titulo, editorial, libro_url, fecha_publicacion, descripcion, estado_libro_id
                FROM Libro 
                WHERE libro_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
        $stmt->bind_param("i", $libro_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $libro = $result->fetch_assoc();
        $stmt->close();
        
        if (!$libro) {
            throw new Exception("Libro no encontrado.");
        }
        
        // 2. Obtener los autores asociados al libro
        $sqlAuthors = "SELECT a.autor_id, a.nombre, a.apellido
                    FROM LibroAutor la
                    INNER JOIN Autor a ON la.autor_id = a.autor_id
                    WHERE la.libro_id = ?";
        $stmt = $this->conn->prepare($sqlAuthors);
        $stmt->bind_param("i", $libro_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $autores = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $libro['autores'] = $autores;
        
        // 3. Obtener los tags asociados al libro
        $sqlTags = "SELECT t.tag_id, t.tag_nombre
                    FROM TagLibro tl
                    INNER JOIN Tag t ON tl.tag_id = t.tag_id
                    WHERE tl.libro_id = ?";
        $stmt = $this->conn->prepare($sqlTags);
        $stmt->bind_param("i", $libro_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $tags = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $libro['tags'] = $tags;
        
        return $libro;
    }


    /**
     * Obtiene todas las clases de un departamento y los libros asociados a cada clase.
     *
     * La consulta junta la tabla Clase (filtrando por dept_id), la tabla ClaseLibro y la tabla Libro.
     * Se agrupan los resultados por clase para devolver un arreglo donde cada entrada representa una clase con sus libros.
     *
     * @param int $departamentoId ID del departamento.
     * @return array Arreglo de clases con sus libros. Ejemplo:
     *   [
     *     {
     *       "clase_id": 1,
     *       "clase_nombre": "Matemáticas I",
     *       "libros": [
     *          { "libro_id": 3, "titulo": "Álgebra", "editorial": "Editorial X", "libro_url": "/uploads/libros/...", ... },
     *          { "libro_id": 5, "titulo": "Cálculo", "editorial": "Editorial Y", "libro_url": "/uploads/libros/...", ... }
     *       ]
     *     },
     *     ...
     *   ]
     * @throws Exception Si ocurre un error en la consulta.
     */
    public function obtenerLibrosPorDepartamento($departamentoId) {
        $sql = "SELECT 
                    c.clase_id,
                    c.nombre AS clase_nombre,
                    l.libro_id,
                    l.titulo,
                    l.editorial,
                    l.libro_url,
                    l.fecha_publicacion,
                    l.descripcion,
                    estl.nombre AS estado
                FROM Clase c
                INNER JOIN ClaseLibro cl ON c.clase_id = cl.clase_id
                INNER JOIN Libro l ON cl.libro_id = l.libro_id
                INNER JOIN EstadoLibro estl ON estl.estado_libro_id = l.estado_libro_id
                WHERE c.dept_id = ?
                ORDER BY c.clase_id
                LIMIT 100";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
        $stmt->bind_param("i", $departamentoId);
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando la consulta: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // Agrupar los resultados por clase
        $clases = [];
        foreach ($data as $row) {
            $clase_id = $row['clase_id'];
            if (!isset($clases[$clase_id])) {
                $clases[$clase_id] = [
                    'clase_id' => $clase_id,
                    'clase_nombre' => $row['clase_nombre'],
                    'libros' => []
                ];
            }
            // Agregar libro a la clase, incluyendo el campo editorial
            $clases[$clase_id]['libros'][] = [
                'libro_id' => $row['libro_id'],
                'titulo' => $row['titulo'],
                'editorial' => $row['editorial'],
                'libro_url' => $row['libro_url'],
                'fecha_publicacion' => $row['fecha_publicacion'],
                'descripcion' => $row['descripcion'],
                'estado' => $row['estado']
            ];
        }
        return array_values($clases);
    }

    /**
     * Obtiene todos los libros (estado ACTIVO) asociados a las clases en las que el estudiante está matriculado o que ya cursó.
     *
     * Se obtiene el conjunto de secciones en las que el estudiante aparece en la tabla Matricula o en HistorialEstudiante,
     * se extraen los ID de clase correspondientes, y se unen con ClaseLibro y Libro para obtener los libros.
     *
     * @param int $estudiante_id ID del estudiante.
     * @return array Arreglo de clases, donde cada clase contiene sus libros asociados. Ejemplo:
     * [
     *    {
     *      "clase_id": 1,
     *      "clase_nombre": "Matemáticas I",
     *      "libros": [
     *         { "libro_id": 3, "titulo": "Álgebra", "editorial": "Editorial X", "libro_url": "/uploads/libros/...", ... },
     *         { "libro_id": 5, "titulo": "Cálculo", "editorial": "Editorial Y", "libro_url": "/uploads/libros/...", ... }
     *      ]
     *    },
     *    ...
     * ]
     * @throws Exception Si ocurre un error en la consulta.
     */
    public function obtenerLibrosPorEstudiante($estudiante_id) {
        // Obtener el estado_libro_id para 'ACTIVO'
        $stmt = $this->conn->prepare("SELECT estado_libro_id FROM EstadoLibro WHERE nombre = 'ACTIVO'");
        if (!$stmt) {
            throw new Exception("Error preparando la consulta de estado: " . $this->conn->error);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            throw new Exception("Estado 'ACTIVO' no encontrado en la base de datos.");
        }
        $row = $result->fetch_assoc();
        $estado_libro_id = $row['estado_libro_id'];
        $stmt->close();

        // La subconsulta obtiene todos los seccion_id en los que el estudiante está (matrícula o historial)
        $sql = "
            SELECT 
                c.clase_id,
                c.nombre AS clase_nombre,
                l.libro_id,
                l.titulo,
                l.editorial,
                l.libro_url,
                l.fecha_publicacion,
                l.descripcion,
                l.estado_libro_id
            FROM Clase c
            INNER JOIN Seccion s ON c.clase_id = s.clase_id
            INNER JOIN ClaseLibro cl ON c.clase_id = cl.clase_id
            INNER JOIN Libro l ON cl.libro_id = l.libro_id
            WHERE l.estado_libro_id = ?
            AND s.seccion_id IN (
                    SELECT seccion_id FROM (
                        SELECT seccion_id FROM Matricula WHERE estudiante_id = ?
                        UNION
                        SELECT seccion_id FROM HistorialEstudiante WHERE estudiante_id = ?
                    ) AS t
            )
            ORDER BY c.clase_id, l.libro_id
            LIMIT 100";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
        $stmt->bind_param("iii", $estado_libro_id, $estudiante_id, $estudiante_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // Agrupar los resultados por clase
        $clases = [];
        foreach ($data as $row) {
            $clase_id = $row['clase_id'];
            if (!isset($clases[$clase_id])) {
                $clases[$clase_id] = [
                    'clase_id'     => $clase_id,
                    'clase_nombre' => $row['clase_nombre'],
                    'libros'       => []
                ];
            }
            // Agregar libro a la clase, incluyendo el campo editorial
            $clases[$clase_id]['libros'][] = [
                'libro_id'          => $row['libro_id'],
                'titulo'            => $row['titulo'],
                'editorial'         => $row['editorial'],
                'libro_url'         => $row['libro_url'],
                'fecha_publicacion' => $row['fecha_publicacion'],
                'descripcion'       => $row['descripcion'],
                'estado_libro_id'   => $row['estado_libro_id']
            ];
        }
        
        // Convertir a arreglo indexado
        return array_values($clases);
    }


     /**
     * Elimina (desasocia) ciertos tags y autores de un libro.
     *
     * @param int $libro_id ID del libro.
     * @param array|null $tagsToRemove Array de tag IDs a eliminar (opcional).
     * @param array|null $autoresToRemove Array de autor IDs a eliminar (opcional).
     * @return bool True si la operación se realizó correctamente.
     * @throws Exception Si ocurre un error durante la operación.
     */
    public function eliminarAsociaciones($libro_id, $tagsToRemove = null, $autoresToRemove = null) {
        $this->conn->begin_transaction();
        try {
            // Si se envió un array de tag IDs, eliminar las asociaciones correspondientes
            if (!empty($tagsToRemove) && is_array($tagsToRemove)) {
                $stmt = $this->conn->prepare("DELETE FROM TagLibro WHERE libro_id = ? AND tag_id = ?");
                if (!$stmt) {
                    throw new Exception("Error preparando la eliminación de TagLibro: " . $this->conn->error);
                }
                foreach ($tagsToRemove as $tag_id) {
                    if (!is_numeric($tag_id)) {
                        throw new Exception("El tag_id '$tag_id' debe ser numérico.");
                    }
                    $tag_id = (int)$tag_id;
                    $stmt->bind_param("ii", $libro_id, $tag_id);
                    if (!$stmt->execute()) {
                        throw new Exception("Error eliminando asociación en TagLibro: " . $stmt->error);
                    }
                }
                $stmt->close();
            }
            
            // Si se envió un array de autor IDs, eliminar las asociaciones correspondientes
            if (!empty($autoresToRemove) && is_array($autoresToRemove)) {
                $stmt = $this->conn->prepare("DELETE FROM LibroAutor WHERE libro_id = ? AND autor_id = ?");
                if (!$stmt) {
                    throw new Exception("Error preparando la eliminación de LibroAutor: " . $this->conn->error);
                }
                foreach ($autoresToRemove as $autor_id) {
                    if (!is_numeric($autor_id)) {
                        throw new Exception("El autor_id '$autor_id' debe ser numérico.");
                    }
                    $autor_id = (int)$autor_id;
                    $stmt->bind_param("ii", $libro_id, $autor_id);
                    if (!$stmt->execute()) {
                        throw new Exception("Error eliminando asociación en LibroAutor: " . $stmt->error);
                    }
                }
                $stmt->close();
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

}
?>
