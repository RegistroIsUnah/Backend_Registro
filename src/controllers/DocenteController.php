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
        try {
            // Obtenemos los docentes con los roles y nombre del departamento
            $docentes = $this->modelo->obtenerDocentesConRoles($dept_id);
            
            // Si no hay docentes, retornamos un mensaje
            if (empty($docentes)) {
                echo json_encode(['error' => 'No se encontraron docentes para el departamento especificado.']);
                return;
            }
            
            // Devolvemos los datos en formato JSON
            echo json_encode(['docentes' => $docentes]);

        } catch (Exception $e) {
            echo json_encode(['error' => 'Error al obtener los docentes: ' . $e->getMessage()]);
        }
    }
}
?>
