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

    public function obtenerPerfilEstudiante() {
        header('Content-Type: application/json');
        
        try {
            session_start();
            
            // Validar autenticación
            if (!isset($_SESSION['usuario_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Debe iniciar sesión']);
                return;
            }
    
            // Obtener ID del estudiante
            $estudianteId = $_SESSION['estudiante_id'] ?? null;
            
            // Validar si es admin o el mismo estudiante
            if ($_SESSION['rol'] !== 'admin' && $_SESSION['estudiante_id'] != $estudianteId) {
                http_response_code(403);
                echo json_encode(['error' => 'No autorizado']);
                return;
            }
    
            // Obtener datos del modelo
            $perfil = $this->modelo->obtenerPerfilEstudiante($estudianteId);
            
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
                        'indice_global' => $perfil['indice_global'],
                        'indice_periodo' => $perfil['indice_periodo'],
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
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
?>
