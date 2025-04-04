<?php
/**
 * API para procesar CSV de resultados de exámenes
 * 
 * Ejemplo de URL 
 * servidor:puerto/api/procesar_aspirantes
 * 
 * Métodos
 *  POST
 * 
 * Se espera recibir en la solicitud (multipart/form-data)
 * Llave: archivo_csv  
 * Valor: el archivo.csv 
 * 
 * Se espera recibir:
 *   - archivo_csv: Archivo CSV con formato:
 *     Documento,Tipo de Examen,Carerra,Nota
 *     1807-1999-01278,Examen de Física,Ingenieria,85
 *     1807-1999-01278,Examen de Matemáticas,Fisica,100
 *   
 * 
 * Respuestas:
 *   - 200 OK: CSV procesado correctamente
 *   - 400 Bad Request: Error en formato o datos
 *   - 403 Forbidden: Usuario no autorizado
 *   - 500 Internal Server Error: Error interno
 * 
 * @package API
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

try {
    // 1. Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido", 405);
    }

    // 3. Verificar archivo
    if (!isset($_FILES['archivo_csv'])) {
        throw new Exception("Archivo CSV requerido", 400);
    }

    // 4. Procesar con el controlador
    require_once __DIR__ . '/../../controllers/AspiranteController.php';

    $controller = new AspiranteController();
    $resultados = $controller->procesarCSV($_FILES['archivo_csv']['tmp_name']);

    // 5. Respuesta exitosa
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'CSV procesado correctamente',
        'data' => [
            'total' => count($resultados),
            'procesados' => count(array_filter($resultados, function($r) { return $r['success']; })),
            'detalles' => $resultados
        ]
    ]);

} catch (Exception $e) {
    // Manejo de errores
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'code' => $e->getCode() ?: 500
    ]);
}