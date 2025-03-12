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
        
        // Procesar la foto del aspirante.
        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['error' => 'Error al subir la foto del aspirante']);
            exit;
        }
        $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
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
        if (!in_array($_FILES['fotodni']['type'], $allowedImageTypes)) {
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
        $allowedCertTypes = ['application/pdf', 'image/jpeg', 'image/png'];
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
}
?>
