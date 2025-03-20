<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

// Función para generar el CSV con los datos de la base de datos
function generarCSV() {
    // Conectar a la base de datos
    $database = new Database();
    $conn = $database->getConnection();
    
    // Consulta para obtener los datos de los estudiantes y sus exámenes
    $sql = "SELECT 
                a.identidad,
                te.nombre AS tipo_examen
            FROM 
                Aspirante a
            JOIN 
                CarreraExamen ce ON a.carrera_principal_id = ce.carrera_id
            JOIN 
                TipoExamen te ON ce.tipo_examen_id = te.tipo_examen_id
            WHERE 
                a.estado = 'ADMITIDO';"; // Ajusta según el filtro que necesites
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error preparando la consulta: " . $conn->error);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Abrir el archivo CSV para escribirlo
    $filePath = __DIR__ . '/../../uploads/aspirantes_examenes.csv';
    $file = fopen($filePath, 'w');

    // Escribir la cabecera del CSV
    fputcsv($file, ['Identidad', 'Tipo de Examen', 'Nota']);
    
    // Escribir los datos
    while ($row = $result->fetch_assoc()) {
        // Por cada estudiante, escribir las filas del CSV
        fputcsv($file, [$row['identidad'], $row['tipo_examen'], '']); // La nota será agregada manualmente luego
    }
    
    // Cerrar el archivo
    fclose($file);
    echo json_encode(['message' => 'CSV generado exitosamente', 'file' => $filePath]);
}

// Llamar a la función para generar el CSV
try {
    generarCSV();
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

?>