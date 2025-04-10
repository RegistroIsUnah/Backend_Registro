<?php
/**
 * Controlador de Estudiante
 *
 *
 * @package Controllers
 * @author Jose Vargas
 * @version 1.0
 * 
 */

require_once __DIR__ . '/../models/Estudiante.php';

class EstudianteController {
    private $modelo;

    public function __construct() {
        $this->modelo = new Estudiante();
    }

    /**
     * Obtiene los docentes de las clases del estudiante y envía a la vista
     */
    public function obtenerDocentesClases() {
        //header('Content-Type: application/json');
        
        try {
            // Obtener ID del estudiante desde sesión
            if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'estudiante') {
                http_response_code(403);
                echo json_encode(['error' => 'Acceso no autorizado']);
                return;
            }
            
            $estudianteId = $_SESSION['usuario_id'];
            
            // Obtener datos
            $docentesClases = $this->modelo->obtenerDocentesDeClases($estudianteId);
            
            if (empty($docentesClases)) {
                http_response_code(404);
                echo json_encode(['message' => 'No se encontraron clases matriculadas']);
                return;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $docentesClases
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener datos: ' . $e->getMessage()
            ]);
        }
    }
    /**
     * Obtiene el perfil del estudiante
     * 
     * @param int $estudianteId ID del estudiante (opcional)
     * @return void
     * @author Jose Vargas
     * @version 1.3
     */
    public function obtenerPerfilEstudiante($estudianteId) {
        header('Content-Type: application/json');
        
        try {
           
            // Obtener datos del modelo
            $perfil = $this->modelo->obtenerPerfilEstudiante($estudianteId);
            
            // Formatear respuesta Actualizada

            $response = [
                'success' => true,
                'data' => [
                    'informacion_personal' => [
                        'nombre_completo' => $perfil['nombre'] . ' ' . $perfil['apellido'],
                        'numero_cuenta' => $perfil['numero_cuenta'],
                        'identidad' => $perfil['identidad'],
                        'correo' => $perfil['correo_personal'],
                        'telefono' => $perfil['telefono'],
                        'direccion' => $perfil['direccion']
                    ],
                    'academico' => [
                        'indice_global' => (float)$perfil['indice_global'],
                        'indice_periodo' => (float)$perfil['indice_periodo'],
                        'centro' => [
                            'centro_id' => $perfil['centro_id'],  // Nuevo campo
                            'nombre' => $perfil['centro']
                        ],
                        'carreras' => array_map(function($id, $nombre) {
                            return [
                                'carrera_id' => $id,  // Nuevo campo
                                'nombre' => $nombre
                            ];
                        }, explode(', ', $perfil['carrerasid']), explode(', ', $perfil['carreras'])),
                        'solicitudes_pendientes' => (int)$perfil['solicitudes_pendientes']
                    ],
                    'cuenta' => [
                        'username' => $perfil['username']
                    ],
                    'fotos' => $perfil['fotos'] ? explode(', ', $perfil['fotos']) : []
                ]
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
     * Actualiza el perfil del estudiante
     * 
     * @return void
     * @author Jose Vargas
     * @version 1.0
     */
    public function actualizarPerfil() {
        header('Content-Type: application/json');
        
        try {
            session_start();
            
            // Validar autenticación
            if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['estudiante_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Debe iniciar sesión como estudiante']);
                return;
            }
            
            // Obtener método HTTP
            $metodo = $_SERVER['REQUEST_METHOD'];
            
            // Obtener datos según el método
            if ($metodo === 'PUT' || $metodo === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
            } elseif ($metodo === 'GET') {
                $input = $_GET;
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
                return;
            }
            
            // Validar datos recibidos
            if (empty($input)) {
                http_response_code(400);
                echo json_encode(['error' => 'Datos de actualización requeridos']);
                return;
            }
            
            // Actualizar perfil
            $this->modelo->actualizarPerfil($_SESSION['estudiante_id'], $input);
            
            echo json_encode([
                'success' => true,
                'message' => 'Perfil actualizado exitosamente'
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Registra la evaluación de un docente realizada por el estudiante
     * 
     * @param array $data Datos de la evaluación en formato array
     * @return void Retorna una respuesta JSON con el resultado
     * @author Jose Vargas
     * @version 1.0
     */
    public function registrarEvaluacionDocente($data) {
        header('Content-Type: application/json');
        
        try {
            // 1. Validar sesión y rol
            session_start();
            if (!isset($_SESSION['usuario_id'])) {
                throw new Exception('Debe iniciar sesión para realizar esta acción', 401);
            }

            if ($_SESSION['rol'] !== 'estudiante') {
                throw new Exception('Solo los estudiantes pueden evaluar docentes', 403);
            }

            // 2. Validar campos requeridos
            $camposRequeridos = ['docente_id', 'periodo_id', 'respuestas'];
            foreach ($camposRequeridos as $campo) {
                if (empty($data[$campo])) {
                    throw new Exception("El campo '$campo' es requerido", 400);
                }
            }

            // 3. Validar estructura de respuestas
            if (!is_array($data['respuestas']) || empty($data['respuestas'])) {
                throw new Exception("Las respuestas deben ser un array no vacío", 400);
            }

            // 4. Registrar evaluación
            $this->modelo->registrarEvaluacionDocente(
                $_SESSION['estudiante_id'], // Obtenido de la sesión
                $data['docente_id'],
                $data['periodo_id'],
                $data['respuestas']
            );

            // 5. Respuesta exitosa
            echo json_encode([
                'success' => true,
                'message' => 'Evaluación registrada correctamente',
                'data' => [
                    'docente_id' => $data['docente_id'],
                    'preguntas_respondidas' => count($data['respuestas'])
                ]
            ]);

        } catch (Exception $e) {
            // Manejo de errores
            $statusCode = $e->getCode() >= 400 ? $e->getCode() : 500;
            http_response_code($statusCode);
            
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'code' => $statusCode
            ]);
        }
    }


    /**
     * Procesa solicitud de cambio de carrera
     * 
     * @return void Retorna respuesta JSON
     * @author Jose Vargas
     * @version 1.0
     */
    public function solicitarCambioCarrera($data) {
        header('Content-Type: application/json');
        
        try {
            // Validar sesión
            session_start();
            if (!isset($_SESSION['estudiante_id'])) {
                throw new Exception('Acceso no autorizado', 401);
            }
    
            // Validar campos requeridos
            $required = ['carrera_solicitada_id'];
            foreach ($required as $campo) {
                if (empty($data[$campo])) {
                    throw new Exception("Campo requerido: $campo", 400);
                }
            }
    
            // Obtener carrera actual
            $carrerasEstudiante = $this->modelo->obtenerCarrerasEstudiante($_SESSION['estudiante_id']);
            if (empty($carrerasEstudiante)) {
                throw new Exception('El estudiante no tiene carrera registrada', 400);
            }
            $carreraActualId = $carrerasEstudiante[0]['carrera_id'];
    
            // Registrar solicitud
            $this->modelo->solicitarCambioCarrera(
                $_SESSION['estudiante_id'],
                $carreraActualId,
                $data['carrera_solicitada_id'],
                $data['motivo'] ?? null
            );
    
            echo json_encode([
                'success' => true,
                'message' => 'Solicitud registrada exitosamente'
            ]);
    
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    //Prueba

    /**
    * Procesa el archivo CSV, crea usuarios, registra estudiantes y los asigna a carreras.
    */
    public function procesarCSVEstudiantes() {
    // Asegurarse de que el archivo CSV se haya recibido correctamente
    if (!isset($_FILES['estudiantes_csv'])) {
        echo json_encode(['error' => 'Archivo CSV no recibido.']);
        return;
    }

    // Ruta temporal del archivo
    $filePath = $_FILES['estudiantes_csv']['tmp_name'];
    $file = fopen($filePath, 'r');

    // Lee el archivo CSV y comienza el procesamiento
    $successCount = 0;
    $errorCount = 0;
    
    // Saltar la cabecera
    fgetcsv($file);
    
    // Procesar cada estudiante del CSV
    while (($row = fgetcsv($file)) !== false) {
        $nombre = $row[0];
        $apellido = $row[1];
        $documento = $row[2];
        $correo = $row[3];
        $telefono = $row[4];
        $centro_id = $row[5];
        $carrera_principal = $row[6];
        $carrera_secundaria = $row[7];

        try {
            // Crear el usuario
            $usuario = $this->modelo->crearUsuarioEstudiante($nombre, $apellido);

            // Registrar el estudiante y obtener el número de cuenta generado
            $estudianteData = $this->modelo->registrarEstudiante(
                $usuario['usuario_id'], 
                $documento, 
                $nombre, 
                $apellido, 
                $correo, 
                $telefono, 
                $centro_id
            );

            // Asignar las carreras al estudiante
            $carreras = [];
            if (!empty($carrera_principal)) {
                $carreras[] = $carrera_principal;
            }
            if (!empty($carrera_secundaria)) {
                $carreras[] = $carrera_secundaria;
            }
            
            if (!empty($carreras)) {
                $this->modelo->relacionarEstudianteConCarreras($estudianteData['estudiante_id'], $carreras);
            }

            // Enviar correo con las credenciales
            $this->modelo->guardarCredencialesParaEnvio($correo, $nombre, $apellido, $usuario['username'], $usuario['password'], $estudianteData['numero_cuenta']);
            
            $successCount++;
        } catch (Exception $e) {
            $errorCount++;
            error_log("Error procesando estudiante $nombre $apellido: " . $e->getMessage());
        }
    }

    fclose($file);

    echo json_encode([
        'message' => 'Estudiantes procesados correctamente.',
        'success_count' => $successCount,
        'error_count' => $errorCount
    ]);
    }

    /**
     * Obtiene el historial de un estudiante.
     * 
     * @param int $estudiante_id ID del estudiante para el cual se obtiene el historial.
     * @return void Responde con un JSON que contiene el historial del estudiante.
     */
    public function obtenerHistorialEstudiante($estudiante_id) {
        try {
            // Llamar al modelo para obtener el historial del estudiante
            $modelo = new Estudiante();
            $historial = $modelo->obtenerHistorialEstudiante($estudiante_id);
            
            // Responder con los datos en formato JSON
            http_response_code(200);
            echo json_encode($historial);
        } catch (Exception $e) {
            // En caso de error, responder con un mensaje de error
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener el historial: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtiene los estudiantes matriculados en una sección.
     *
     * Se espera recibir en $data:
     * - seccion_id: ID de la sección.
     *
     * @param array $data Datos recibidos del endpoint.
     * @return void
     */
    public function obtenerEstudiantesMatriculadosEnSeccion($data) {
        if (!isset($data['seccion_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Falta el parámetro: seccion_id']);
            exit;
        }

        $seccion_id = intval($data['seccion_id']);

        try {
            $modelo = new Estudiante(); // Cambia esto a tu clase de modelo correcta
            $estudiantes = $modelo->obtenerEstudiantesPorSeccion($seccion_id);

            // Si se encuentran estudiantes, devolverlos como respuesta
            if (!empty($estudiantes)) {
                http_response_code(200);
                echo json_encode(['estudiantes' => $estudiantes]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'No se encontraron estudiantes matriculados en esta sección']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

     /**
     * Genera un archivo CSV con los estudiantes matriculados en una sección.
     *
     * @param array $data Datos recibidos del endpoint (sección ID).
     * @return void
     */
    public function generarCSVEstudiantesPorSeccion($data) {
        if (!isset($data['seccion_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Falta el parámetro: seccion_id']);
            exit;
        }

        $seccion_id = intval($data['seccion_id']);

        try {
            // Generar el archivo CSV y obtener la ruta
            $fileName = $this->modelo->generarCSVEstudiantesPorSeccion($seccion_id);

            // Devolver la respuesta con la ubicación del archivo
            http_response_code(200);
            echo json_encode(['message' => 'Archivo CSV generado correctamente', 'file' => $fileName]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }





    /**
     * Busca estudiantes con filtros
     * 
     * @return void
     * @version 1.0
     */
    public function buscarEstudiante($filtros) {
        header('Content-Type: application/json');
        
        try {
            // Validación
            $errores = [];
            
           
            
            if (!empty($errores)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'errors' => $errores]);
                return;
            }
    
            // Buscar en el modelo
            $resultados = $this->modelo->buscarEstudiante([
                'nombre' => $filtros['nombre'] ?? null,
                'no_cuenta' => $filtros['no_cuenta'] ?? null,
                'carrera' => $filtros['carrera'] ?? null,
                'departamento' => $filtros['departamento'] ?? null
            ]);
    
            // Respuesta
            echo json_encode([
                'success' => true,
                'data' => $resultados,
                'total' => count($resultados)
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
             
    /**
     * Valida si un estudiante puede matricular hoy, dependiendo del tipo de proceso y el índice.
     *
     * @param int $estudianteId
     * @return array
     * @throws Exception
     */
    public function validarDiaMatricula(int $estudianteId): array
    {
        $proceso = $this->modelo->obtenerProcesoActivo();

        if (!$proceso) {
            return [
                'puede_matricular' => false,
                'mensaje' => 'No hay un proceso de matrícula activo actualmente.'
            ];
        }

        $tipoProceso = strtoupper($proceso['tipo_proceso']);
        
        $zona = new DateTimeZone('America/Tegucigalpa');
        $fechaInicio = new DateTime($proceso['fecha_inicio'], $zona);
        $fechaHoy = new DateTime('now', $zona);

        //$fechaInicio = new DateTime($proceso['fecha_inicio']);
        //$fechaHoy = new DateTime();

        // Si es adiciones/cancelaciones, no hay restricción por índice
        if ($tipoProceso === 'ADICIONES_CANCELACIONES') {
            return [
                'puede_matricular' => true,
                'mensaje' => 'Puede matricular. Proceso actual: Adiciones/Cancelaciones.'
            ];
        }

        // Obtener índice global del estudiante
        $indice = $this->modelo->obtenerIndiceGlobal($estudianteId);

        if ($indice === null) {
            return [
                'puede_matricular' => false,
                'mensaje' => 'Estudiante no encontrado o sin índice asignado.'
            ];
        }

        // Calcular qué día es hoy dentro del proceso
        $intervalo = $fechaInicio->diff($fechaHoy);
        $dia = $intervalo->days + 1;

        // Obtener fechas exactas de los tres días de matrícula
        $diasProceso = [
            1 => (clone $fechaInicio)->modify('+0 days')->format('d/m/Y'),
            2 => (clone $fechaInicio)->modify('+1 days')->format('d/m/Y'),
            3 => (clone $fechaInicio)->modify('+2 days')->format('d/m/Y')
        ];

        // Determinar el día asignado por índice
        if ($indice >= 80) {
            $diaAsignado = 1;
        } elseif ($indice >= 60) {
            $diaAsignado = 2;
        } else {
            $diaAsignado = 3;
        }

        if ($dia === $diaAsignado) {
            return [
                'puede_matricular' => true,
                'mensaje' => "Puede matricular. Día $dia (hoy es su turno de matrícula)."
            ];
        } else {
            return [
                'puede_matricular' => false,
                'mensaje' => "No es su día de matrícula. Su índice es $indice. Debe matricular el día {$diasProceso[$diaAsignado]}."
            ];

        }
    }
    








    /**
     * Obtiene las clases activas de un estudiante
     *
     * @param int $estudianteId ID del estudiante
     * @return void
     * @author Jose Vargas
     */
    public function obtenerClasesActEstudiante($estudianteId) {
        header('Content-Type: application/json');

        try {
            $clases = $this->modelo->obtenerClasesActEstudiante($estudianteId);

            // Asegurar que $clases siempre sea un array (incluso vacío)
            $clases = is_array($clases) ? $clases : [];

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
                        'laboratorio' => $clase['laboratorio_id'] ? [
                            'laboratorio_id' => $clase['laboratorio_id'],
                            'codigo_laboratorio' => $clase['codigo_laboratorio'],
                            'hora_inicio' => $clase['hora_inicio_lab'],
                            'hora_fin' => $clase['hora_fin_lab'],
                            'dias' => [
                                'lista_dia_ids' => explode(', ', $clase['lista_dia_ids_lab']),
                                'nombres_dias' => explode(', ', $clase['nombres_dias_lab'])
                            ],
                            'ubicacion' => [
                                'edificio' => $clase['edificio_lab'],
                                'aula' => $clase['aula_lab']
                            ]
                        ] : null,
                        'docente' => [
                            'docente_id' => $clase['docente_id'],
                            'numero_empleado' => $clase['numero_empleado'],
                            'nombre' => $clase['nombre_docente'],
                            'apellido' => $clase['apellido_docente'],
                            'correo' => $clase['correo_docente']
                        ],
                        'periodo_academico' => [
                            'anio' => (int)$clase['anio'],
                            'numero_periodo_id' => (int)$clase['numero_periodo_id']
                        ]
                    ];
                }, $clases)
            ];

            echo json_encode($response);

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