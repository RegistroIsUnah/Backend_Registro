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
        header('Content-Type: application/json');
        
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

    public function obtenerPerfilEstudiante($estudianteId) {
        header('Content-Type: application/json');
        
        try {
            session_start();
            
            // Validar autenticación
            if (!isset($_SESSION['usuario_id'])) {
                throw new Exception('Debe iniciar sesión', 401);
            }
    
            // Determinar ID a usar
            $idFinal = $estudianteId ?? $_SESSION['estudiante_id'];
            
            // Validar permisos
            if ($_SESSION['rol'] !== 'admin' && $_SESSION['estudiante_id'] != $idFinal) {
                throw new Exception('No tiene permisos para ver este perfil', 403);
            }
    
            // Obtener datos del modelo
            $perfil = $this->modelo->obtenerPerfilEstudiante($idFinal);
            
            // Formatear respuesta
            $response = [
                'success' => true,
                'data' => [
                    'informacion_personal' => [
                        'nombre_completo' => $perfil['nombre'] . ' ' . $perfil['apellido'],
                        'identidad' => $perfil['identidad'],
                        'correo' => $perfil['correo_personal'],
                        'telefono' => $perfil['telefono'],
                        'direccion' => $perfil['direccion']
                    ],
                    'academico' => [
                        'indice_global' => (float)$perfil['indice_global'],
                        'indice_periodo' => (float)$perfil['indice_periodo'],
                        'centro' => $perfil['centro'],
                        'carreras' => explode(', ', $perfil['carreras'])
                    ],
                    'cuenta' => [
                        'username' => $perfil['username']
                    ]
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
            
                // Registrar el estudiante
                $estudiante_id = $this->modelo->registrarEstudiante(
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
                    $this->modelo->relacionarEstudianteConCarreras($estudiante_id, $carreras);
                }
            
                // Enviar correo con las credenciales
                $this->modelo->enviarCorreoConCredenciales($correo, $nombre, $apellido, $usuario['username'], $usuario['password']);
                
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
}
?>