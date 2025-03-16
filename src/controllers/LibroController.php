<?php
/**
 * Controlador de Libro.
 *
 * Encapsula la lógica para registrar un libro.
 *
 * Sólo podrán ejecutar esta acción usuarios con rol:
 *   - Biblioteca_Jefe de Departamento
 *   - Biblioteca_Coordinador
 *
 * @package Controllers
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

require_once __DIR__ . '/../models/Libro.php';

class LibroController {
    /**
     * Registra un libro.
     *
     * Se esperan los siguientes datos vía POST (multipart/form-data):
     *   - titulo (string)
     *   - fecha_publicacion (YYYY-MM-DD)
     *   - descripcion (string)
     *   - tags (opcional, JSON string con array de tag IDs)
     *   - autores (opcional, JSON string con array de objetos {nombre, apellido})
     *   - clase_id (opcional, int)
     *   - estado (opcional, string: 'ACTIVO' o 'INACTIVO'; por defecto se usa 'ACTIVO')
     *   - libro (archivo a subir, **obligatoriamente PDF**)
     *
     * @param array $data Datos enviados vía POST.
     * @param array $files Datos de archivos enviados vía $_FILES.
     * @return void Envía la respuesta en formato JSON.
     */
    public function registrarLibro($data, $files) {
        // Validar campos obligatorios
        $camposObligatorios = ['titulo', 'fecha_publicacion', 'descripcion'];
        foreach ($camposObligatorios as $campo) {
            if (empty($data[$campo])) {
                http_response_code(400);
                echo json_encode(['error' => "Falta el campo obligatorio: $campo"]);
                return;
            }
        }
        
        // Expresiones regulares de validación
        $regexTitulo = '/^[\w\s\.\-áéíóúÁÉÍÓÚñÑ,!?]+$/u';
        $regexFecha  = '/^\d{4}-\d{2}-\d{2}$/';
        $regexDescripcion = '/^.{1,1000}$/s';
        $regexTag    = '/^\d+$/'; // Se esperan tag IDs numéricos
        $regexNombre = '/^[A-Za-z\sáéíóúÁÉÍÓÚñÑ]+$/u';
        
        // Validar y limpiar título
        $titulo = trim($data['titulo']);
        if (!preg_match($regexTitulo, $titulo)) {
            http_response_code(400);
            echo json_encode(['error' => 'El título tiene un formato no válido']);
            return;
        }
        
        // Validar fecha de publicación
        $fecha_publicacion = trim($data['fecha_publicacion']);
        if (!preg_match($regexFecha, $fecha_publicacion)) {
            http_response_code(400);
            echo json_encode(['error' => 'La fecha de publicación debe tener el formato YYYY-MM-DD']);
            return;
        }
        
        // Validar descripción
        $descripcion = trim($data['descripcion']);
        if (!preg_match($regexDescripcion, $descripcion)) {
            http_response_code(400);
            echo json_encode(['error' => 'La descripción debe contener entre 1 y 1000 caracteres']);
            return;
        }
        
        // Procesar tags (opcional)
        $tags = [];
        if (!empty($data['tags'])) {
            $tagsDecoded = json_decode($data['tags'], true);
            if (!is_array($tagsDecoded)) {
                http_response_code(400);
                echo json_encode(['error' => 'El campo tags debe ser un array JSON válido de identificadores']);
                return;
            }
            foreach ($tagsDecoded as $tagId) {
                if (!is_numeric($tagId)) {
                    http_response_code(400);
                    echo json_encode(['error' => "El tag_id '$tagId' debe ser numérico"]);
                    return;
                }
                $tags[] = (int)$tagId;
            }
        }
        
        // Procesar autores (opcional)
        $autores = [];
        if (!empty($data['autores'])) {
            $autoresDecoded = json_decode($data['autores'], true);
            if (!is_array($autoresDecoded)) {
                http_response_code(400);
                echo json_encode(['error' => 'El campo autores debe ser un array JSON válido']);
                return;
            }
            foreach ($autoresDecoded as $autor) {
                if (!isset($autor['nombre']) || !isset($autor['apellido'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Cada autor debe tener nombre y apellido']);
                    return;
                }
                $nombre = trim($autor['nombre']);
                $apellido = trim($autor['apellido']);
                if (!preg_match($regexNombre, $nombre) || !preg_match($regexNombre, $apellido)) {
                    http_response_code(400);
                    echo json_encode(['error' => "El autor $nombre $apellido tiene un formato no válido"]);
                    return;
                }
                $autores[] = ['nombre' => $nombre, 'apellido' => $apellido];
            }
        }
        
        // Procesar clase_id (opcional)
        $clase_id = 0;
        if (isset($data['clase_id']) && is_numeric($data['clase_id'])) {
            $clase_id = (int)$data['clase_id'];
        }
        
        // Obtener estado (opcional) - por defecto 'ACTIVO'
        $estado = 'ACTIVO';
        if (isset($data['estado']) && in_array($data['estado'], ['ACTIVO', 'INACTIVO'])) {
            $estado = $data['estado'];
        }
        
        // Validar archivo del libro
        if (!isset($files['libro']) || $files['libro']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['error' => 'Error al subir el archivo del libro']);
            return;
        }
        
        // Validar que el archivo sea PDF
        $alloweDocTypes = ['application/pdf'];
        if (!in_array($files['libro']['type'], $alloweDocTypes)) {
            http_response_code(400);
            echo json_encode(['error' => 'Solo se permiten archivos PDF']);
            return;
        }
        
        $nombreArchivo = basename($files['libro']['name']);
        $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            http_response_code(400);
            echo json_encode(['error' => 'El archivo debe tener extensión PDF']);
            return;
        }
        
        // Manejar el archivo: moverlo a la carpeta de destino
        $uploadDir = __DIR__ . '/../../uploads/libros/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        // Generar un nombre único para evitar conflictos
        $newFileName = uniqid('libro_', true) . '.' . $extension;
        $targetPath = $uploadDir . $newFileName;
        if (!move_uploaded_file($files['libro']['tmp_name'], $targetPath)) {
            http_response_code(500);
            echo json_encode(['error' => 'Error moviendo el archivo subido']);
            return;
        }
        // Guardar la ruta relativa o URL del archivo
        $libro_url = '/uploads/libros/' . $newFileName;
        
        // Llamar al modelo para registrar el libro, pasando también el estado
        try {
            $libroModel = new Libro();
            $libro_id = $libroModel->registrarLibro(
                $titulo,
                $libro_url,
                $fecha_publicacion,
                $descripcion,
                $estado,
                $tags,
                $autores,
                $clase_id
            );
            http_response_code(200);
            echo json_encode(['libro_id' => $libro_id, 'mensaje' => 'Libro registrado correctamente']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Actualiza un libro.
     *
     * Se esperan los siguientes parámetros vía POST (multipart/form-data):
     *   - libro_id: int (requerido)
     *   - titulo: string (opcional)
     *   - fecha_publicacion: string (YYYY-MM-DD, opcional)
     *   - descripcion: string (opcional)
     *   - tags: JSON string (opcional, array de tag IDs)
     *   - autores: JSON string (opcional, array de objetos {nombre, apellido})
     *   - clase_id: int (opcional)
     *   - estado: string (opcional, 'ACTIVO' o 'INACTIVO')
     *   - libro: archivo (opcional, para actualizar el archivo)
     *
     * @param array $data Datos enviados vía POST.
     * @param array $files Datos de archivos enviados vía $_FILES.
     * @return void Envía la respuesta en formato JSON.
     */
    public function actualizarLibro($data, $files) {
        // Validar libro_id
        if (empty($data['libro_id']) || !is_numeric($data['libro_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Falta el campo obligatorio: libro_id']);
            return;
        }
        $libro_id = (int)$data['libro_id'];
        
        // Expresiones regulares
        $regexTitulo = '/^[\w\s\.\-áéíóúÁÉÍÓÚñÑ,!?]+$/u';
        $regexFecha  = '/^\d{4}-\d{2}-\d{2}$/';
        $regexTexto  = '/^.{0,1000}$/s'; // Permite hasta 1000 caracteres, opcional
        $regexTag    = '/^\d+$/'; // Cada tag se espera sea numérico
        $regexNombre = '/^[A-Za-z\sáéíóúÁÉÍÓÚñÑ]+$/u';
        
        // Variables opcionales para actualizar (si no se envían, se dejan null)
        $titulo = isset($data['titulo']) ? trim($data['titulo']) : null;
        if ($titulo !== null && $titulo !== "" && !preg_match($regexTitulo, $titulo)) {
            http_response_code(400);
            echo json_encode(['error' => 'El título tiene un formato no válido']);
            return;
        }
        
        $fecha_publicacion = isset($data['fecha_publicacion']) ? trim($data['fecha_publicacion']) : null;
        if ($fecha_publicacion !== null && $fecha_publicacion !== "" && !preg_match($regexFecha, $fecha_publicacion)) {
            http_response_code(400);
            echo json_encode(['error' => 'La fecha de publicación debe tener el formato YYYY-MM-DD']);
            return;
        }
        
        $descripcion = isset($data['descripcion']) ? trim($data['descripcion']) : null;
        if ($descripcion !== null && $descripcion !== "" && !preg_match($regexTexto, $descripcion)) {
            http_response_code(400);
            echo json_encode(['error' => 'La descripción debe tener máximo 1000 caracteres']);
            return;
        }
        
        // Procesar tags (opcional) - se espera un array JSON de números (tag IDs)
        $tags = null;
        if (!empty($data['tags'])) {
            $tagsDecoded = json_decode($data['tags'], true);
            if (!is_array($tagsDecoded)) {
                http_response_code(400);
                echo json_encode(['error' => 'El campo tags debe ser un array JSON válido de identificadores']);
                return;
            }
            $tags = [];
            foreach ($tagsDecoded as $tagId) {
                if (!preg_match($regexTag, $tagId)) {
                    http_response_code(400);
                    echo json_encode(['error' => "El tag_id '$tagId' debe ser numérico"]);
                    return;
                }
                $tags[] = (int)$tagId;
            }
        }
        
        // Procesar autores (opcional)
        $autores = null;
        if (!empty($data['autores'])) {
            $autoresDecoded = json_decode($data['autores'], true);
            if (!is_array($autoresDecoded)) {
                http_response_code(400);
                echo json_encode(['error' => 'El campo autores debe ser un array JSON válido']);
                return;
            }
            $autores = [];
            foreach ($autoresDecoded as $autor) {
                if (!isset($autor['nombre']) || !isset($autor['apellido'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Cada autor debe tener nombre y apellido']);
                    return;
                }
                $nombre = trim($autor['nombre']);
                $apellido = trim($autor['apellido']);
                if (!preg_match($regexNombre, $nombre) || !preg_match($regexNombre, $apellido)) {
                    http_response_code(400);
                    echo json_encode(['error' => "El autor $nombre $apellido tiene un formato no válido"]);
                    return;
                }
                $autores[] = ['nombre' => $nombre, 'apellido' => $apellido];
            }
        }
        
        // Procesar clase_id (opcional)
        $clase_id = null;
        if (isset($data['clase_id']) && is_numeric($data['clase_id'])) {
            $clase_id = (int)$data['clase_id'];
        }
        
        // Procesar estado (opcional)
        $estado = null;
        if (isset($data['estado']) && in_array($data['estado'], ['ACTIVO', 'INACTIVO'])) {
            $estado = $data['estado'];
        }
        
        // Procesar el archivo del libro (opcional)
        $libro_url = null;
        if (isset($_FILES['libro']) && $_FILES['libro']['error'] === UPLOAD_ERR_OK) {
        // Definir tipos MIME permitidos para PDF
        $allowedDocTypes = ['application/pdf'];
        if (!in_array($_FILES['libro']['type'], $allowedDocTypes)) {
        http_response_code(400);
        echo json_encode(['error' => 'Solo se permiten archivos PDF']);
        exit;
    }
    
        // Definir la carpeta de destino para los archivos PDF
        $uploadDir = __DIR__ . '/../../uploads/libros/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generar un nombre único para el archivo
        $fileExt = pathinfo($_FILES['libro']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('libro_', true) . '.' . strtolower($fileExt);
        $targetPath = $uploadDir . $fileName;
        
        // Mover el archivo subido a la carpeta de destino
        if (!move_uploaded_file($_FILES['libro']['tmp_name'], $targetPath)) {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo guardar el archivo del libro']);
            exit;
        }
        
        // Guardar la ruta relativa o URL del archivo
        $libro_url = '/uploads/libros/' . $fileName;
    }

        
        // Llamar al modelo para actualizar el libro
        try {
            $libroModel = new Libro();
            $libroModel->actualizarLibro($libro_id, $titulo, $libro_url, $fecha_publicacion, $descripcion, $tags, $autores, $clase_id, $estado);
            http_response_code(200);
            echo json_encode(['mensaje' => 'Libro actualizado correctamente']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Obtiene los detalles de un libro que estan activos para Estudiantes.
     *
     * @param int $libro_id ID del libro a obtener.
     * @return void Envía la respuesta en formato JSON.
     */
    public function obtenerLibro($libro_id) {
        try {
            $libroModel = new Libro();
            $libro = $libroModel->obtenerLibro($libro_id);
            http_response_code(200);
            echo json_encode($libro);
        } catch (Exception $e) {
            http_response_code(404);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Obtiene los detalles completos de un libro, sin filtrar por estado para Encargado Biblioteca.
     *
     * @param int $libro_id ID del libro a obtener.
     * @return void Envía la respuesta en formato JSON.
     */
    public function obtenerLibroCompleto($libro_id) {
        try {
            $libroModel = new Libro();
            $libro = $libroModel->obtenerLibroCompleto($libro_id);
            http_response_code(200);
            echo json_encode($libro);
        } catch (Exception $e) {
            http_response_code(404);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Obtiene las clases y los libros asociados para un departamento.
     *
     * @param int $departamentoId ID del departamento.
     * @return void Envía la respuesta en formato JSON.
     */
    public function obtenerLibrosPorDepartamento($departamentoId) {
        try {
            $libroModel = new Libro();
            $resultado = $libroModel->obtenerLibrosPorDepartamento($departamentoId);
            http_response_code(200);
            echo json_encode($resultado);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Obtiene los libros asociados a las clases en las que el estudiante está (o estuvo).
     *
     * @param int $estudiante_id ID del estudiante.
     * @return void Envía la respuesta en formato JSON.
     */
    public function obtenerLibrosPorEstudiante($estudiante_id) {
        try {
            $libroModel = new Libro();
            $resultado = $libroModel->obtenerLibrosPorEstudiante($estudiante_id);
            http_response_code(200);
            echo json_encode($resultado);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Elimina (desasocia) tags y autores de un libro.
     *
     * Se espera recibir vía POST:
     *   - libro_id: int (requerido)
     *   - tags: JSON string (opcional, array de tag IDs a eliminar)
     *   - autores: JSON string (opcional, array de autor IDs a eliminar)
     *
     * @param array $data Datos enviados vía POST.
     * @return void Envía la respuesta en formato JSON.
     */
    public function eliminarAsociacionesLibro($data) {
        if (empty($data['libro_id']) || !is_numeric($data['libro_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Falta el parámetro libro_id o no es numérico']);
            return;
        }
        $libro_id = (int)$data['libro_id'];
        
        // Procesar tags (opcional)
        $tags = null;
        if (!empty($data['tags'])) {
            $tagsDecoded = json_decode($data['tags'], true);
            if (!is_array($tagsDecoded)) {
                http_response_code(400);
                echo json_encode(['error' => 'El campo tags debe ser un array JSON válido']);
                return;
            }
            $tags = [];
            foreach ($tagsDecoded as $tagId) {
                if (!is_numeric($tagId)) {
                    http_response_code(400);
                    echo json_encode(['error' => "El tag_id '$tagId' debe ser numérico"]);
                    return;
                }
                $tags[] = (int)$tagId;
            }
        }
        
        // Procesar autores (opcional)
        $autores = null;
        if (!empty($data['autores'])) {
            $autoresDecoded = json_decode($data['autores'], true);
            if (!is_array($autoresDecoded)) {
                http_response_code(400);
                echo json_encode(['error' => 'El campo autores debe ser un array JSON válido']);
                return;
            }
            $autores = [];
            foreach ($autoresDecoded as $autorId) {
                if (!is_numeric($autorId)) {
                    http_response_code(400);
                    echo json_encode(['error' => "El autor_id '$autorId' debe ser numérico"]);
                    return;
                }
                $autores[] = (int)$autorId;
            }
        }
        
        try {
            $libroModel = new Libro();
            $libroModel->eliminarAsociaciones($libro_id, $tags, $autores);
            http_response_code(200);
            echo json_encode(['mensaje' => 'Asociaciones eliminadas correctamente']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
?>
