<?php
/**
 * Controlador de Departamento
 *
 * Maneja la lógica de negocio para listar todos los departamentos.
 *
 * @package Controllers
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

require_once __DIR__ . '/../models/Departamento.php';

class DepartamentoController {
    private $model;


    public function __construct() {
        $this->model = new Departamento();
        
    }

    /**
     * Lista todos los departamentos y envía la respuesta en formato JSON.
     *
     * @return void
     */
    public function listarDepartamentos() {
        try {
            $departamentoModel = new Departamento();
            $departamentos = $departamentoModel->obtenerDepartamentos();
            http_response_code(200);
            echo json_encode($departamentos);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }


    /**
     * Estructura los datos agrupando secciones por clase.
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


    /**
     * Obtiene clases y secciones por departamento, año y período
     */

    private function responder($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
    }

    private function error($mensaje, $statusCode) {
        $this->responder(['error' => $mensaje], $statusCode);
    }



    /**
     * Obtiene clases y secciones activas por departamento, año y período.
     * @author Jose Vargas
     * @param int $deptId ID del departamento.
     * @param int $anio Año académico.
     * @param int $periodoId ID del período académico.
     */
    public function obtenerClasesPorDepartamento($departamentoId, $anio, $periodo) {
        header('Content-Type: application/json');
        
        try {
            $clases = $this->model->obtenerClasesYSecciones($departamentoId, $anio, $periodo);
            
            // Formateo más sencillo ya que el modelo ya agrupó
            return array_map(function($clase) {
                return [
                    'clase_id' => (int)$clase['clase_id'],
                    'nombre_clase' => $clase['nombre_clase'],
                    'secciones' => array_map(function($seccion) {
                        return [
                            'seccion_id' => (int)$seccion['seccion_id'],
                            'codigo' => $seccion['codigo'],
                            'horario' => [
                                'inicio' => $seccion['hora_inicio'],
                                'fin' => $seccion['hora_fin']
                            ],
                            'aula' => $seccion['aula'],
                            'docente' => $seccion['docente']
                        ];
                    }, $clase['secciones'])
                ];
            }, $clases);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener las clases: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Obtiene estudiantes por departamento
     * @param int $departamentoId ID del departamento
     * @return array Resultado con estudiantes o error
     */
    public function obtenerEstudiantesPorDepartamento($departamentoId) {
        if (!is_numeric($departamentoId)) {
            return ['error' => 'ID de departamento inválido', 'code' => 400];
        }
    
        $departamentoId = (int)$departamentoId;
        $resultado = $this->model->obtenerEstudiantesPorDepartamento($departamentoId);
    
        if (isset($resultado['error'])) {
            return $resultado;
        }
    
        $estudiantesAgrupados = [];
        foreach ($resultado as $registro) {
            $idEstudiante = $registro['estudiante_id'];
    
            // Si el estudiante aún no se ha agregado
            if (!isset($estudiantesAgrupados[$idEstudiante])) {
                $estudiantesAgrupados[$idEstudiante] = [
                    'estudiante_id' => $registro['estudiante_id'],
                    'nombre' => $registro['estudiante_nombre'],
                    'apellido' => $registro['estudiante_apellido'],
                    'numero_cuenta' => $registro['numero_cuenta'],
                    'correo_personal' => $registro['correo_personal'],
                    // Inicializamos un array de carreras
                    'carreras' => []
                ];
            }
    
            // Solo agregar la carrera si pertenece al departamento solicitado
            if ($registro['departamento_id'] == $departamentoId) {
                $estudiantesAgrupados[$idEstudiante]['carreras'][] = [
                    'carrera_id' => $registro['carrera_id'],
                    'carrera_nombre' => $registro['carrera_nombre']
                ];
            }
        }
    
        // Limpiar estudiantes sin carreras en este departamento
        foreach ($estudiantesAgrupados as $id => $estudiante) {
            if (empty($estudiante['carreras'])) {
                unset($estudiantesAgrupados[$id]);
            } else {
                // Si solo tiene una carrera, convertirla en objeto plano
                if (count($estudiante['carreras']) == 1) {
                    $estudiantesAgrupados[$id]['carrera_id'] = $estudiante['carreras'][0]['carrera_id'];
                    $estudiantesAgrupados[$id]['carrera_nombre'] = $estudiante['carreras'][0]['carrera_nombre'];
                    unset($estudiantesAgrupados[$id]['carreras']);
                }
            }
        }
    
        return [
            'departamento_id' => $departamentoId,
            'departamento_nombre' => count($resultado) > 0 ? $resultado[0]['departamento_nombre'] : '',
            'total_estudiantes' => count($estudiantesAgrupados),
            'estudiantes' => array_values($estudiantesAgrupados)
        ];
    }    
}
?>
