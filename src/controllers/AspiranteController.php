<?php
/**
 * Controlador de Aspirante
 *
 * Valida los datos recibidos, procesa la carga de archivos (foto, fotodni y certificado)
 * y llama al modelo para insertar el aspirante.
 *
 * @package Controllers
 * @author Ruben Diaz
 * @version 1.1
 * 
 */

require_once __DIR__ . '/../models/Aspirante.php';

class AspiranteController {
    private $modelo; // Propiedad para almacenar el modelo

    /**
     * Constructor del controlador.
     */
    public function __construct() {
        // Inicializar el modelo Aspirante
        $this->modelo = new Aspirante();
    }

    /**
     * Inserta un aspirante.
     *
     * Valida los campos de texto y procesa los archivos (foto, foto del DNI y certificado) para posteriormente
     * llamar al modelo que ejecuta el procedimiento almacenado SP_insertarAspirante.
     *
     * @param array $data Datos de texto del aspirante (de $_POST). Se espera:
     *      - nombre
     *      - apellido
     *      - documento
     *      - telefono
     *      - correo
     *      - carrera_principal_id
     *      - carrera_secundaria_id (opcional)
     *      - centro_id
     *      - tipo_documento_id
     * @return void Envía la respuesta en formato JSON.
     */
    public function insertarAspirante($data) {
        // Validaciones de campos de texto
        if (!isset($data['nombre']) || !preg_match('/^[a-zA-ZÀ-ÿ\s]+$/', $data['nombre'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre inválido']);
            exit;
        }
        if (!isset($data['apellido']) || !preg_match('/^[a-zA-ZÀ-ÿ\s]+$/', $data['apellido'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Apellido inválido']);
            exit;
        }
        if (!isset($data['documento']) || !preg_match('/^[0-9\-]+$/', $data['documento'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Documento inválida']);
            exit;
        }
    
        if (!isset($data['telefono']) || !preg_match('/^[0-9\+\-\s]+$/', $data['telefono'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Teléfono inválido']);
            exit;
        }
    
        if (!isset($data['correo']) || !filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Correo inválido']);
            exit;
        }
    
        if (!isset($data['carrera_principal_id']) || !is_numeric($data['carrera_principal_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Carrera principal inválida']);
            exit;
        }
        $carrera_principal_id = intval($data['carrera_principal_id']);
        $carrera_secundaria_id = (isset($data['carrera_secundaria_id']) && is_numeric($data['carrera_secundaria_id']))
            ? intval($data['carrera_secundaria_id'])
            : null;
    
        if (!isset($data['centro_id']) || !is_numeric($data['centro_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Centro inválido']);
            exit;
        }
        $centro_id = intval($data['centro_id']);
    
        // Validar el tipo de documento
        if (!isset($data['tipo_documento_id']) || !is_numeric($data['tipo_documento_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Tipo de documento inválido']);
            exit;
        }
        $tipo_documento_id = intval($data['tipo_documento_id']);
        
        // Procesar la foto del aspirante.
        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['error' => 'Error al subir la foto del aspirante']);
            exit;
        }
        $allowedImageTypes = ['image/jpeg', 'image/jpg', 'image/tiff', 'image/avif', 'image/png', 'image/webp'];
        if (!in_array($_FILES['foto']['type'], $allowedImageTypes)) {
            http_response_code(400);
            echo json_encode(['error' => 'Tipo de imagen de aspirante no permitido']);
            exit;
        }
        $uploadsDirFotos = __DIR__ . '/../../uploads/fotos/';
        if (!is_dir($uploadsDirFotos)) {
            mkdir($uploadsDirFotos, 0755, true);
        }
        $fileExtFoto = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $fileNameFoto = uniqid('foto_', true) . '.' . $fileExtFoto;
        $filePathFoto = $uploadsDirFotos . $fileNameFoto;
        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $filePathFoto)) {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo guardar la foto del aspirante']);
            exit;
        }
        $fotoRuta = 'uploads/fotos/' . $fileNameFoto;
        
        // Procesar la foto del DNI (fotodni).
        if (!isset($_FILES['fotodni']) || $_FILES['fotodni']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['error' => 'Error al subir la foto del DNI']);
            exit;
        }
        $allowedImageTypesDni = ['image/jpeg', 'image/jpg', 'image/tiff', 'image/avif', 'image/png', 'image/webp'];
        if (!in_array($_FILES['fotodni']['type'], $allowedImageTypesDni)) {
            http_response_code(400);
            echo json_encode(['error' => 'Tipo de imagen para el DNI no permitido']);
            exit;
        }
        $uploadsDirFotodni = __DIR__ . '/../../uploads/fotodni/';
        if (!is_dir($uploadsDirFotodni)) {
            mkdir($uploadsDirFotodni, 0755, true);
        }
        $fileExtFotodni = pathinfo($_FILES['fotodni']['name'], PATHINFO_EXTENSION);
        $fileNameFotodni = uniqid('fotodni_', true) . '.' . $fileExtFotodni;
        $filePathFotodni = $uploadsDirFotodni . $fileNameFotodni;
        if (!move_uploaded_file($_FILES['fotodni']['tmp_name'], $filePathFotodni)) {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo guardar la foto del DNI']);
            exit;
        }
        $fotodniRuta = 'uploads/fotodni/' . $fileNameFotodni;
        
        // Procesar el certificado.
        if (!isset($_FILES['certificado']) || $_FILES['certificado']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['error' => 'Error al subir el certificado']);
            exit;
        }
        $allowedCertTypes = ['image/jpeg', 'image/jpg', 'image/tiff', 'image/avif', 'image/png', 'image/webp', 'application/pdf'];
        if (!in_array($_FILES['certificado']['type'], $allowedCertTypes)) {
            http_response_code(400);
            echo json_encode(['error' => 'Tipo de archivo de certificado no permitido']);
            exit;
        }
        $uploadsDirCertificados = __DIR__ . '/../../uploads/certificados/';
        if (!is_dir($uploadsDirCertificados)) {
            mkdir($uploadsDirCertificados, 0755, true);
        }
        $fileExtCert = pathinfo($_FILES['certificado']['name'], PATHINFO_EXTENSION);
        $fileNameCert = uniqid('cert_', true) . '.' . $fileExtCert;
        $filePathCert = $uploadsDirCertificados . $fileNameCert;
        if (!move_uploaded_file($_FILES['certificado']['tmp_name'], $filePathCert)) {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo guardar el certificado']);
            exit;
        }
        $certificadoRuta = 'uploads/certificados/' . $fileNameCert;
        
        // Llamar al modelo para insertar el aspirante y enviar correo
        try {
            $aspiranteModel = new Aspirante();
            $numSolicitud = $aspiranteModel->insertarAspirante(
                $data['nombre'],
                $data['apellido'],
                $data['documento'],
                $data['telefono'],
                $data['correo'],
                $fotoRuta,
                $fotodniRuta,
                $carrera_principal_id,
                $carrera_secundaria_id,
                $centro_id,
                $certificadoRuta,
                $tipo_documento_id  // Se pasa el tipo_documento_id al modelo
            );
            
            // Enviar respuesta de éxito
            http_response_code(200);
            echo json_encode(['numSolicitud' => $numSolicitud, 'message' => 'Aspirante ingresado exitosamente']);
        } catch (Exception $e) {
            // Manejo de errores
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }    

    /**
     * Genera un CSV con los aspirantes admitidos y guarda el archivo en una carpeta específica.
     */
    public function generarCSVAspirantesAdmitidos() {
        // Validar que el método de solicitud sea GET
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405); // Método no permitido
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }

        // Intentar generar el CSV
        try {
            // Definir la ruta de la carpeta donde se guardará el CSV
            $uploadsDir = __DIR__ . '/../../uploads/estudiantesaprobados/';

            // Crear la carpeta si no existe
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0755, true); // Permite la creación de carpetas de forma recursiva
            }

            // Definir la ruta del archivo CSV
            $fileName = 'aspirantes_admitidos_' . uniqid() . '.csv'; // Nombre único para el archivo
            $filePath = $uploadsDir . $fileName;
            $file = fopen($filePath, 'w'); // Abrir el archivo para escribir

            // Verificar que el método exista en el modelo
            if (!method_exists($this->modelo, 'exportarAspirantesAdmitidosCSV')) {
                throw new Exception("El método exportarAspirantesAdmitidosCSV no existe en el modelo.");
            }

            // Llamar al método del modelo para generar el CSV y escribir en el archivo
            $this->modelo->exportarAspirantesAdmitidosCSV($file);

            fclose($file); // Cerrar el archivo

            // Responder con éxito y la ruta del archivo generado
            echo json_encode([
                'success' => true,
                'message' => 'Archivo CSV generado exitosamente.',
                'file' => '/uploads/estudiantesaprobados/' . $fileName // Ruta accesible para descargar
            ]);
            exit; // Terminar ejecución

        } catch (Exception $e) {
            // Registrar el error en el log
            error_log("Error generando CSV: " . $e->getMessage());

            // Devolver error en formato JSON
            http_response_code(500);
            echo json_encode(['error' => 'Error al generar el archivo CSV: ' . $e->getMessage()]);
            exit;
        }
    }


    /*

    @author Jose Vargas
    @Version 1.0
    */
    public function evaluarAspirante() {
        header('Content-Type: application/json');
        
        try {
            // Validar entrada
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['aspirante_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID de aspirante requerido']);
                return;
            }
    
            // Procesar evaluación
            $resultado = $this->modelo->evaluarAspirante($input['aspirante_id']);
            
            echo json_encode([
                'success' => true,
                'resultado' => $resultado
            ]);
    
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
 

    /**
     * Procesa la revisión de una solicitud de aspirante.
     *
     * Se espera recibir vía POST:
     *   - aspirante_id: int (requerido)
     *   - revisor_id: int (requerido)
     *   - accion: string ('aceptar' o 'rechazar') (requerido)
     *   - motivos: JSON string (opcional, array de motivo_id; requerido si la acción es 'rechazar')
     *
     * @param array $data Datos enviados vía POST.
     * @return void Envía la respuesta en formato JSON.
     */
    public function procesarRevision($data) {
        // Validar parámetros obligatorios
        if (empty($data['aspirante_id']) || !is_numeric($data['aspirante_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Parámetro aspirante_id inválido o faltante']);
            return;
        }
        if (empty($data['revisor_id']) || !is_numeric($data['revisor_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Parámetro revisor_id inválido o faltante']);
            return;
        }
        if (empty($data['accion'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Parámetro accion es requerido']);
            return;
        }

        $aspirante_id = (int)$data['aspirante_id'];
        $revisor_id   = (int)$data['revisor_id'];
        $accion      = strtolower(trim($data['accion']));

        $motivos = null;
        if ($accion === 'rechazar') {
            if (empty($data['motivos'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Al rechazar, se deben enviar los motivos']);
                return;
            }
            $motivosDecoded = json_decode($data['motivos'], true);
            if (!is_array($motivosDecoded)) {
                http_response_code(400);
                echo json_encode(['error' => 'El parámetro motivos debe ser un array JSON válido']);
                return;
            }
            $motivos = [];
            foreach ($motivosDecoded as $motivo_id) {
                if (!is_numeric($motivo_id)) {
                    http_response_code(400);
                    echo json_encode(['error' => "El motivo_id '$motivo_id' debe ser numérico"]);
                    return;
                }
                $motivos[] = (int)$motivo_id;
            }
        }

        try {
            $revisionModel = new Aspirante();
            $resultado = $revisionModel->procesarRevision($aspirante_id, $revisor_id, $accion, $motivos);

            // Aquí la respuesta será más clara ahora que se está utilizando estado_aspirante_id
            http_response_code(200);
            echo json_encode($resultado);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

     /**
     * Obtiene los detalles de un aspirante por su documento.
     *
     * Se espera recibir en $data el siguiente parámetro:
     * - documento: Documento del aspirante.
     *
     * @param array $data Datos recibidos del endpoint.
     * @return void
     */
    public function obtenerAspirantePorDocumento($data) {
        if (!isset($data['documento']) || empty($data['documento'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El parámetro documento es requerido']);
            exit;
        }
        
        $documento = trim($data['documento']);
        
        try {
            $aspiranteModel = new Aspirante();
            $aspirante = $aspiranteModel->obtenerAspirantePorDocumento($documento);

            if ($aspirante === null) {
                http_response_code(404);
                echo json_encode(['error' => 'Aspirante no encontrado']);
                exit;
            }

            http_response_code(200);
            echo json_encode($aspirante);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

   /**
     * Obtiene los detalles de un aspirante por su número de solicitud.
     *
     * Se espera recibir en $data el siguiente parámetro:
     * - numSolicitud: Número de solicitud del aspirante.
     *
     * @param array $data Datos recibidos del endpoint.
     * @return void
     */
    public function obtenerAspirantePorSolicitud($data) {
        if (!isset($data['numSolicitud']) || empty($data['numSolicitud'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El parámetro numSolicitud es requerido']);
            exit;
        }

        $numSolicitud = trim($data['numSolicitud']);
        
        try {
            $aspiranteModel = new Aspirante();
            $aspirante = $aspiranteModel->obtenerAspirantePorSolicitud($numSolicitud);

            if ($aspirante === null) {
                http_response_code(404);
                echo json_encode(['error' => 'Aspirante no encontrado']);
                exit;
            }

            http_response_code(200);
            echo json_encode($aspirante);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Actualiza los detalles de un aspirante por su número de solicitud.
     *
     * Se espera recibir en $data el siguiente parámetro:
     * - numSolicitud: Número de solicitud del aspirante.
     * - datos (aspirante_nombre, aspirante_apellido, etc.): Datos a actualizar.
     * - foto, fotodni, certificado_url: Archivos a actualizar.
     *
     * @param array $data Datos recibidos del endpoint (POST y FILES).
     * @return void
     */
    public function actualizarAspirante($data) {
        // Verificar que el parámetro numSolicitud esté presente
        if (!isset($data['numSolicitud']) || empty($data['numSolicitud'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El parámetro numSolicitud es requerido']);
            exit;
        }
    
        // Extraemos el numSolicitud y lo removemos de $data
        $numSolicitud = trim($data['numSolicitud']);
        unset($data['numSolicitud']);
    
        // Procesamos los archivos, subiéndolos y guardando la ruta
        if (isset($data['foto'])) {
            $data['foto'] = $this->subirArchivo($data['foto'], 'fotos');
        }
    
        if (isset($data['fotodni'])) {
            $data['fotodni'] = $this->subirArchivo($data['fotodni'], 'fotodni');
        }
    
        if (isset($data['certificado_url'])) {
            $data['certificado_url'] = $this->subirArchivo($data['certificado_url'], 'certificados');
        }
    
        try {
            // Llamamos al modelo para actualizar el aspirante
            $aspiranteModel = new Aspirante();
            $updated = $aspiranteModel->actualizarAspirantePorSolicitud($numSolicitud, $data);
    
            if (!$updated) {
                http_response_code(404);
                echo json_encode(['error' => 'No se encontró el aspirante o no se realizaron cambios']);
                exit;
            }
    
            http_response_code(200);
            echo json_encode(['message' => 'Aspirante actualizado exitosamente']);
    
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Función para subir archivos a una carpeta específica y devolver la ruta del archivo.
     *
     * @param array $file El archivo que se está subiendo.
     * @param string $folder La carpeta donde se guardará el archivo.
     * @return string Ruta del archivo subido.
     * @throws Exception Si ocurre un error al subir el archivo.
     */
    private function subirArchivo($file, $folder) {
        $uploadsDir = __DIR__ . "/../../uploads/{$folder}/";
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }
        $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $fileExt;
        $filePath = $uploadsDir . $fileName;
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Error al subir el archivo');
        }
        return 'uploads/' . $folder . '/' . $fileName;
    }

     /**
     * Procesa el archivo CSV con resultados de exámenes
     * @param string $filePath Ruta temporal al archivo CSV
     * @return array Resultados del procesamiento
     */
    public function procesarCSV($filePath) {
        // Validar archivo
        $this->validarArchivoCSV($filePath);

        // Leer y agrupar datos
        $datos = $this->leerCSV($filePath);
        $agrupado = $this->agruparPorAspirante($datos);

        // Procesar cada aspirante
        $resultados = [];
        foreach ($agrupado as $documento => $examenes) {
            $resultados[$documento] = $this->procesarAspirante($documento, $examenes);
        }

        return $resultados;
    }

    /**
     * Valida el archivo CSV
     */
    private function validarArchivoCSV($filePath) {
        // Verificación básica de existencia
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Exception("No se puede leer el archivo", 400);
        }
    
        try {
            $handle = fopen($filePath, "r");
            if (!$handle) {
                throw new Exception("Error al abrir el archivo", 400);
            }
    
            // Validar encabezados (ahora con 4 columnas)
            $firstLine = fgetcsv($handle, 1000, ",", '"');
            if (count($firstLine) < 4 || 
                strtolower(trim($firstLine[0])) !== 'documento' ||
                strtolower(trim($firstLine[1])) !== 'tipo de examen' || 
                strtolower(trim($firstLine[2])) !== 'carrera' ||
                strtolower(trim($firstLine[3])) !== 'nota') {
                throw new Exception("Formato de CSV inválido. Encabezados requeridos: Documento, Tipo de Examen, Carrera, Nota", 400);
            }
    
            fclose($handle);
            return true;
    
        } catch (Exception $e) {
            if (isset($handle) && $handle) fclose($handle);
            throw new Exception("Error validando CSV: " . $e->getMessage(), 400);
        }
    }

    /**
     * Lee el archivo CSV
     */
    private function leerCSV($filePath) {
        $datos = [];
        $linea = 0;
    
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $linea++;
                
                // Saltar encabezados o líneas vacías
                if ($linea === 1 || count($row) < 4) continue;
    
                // Validar formato de línea
                if (empty($row[0]) || empty($row[1]) || empty($row[2]) || !is_numeric($row[3])) {
                    continue; // O podrías lanzar una excepción
                }
    
                $datos[] = [
                    'documento' => trim($row[0]),
                    'tipo_examen' => trim($row[1]),
                    'carrera' => trim($row[2]), // Nueva columna
                    'nota' => (float)$row[3]
                ];
            }
            fclose($handle);
        }
    
        if (empty($datos)) {
            throw new Exception("El CSV no contiene datos válidos", 400);
        }
    
        return $datos;
    }

    /**
     * Agrupa exámenes por documento de aspirante
     */
    private function agruparPorAspirante($datos) {
        $agrupado = [];
        foreach ($datos as $fila) {
            $documento = $fila['documento'];
            if (!isset($agrupado[$documento])) {
                $agrupado[$documento] = [];
            }
            $agrupado[$documento][] = [
                'tipo_examen' => $fila['tipo_examen'],
                'carrera' => $fila['carrera'], // Nueva columna
                'nota' => $fila['nota']
            ];
        }
        return $agrupado;
    }

    private function procesarAspirante($documento, $examenes) {
        try {
            $aspirante = $this->modelo->obtenerAspiranteResultado($documento);
            
            if (!$aspirante) {
                throw new Exception("Aspirante no encontrado");
            }
    
            $resultadosExamenes = [];
            foreach ($examenes as $examen) {
                $tipoExamen = $this->modelo->obtenerTipoExamenId($examen['tipo_examen']);
                $carrera = $this->modelo->obtenerCarreraPorNombre($examen['carrera']);
    
                if (!$tipoExamen || !$carrera) {
                    throw new Exception("Examen o carrera no válidos");
                }
    
                $this->modelo->registrarResultadoExamen(
                    $aspirante['aspirante_id'],
                    $tipoExamen['tipo_examen_id'],
                    $carrera['carrera_id'],
                    $examen['nota']
                );
    
                $resultadosExamenes[] = [
                    'tipo_examen' => $examen['tipo_examen'],
                    'tipo_examen_id' => $tipoExamen['tipo_examen_id'],
                    'carrera' => $examen['carrera'],
                    'carrera_id' => $carrera['carrera_id'],
                    'nota' => $examen['nota'],
                    'nota_minima' => $tipoExamen['nota_minima'],
                    'aprobado' => $examen['nota'] >= $tipoExamen['nota_minima']
                ];
            }
            
            // Verificar aprobación con los datos frescos (sin forzar estado false primero)
            $aprobado_principal = $this->modelo->verificarAprobacionCarrera(
                $aspirante['aspirante_id'], 
                $aspirante['carrera_principal_id']
            );
            
            $aprobado_secundaria = $aspirante['carrera_secundaria_id'] ? 
                $this->modelo->verificarAprobacionCarrera(
                    $aspirante['aspirante_id'], 
                    $aspirante['carrera_secundaria_id']
                ) : false;
    
            // Actualizar estados SOLO después de verificar
            $this->modelo->actualizarEstadoCarrera(
                $aspirante['aspirante_id'],
                $aspirante['carrera_principal_id'],
                $aprobado_principal
            );
            
            if ($aspirante['carrera_secundaria_id']) {
                $this->modelo->actualizarEstadoCarrera(
                    $aspirante['aspirante_id'],
                    $aspirante['carrera_secundaria_id'],
                    $aprobado_secundaria
                );
            }
    
            $this->modelo->enviarCorreoResultados($aspirante, $resultadosExamenes, $aprobado_principal, $aprobado_secundaria);
    
            return [
                'success' => true,
                'aspirante' => $aspirante['nombre'] . ' ' . $aspirante['apellido'],
                'examenes' => $resultadosExamenes,
                'aprobado_principal' => $aprobado_principal,
                'aprobado_secundaria' => $aprobado_secundaria
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'documento' => $documento
            ];
        }
    }

    /**
     * Obtiene una solicitud pendiente o corregida para revisión y asigna al revisor.
     *
     * @param int $revisor_id ID del revisor que se le asignará la solicitud.
     * @return void Responde con el resultado en formato JSON.
     */
    public function obtenerSolicitudParaRevision($revisor_id) {
        try {
            // Llamar al modelo para obtener y asignar la solicitud
            $solicitud = $this->modelo->obtenerYAsignarSolicitud($revisor_id);

            // Si hay una solicitud disponible, la asignamos
            if ($solicitud) {
                // Asignar revisor a la solicitud
                $asignada = $this->modelo->asignarRevisor($solicitud['aspirante_id'], $revisor_id);

                // Devolver respuesta de éxito
                if ($asignada) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Solicitud asignada correctamente.',
                        'solicitud' => $solicitud
                    ]);
                    return;
                } else {
                    // Si no se pudo asignar la solicitud
                    http_response_code(400);
                    echo json_encode(['error' => 'No se pudo asignar la solicitud']);
                    return;
                }
            } else {
                // Si no hay solicitudes pendientes
                http_response_code(404);
                echo json_encode(['message' => 'No hay solicitudes pendientes para asignar']);
                return;
            }
        } catch (Exception $e) {
            // Si ocurre un error en la consulta o en la asignación
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener la solicitud: ' . $e->getMessage()]);
        }
    }

        /**
     * Acción para reenviar el correo usando el email del aspirante.
     * 
     * @return void Envía respuesta JSON.
     */
    public function reenviarCorreoAction() {
        // Obtener datos del cuerpo (soporta JSON y form-data)
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        
        // Validar entrada
        if (empty($data['correo'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Campo "correo" es requerido']);
            return;
        }

        $correo = trim($data['correo']);

        // Validar formato del correo
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Formato de correo electrónico inválido']);
            return;
        }

        try {
            // Procesar
            $this->modelo->reenviarCorreoPorEmail($correo);
            
            // Respuesta exitosa
            echo json_encode([
                'success' => true,
                'message' => 'Correo reenviado exitosamente',
                'correo' => $correo
            ]);

        } catch (Exception $e) {
            // Manejo de errores
            $statusCode = (strpos($e->getMessage(), 'No se encontró') !== false) ? 404 : 500;
            http_response_code($statusCode);
            echo json_encode([
                'error' => $e->getMessage(),
                'correo' => $correo
            ]);
        }    
    }
}
?>