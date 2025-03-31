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
    
    // Verificar si la carpeta existe, si no, crearla
    $uploadsDir = __DIR__ . '/../../uploads/notas_aspirantes/';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true); // Crear la carpeta de forma recursiva
    }

    // Definir el nombre del archivo CSV con un nombre único
    $filePath = $uploadsDir . 'notas_' . uniqid() . '.csv';
    
    // Abrir el archivo CSV para escribirlo
    $file = fopen($filePath, 'w');

    // Cabecera con el campo de carrera
    fputcsv($file, ['Documento', 'Tipo de Examen', 'Carrera', 'Nota']);
    
    while ($row = $result->fetch_assoc()) {
        // Cada fila mostrará claramente a qué carrera pertenece el examen
        fputcsv($file, [
            $row['documento'],
            $row['tipo_examen'],
            $row['nombre_carrera'],  // Nombre de la carrera asociada
            ''  // Espacio para la nota (puedes agregar lógica para la nota si la tienes)
        ]);
    }
        
    fclose($file);

    // Responder con la ruta del archivo generado y el número de registros
    echo json_encode([
        'message' => 'CSV generado exitosamente', 
        'file' => '/uploads/estudiantesaprobados/' . basename($filePath),
        'records' => $result->num_rows
    ]);
}

try {
    generarCSV();
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
