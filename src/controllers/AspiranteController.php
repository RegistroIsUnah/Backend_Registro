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
     * Genera un CSV con los aspirantes admitidos y fuerza la descarga.
     */
    public function generarCSVAspirantesAdmitidos() {
        // Validar que el método de solicitud sea GET
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405); // Método no permitido
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }

        // Validar permisos (opcional, dependiendo de tu sistema de autenticación)
        if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
            http_response_code(403); // Prohibido
            echo json_encode(['error' => 'No tienes permisos para realizar esta acción']);
            exit;
        }

        // Intentar generar el CSV
        try {
            // Configurar headers para descarga
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="aspirantes_admitidos.csv"');

            // Verificar que el método exista en el modelo
            if (!method_exists($this->modelo, 'exportarAspirantesAdmitidosCSV')) {
                throw new Exception("El método exportarAspirantesAdmitidosCSV no existe en el modelo.");
            }

            // Llamar al método del modelo para generar el CSV
            $this->modelo->exportarAspirantesAdmitidosCSV();

            exit; // Terminar ejecución después de enviar el archivo

        } catch (Exception $e) {
            // Registrar el error en el log
            error_log("Error generando CSV: " . $e->getMessage());

            // Devolver error en formato JSON
            http_response_code(500);
            echo json_encode(['error' => 'Error al generar el archivo CSV: ' . $e->getMessage()]);
            exit;
        }
    }
  
    /**
     * Obtiene una solicitud pendiente o corregida y la asigna al revisor que realiza la petición.
     *
     * Se espera recibir el ID del revisor y se retorna la solicitud asignada.
     *
     * @param int $revisor_id ID del revisor.
     * @return void Envía la respuesta en formato JSON.
     */
    public function obtenerSolicitudParaRevision($revisor_id) {
        try {
            $solicitudModel = new Aspirante();
            $solicitud = $solicitudModel->obtenerYAsignarSolicitud($revisor_id);
            
            if ($solicitud === null) {
                http_response_code(200);
                echo json_encode(['mensaje' => 'No hay solicitudes pendientes']);
            } else {
                // Asignamos el revisor a la solicitud si no tiene un revisor asignado
                $asignado = $solicitudModel->asignarRevisor($solicitud['aspirante_id'], $revisor_id);
                if ($asignado) {
                    http_response_code(200);
                    echo json_encode(['mensaje' => 'Solicitud asignada con éxito', 'solicitud' => $solicitud]);
                } else {
                    http_response_code(200);
                    echo json_encode(['mensaje' => 'La solicitud ya fue revisada o está fuera de tiempo']);
                }
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
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