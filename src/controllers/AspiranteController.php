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
     * @param array $data Datos de texto del aspirante (de $_POST).
     * @return void
     */
    public function insertarAspirante($data) {
        // Validaciones de campos de texto.
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
        if (!isset($data['identidad']) || !preg_match('/^[0-9\-]+$/', $data['identidad'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Identidad inválida']);
            exit;
        }

        /*
        if (!isset($data['identidad']) || !preg_match('/^((01(0[1-8]))|(02(0[1-9]|10))|(03(0[1-9]|1[0-9]|2[01]))|
        (04(0[1-9]|1[0-9]|2[0-3]))|(05(0[1-9]|1[0-2]))|(06(0[1-9]|1[0-6]))|(07(0[1-9]|1[0-9]))|
        (08(0[1-9]|1[0-9]|2[0-8]))|(09(0[1-6]))|(10(0[1-9]|1[0-7]))|(11(0[1-4]))|(12(0[1-9]|1[0-9]))|
        (13(0[1-9]|1[0-9]|2[0-8]))|(14(0[1-9]|1[0-6]))|(15(0[1-9]|1[0-9]|2[0-3]))|(16(0[1-9]|1[0-9]|2[0-8]))|
        (17(0[1-9]))|(18(0[1-9]|1[0-1])))-((19[4-9][0-9])|(20[0-9]{2}))-([0-9]{5})$/', $data['identidad'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Identidad inválida']);
        exit;
        }
        */
        
        if (!isset($data['telefono']) || !preg_match('/^[0-9\+\-\s]+$/', $data['telefono'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Teléfono inválido']);
            exit;
        }

        /*
        if (!isset($data['telefono']) || !preg_match('/^(\+504|504|\(\+504\))?[-]?([369][0-9]{3})[-]?([0-9]{4})+$/', 
        $data['telefono'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Teléfono inválido']);
            exit;
        }
        */

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
        $allowedImageTypesDni = ['image/jpeg', 'image/jpg', 'image/tiff', 'image/avif', 'image/png', 'image/webp', 'application.pdf'];
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
        $allowedCertTypes = ['image/jpeg', 'image/jpg', 'image/tiff', 'image/avif', 'image/png', 'image/webp, application.pdf'];
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

        // Llamar al modelo para insertar el aspirante.
        try {
            $aspiranteModel = new Aspirante();
            $numSolicitud = $aspiranteModel->insertarAspirante(
                $data['nombre'],
                $data['apellido'],
                $data['identidad'],
                $data['telefono'],
                $data['correo'],
                $fotoRuta,
                $fotodniRuta,  // Nuevo parámetro
                $carrera_principal_id,
                $carrera_secundaria_id,
                $centro_id,
                $certificadoRuta
            );
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
        
        http_response_code(200);
        echo json_encode(['numSolicitud' => $numSolicitud, 'message' => 'Aspirante ingresado exitosamente']);
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

 
}
?>
