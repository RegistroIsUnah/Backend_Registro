<?php
/*
  GET /api/get/clases_departamento.php
  
  Obtiene las clases agrupadas por secciones para un departamento específico en un año y periodo determinado
  
  Parámetros:
  - departamentoId (required): ID del departamento académico
  - anio (required): Año académico (ej. 2024)
  - periodo (required): Periodo académico (1, 2, etc.)
  
 Ejemplo de respuesta exitosa:
 
 {
    "success": true,
    "data": [
        {
            "clase_id": 1,
            "nombre_clase": "Cálculo I",
            "secciones": [
                {
                    "seccion_id": 1,
                    "codigo": "MAT101",
                    "horario": {
                        "inicio": "08:00:00",
                        "fin": "10:00:00"
                    },
                    "aula": "Aula 101",
                    "docente": "Pedro García"
                }
            ]
        }
    ]
}
*/

    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');
    
    // Verificar parámetros primero
    $requiredParams = ['departamentoId', 'anio', 'periodo'];
    foreach ($requiredParams as $param) {
        if (!isset($_GET[$param]) || empty($_GET[$param])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => "Parámetro requerido: $param"
            ]);
            exit;
        }
    }
    
    // Validar tipos
    $departamentoId = (int)$_GET['departamentoId'];
    $anio = (int)$_GET['anio'];
    $periodo = (int)$_GET['periodo'];
    
    if ($departamentoId <= 0 || $anio <= 0 || $periodo <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Todos los parámetros deben ser números positivos'
        ]);
        exit;
    }
    
    require_once __DIR__ . '/../../controllers/DepartamentoController.php';
    
    try {
        $controller = new DepartamentoController();
        $clases = $controller->obtenerClasesPorDepartamento($departamentoId, $anio, $periodo);
        
        echo json_encode([
            'success' => true,
            'data' => $clases
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
?>