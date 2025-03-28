<?php
require_once __DIR__ . '/../modules/config/DataBase.php';

// Función para generar el CSV con los datos de la base de datos
function generarCSV() {
    // Conectar a la base de datos
    $database = new Database();
    $conn = $database->getConnection();
    
    // Consulta para obtener los datos de los estudiantes y sus exámenes
    $sql = "SELECT 
                a.documento,
                te.nombre AS tipo_examen,
                CASE 
                    WHEN ce.carrera_id = a.carrera_principal_id THEN 'Principal'
                    WHEN ce.carrera_id = a.carrera_secundaria_id THEN 'Secundaria'
                END AS tipo_carrera,
                c.nombre AS nombre_carrera
            FROM 
                Aspirante a
            JOIN 
                EstadoAspirante est ON a.estado_aspirante_id = est.estado_aspirante_id
            JOIN 
                CarreraExamen ce ON (ce.carrera_id = a.carrera_principal_id OR ce.carrera_id = a.carrera_secundaria_id)
            JOIN 
                TipoExamen te ON ce.tipo_examen_id = te.tipo_examen_id
            JOIN 
                Carrera c ON c.carrera_id = ce.carrera_id  -- Relacionamos la carrera directamente con CarreraExamen
            WHERE 
                est.nombre = 'ADMITIDO'
                AND (a.carrera_principal_id IS NOT NULL OR a.carrera_secundaria_id IS NOT NULL)  -- Aseguramos que tenga al menos una carrera
            ORDER BY 
                a.documento, 
                tipo_carrera,
                te.nombre"; // Ajusta según el filtro que necesites
    
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
    fputcsv($file, ['Documento', 'Tipo de Examen', 'Nota']);
    
    // Escribir los datos
    while ($row = $result->fetch_assoc()) {
        // Por cada estudiante, escribir las filas del CSV
        fputcsv($file, [$row['documento'], $row['tipo_examen'], '']); // La nota será agregada manualmente luego
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