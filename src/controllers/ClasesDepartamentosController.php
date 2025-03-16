<?php
require_once __DIR__ . '/../models/ClasesDepartamentosModel.php';

class ClasesDepartamentosController {
    private $model;

    public function __construct() {
        $this->model = new ClasesDepartamentosModel();
    }

    private function responder($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
    }

    private function error($mensaje, $statusCode) {
        $this->responder(['error' => $mensaje], $statusCode);
    }

    /**
     * Obtiene clases y secciones por departamento, año y período
     */
    public function obtenerClasesPorDepartamento() {
        $params = ['departamentoId', 'anio', 'periodo'];
        
        foreach ($params as $param) {
            if (!isset($_GET[$param]) || empty($_GET[$param])) {
                return $this->error("Parámetro requerido: $param", 400);
            }
        }

        try {
            $deptId = (int)$_GET['departamentoId'];
            $anio = (int)$_GET['anio'];
            $periodo = $_GET['periodo'];

            if (!in_array($periodo, ['1', '2', '3'])) {
                return $this->error('Período inválido (valores permitidos: 1, 2, 3)', 400);
            }

            $data = $this->model->obtenerClasesYSecciones($deptId, $anio, $periodo);

            if (empty($data)) {
                return $this->error('No se encontraron clases activas', 404);
            }

            $response = $this->procesarDatos($data);
            $this->responder($response);

        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            $this->error('Error interno del servidor', 500);
        }
    }

    /**
     * Estructura los datos agrupando secciones por clase
     */
    private function procesarDatos($data) {
        $clases = [];
        
        foreach ($data as $row) {
            $claseId = $row['clase_id'];
            
            if (!isset($clases[$claseId])) {
                $clases[$claseId] = [
                    'clase_id' => $claseId,
                    'nombre_clase' => $row['nombre_clase'],
                    'secciones' => []
                ];
            }
            
            $clases[$claseId]['secciones'][] = [
                'seccion_id' => $row['seccion_id'],
                'codigo' => $row['codigo_seccion'],
                'horario' => [
                    'inicio' => $row['hora_inicio'],
                    'fin' => $row['hora_fin']
                ],
                'aula' => $row['aula'],
                'docente' => $row['docente']
            ];
        }
        
        return array_values($clases);
    }
}
?>