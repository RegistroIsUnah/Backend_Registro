<?php
/**
 * Controlador de Docente
 *
 * Maneja la asignación de usuario a un docente.
 *
 * @package Controllers
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

require_once __DIR__ . '/../models/Docente.php';

class DocenteController {
    /**
     * @var Docente Modelo de Docente
     */
    private $modelo;
  
    /**
    * Constructor del controlador.
    */
    public function __construct() {
        $this->modelo = new Docente();
    }

    /**
     * Asigna un usuario a un docente llamando al procedimiento almacenado.
     *
     * @param int $docente_id ID del docente.
     * @param string $username Nombre de usuario.
     * @param string $password Contraseña.
     * @return void
     */
    public function asignarUsuarioDocente($docente_id, $username, $password) {
        try {
            $docenteModel = new Docente();
            $resultado = $docenteModel->asignarUsuario($docente_id, $username, $password);
            http_response_code(200);
            echo json_encode($resultado);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
  
    /**
     * Obtiene las clases activas de un docente
     * 
     * @param int $docenteId ID del docente
     * @return void
     * @author Jose Vargas
     * @version 1.0
     */
    public function obtenerClasesActDocente($docenteId) {
        header('Content-Type: application/json');
        
        try {
            // Obtener datos del modelo
            $clases = $this->modelo->obtenerClasesActDocente($docenteId);
            
            // Verificar si se obtuvieron resultados
            if (!$clases) {
                throw new Exception("No se encontraron clases para el docente especificado", 404);
            }
            
            // Formatear respuesta
            $response = [
                'success' => true,
                'data' => array_map(function($clase) {
                    return [
                        'clase_id' => $clase['clase_id'],
                        'codigo_clase' => $clase['codigo_clase'],
                        'nombre_clase' => $clase['nombre_clase'],
                        'creditos' => (int)$clase['creditos'],
                        'tiene_laboratorio' => (bool)$clase['tiene_laboratorio'],
                        'seccion' => [
                            'seccion_id' => $clase['seccion_id'],
                            'hora_inicio' => $clase['hora_inicio'],
                            'hora_fin' => $clase['hora_fin'],
                            'dias' => [
                                'lista_dia_ids' => explode(', ', $clase['lista_dia_ids']),
                                'nombres_dias' => explode(', ', $clase['nombres_dias'])
                            ],
                            'ubicacion' => [
                                'edificio' => $clase['edificio'],
                                'aula' => $clase['aula']
                            ]
                        ],
                        'periodo_academico' => [
                            'anio' => (int)$clase['anio'],
                            'numero_periodo_id' => (int)$clase['numero_periodo_id']
                        ]
                    ];
                }, $clases)
            ];
            
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

     /**
     * Lista los docentes por departamento con sus roles asignados y el nombre del departamento.
     *
     * @param int $dept_id ID del departamento
     */
    public function listarDocentesPorDepartamento($dept_id)
    {
        header('Content-Type: application/json');
        
        try {
            $docentes = $this->modelo->obtenerDocentesConRoles($dept_id);
            
            if (empty($docentes)) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'No se encontraron docentes para el departamento especificado.'
                ]);
                return;
            }
            
            // Respuesta única y consistente
            echo json_encode([
                'success' => true,
                'docentes' => $docentes
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener los docentes: ' . $e->getMessage()
            ]);
        }
    }




    /**
     * Procesa la calificación de un estudiante
     * 
     * @param array $data Datos de la calificación del estudiante
     * @return void
     */
    public function calificarEstudiante($data) {
        try {
            $camposRequeridos = ['numero_cuenta', 'seccion_id', 'calificacion'];
            foreach ($camposRequeridos as $campo) {
                if (empty($data[$campo])) {
                    throw new Exception("Campo requerido: $campo", 400);
                }
            }
    
            $dataFormateada = [
                'numero_cuenta' => $data['numero_cuenta'],
                'seccion_id' => $data['seccion_id'],
                'calificacion' => $data['calificacion'],
                'observacion' => $data['observacion'] ?? null,
                'estado_curso_id' => $data['estado_curso_id'] ?? null
            ];
    
            $resultado = $this->modelo->calificarEstudiante($dataFormateada);
    
            echo json_encode([
                'success' => true,
                'data' => $resultado
            ]);
    
        } catch (Exception $e) {
            $code = $e->getCode() ?: 500;
            http_response_code($code);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'code' => $code
            ]);
        }
    }





       /**
     * Obtiene los datos del docente a partir del ID de la sección
     */
    public function obtenerDocentePorSeccion($seccion_id) {
        try {
            // Instanciar el modelo y obtener el docente
            $data = $this->modelo->obtenerDocentePorSeccion($seccion_id);

            // Respuesta exitosa
            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
        } catch (Exception $e) {
            // Manejo de errores
            http_response_code(500);
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }



    
    /**
     * Procesa la actualización de la calificación de un estudiante
     * 
     * @param array $data Datos de la calificación del estudiante
     * @return void
     */
    public function actualizarCalificacionEstudiante($data) {
        try {
            $camposRequeridos = ['numero_cuenta', 'seccion_id', 'calificacion'];
            foreach ($camposRequeridos as $campo) {
                if (empty($data[$campo])) {
                    throw new Exception("Campo requerido: $campo", 400);
                }
            }

            $dataFormateada = [
                'numero_cuenta' => $data['numero_cuenta'],
                'seccion_id' => $data['seccion_id'],
                'calificacion' => $data['calificacion'],
                'observacion' => $data['observacion'] ?? null,
                'estado_curso_id' => $data['estado_curso_id'] ?? null
            ];

            $resultado = $this->modelo->actualizarCalificacionEstudiante($dataFormateada);

            echo json_encode([
                'success' => true,
                'data' => $resultado
            ]);

        } catch (Exception $e) {
            $code = $e->getCode() ?: 500;
            http_response_code($code);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'code' => $code
            ]);
        }
    }


       /**
     * Maneja la solicitud GET para obtener datos de un docente.
     *
     * @param int $docente_id ID del docente a consultar
     * @return void Imprime respuesta JSON
     */
    public function getDocente($docente_id) {
        header('Content-Type: application/json');
        
        try {
            $docente = $this->modelo->obtenerDocenteCompleto($docente_id);
            
            echo json_encode([
                'success' => true,
                'data' => $docente,
                'message' => 'Datos del docente obtenidos correctamente'
            ]);
            
        } catch (Exception $e) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }


    public function resumenEvaluacionesPorSeccion($seccionId) {
        header('Content-Type: application/json');
        try {
            if (empty($seccionId)) {
                throw new Exception("El campo 'seccion_id' es requerido", 400);
            }

            $data = $this->modelo->obtenerResumenEvaluacionPorSeccion($seccionId);

            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
?>