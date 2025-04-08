<?php
/**
 * Controlador para manejar UsuarioRol
 *
 * @package Controllers
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

require_once __DIR__ . '/../models/UsuarioRol.php';

class UsuarioRolController {
    private $model;
    
    public function __construct() {
        $this->model = new UsuarioRol();
    }
    
    /**
     * Asigna roles a un usuario.
     *
     * Se espera recibir vía POST:
     *   - usuario_id: int
     *   - roles: JSON string (array de role IDs, ej. [1,2,3])
     *
     * @param array $data Datos enviados vía POST.
     */
    public function asignarRoles($data) {
        if (empty($data['usuario_id']) || !is_numeric($data['usuario_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Falta o es inválido el parámetro usuario_id']);
            return;
        }
        $usuario_id = (int)$data['usuario_id'];
        
        if (empty($data['roles'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Falta el parámetro roles']);
            return;
        }
        
        $roles = json_decode($data['roles'], true);
        if (!is_array($roles)) {
            http_response_code(400);
            echo json_encode(['error' => 'El parámetro roles debe ser un array JSON válido']);
            return;
        }
        
        try {
            $this->model->asignarRoles($usuario_id, $roles);
            http_response_code(200);
            echo json_encode(['mensaje' => 'Roles asignados correctamente']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Quita roles de un usuario.
     *
     * Se espera recibir vía POST:
     *   - usuario_id: int
     *   - roles: JSON string (array de role IDs a quitar, ej. [2,3])
     *
     * @param array $data Datos enviados vía POST.
     */
    public function quitarRoles($data) {
        if (empty($data['usuario_id']) || !is_numeric($data['usuario_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Falta o es inválido el parámetro usuario_id']);
            return;
        }
        $usuario_id = (int)$data['usuario_id'];
        
        if (empty($data['roles'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Falta el parámetro roles']);
            return;
        }
        
        $roles = json_decode($data['roles'], true);
        if (!is_array($roles)) {
            http_response_code(400);
            echo json_encode(['error' => 'El parámetro roles debe ser un array JSON válido']);
            return;
        }
        
        try {
            $this->model->quitarRoles($usuario_id, $roles);
            http_response_code(200);
            echo json_encode(['mensaje' => 'Roles eliminados correctamente']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Asigna roles a un docente y actualiza los departamentos o carreras según corresponda.
     */
    public function asignarRolesDocente($docente_id, $roles, $departamento_id = null, $carrera_id = null) {
        try {
            // Llamar directamente al método del modelo para asignar los roles
            $this->model->asignarRolesDocente($docente_id, $roles, $departamento_id, $carrera_id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Roles asignados correctamente'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Quita roles de un docente y actualiza el departamento o la carrera según corresponda.
     */
    public function quitarRolesDocente($docente_id, $roles, $departamento_id = null, $carrera_id = null) {
        try {
            // Llamar al modelo para quitar los roles
            $this->model->quitarRolesDocente($docente_id, $roles, $departamento_id, $carrera_id);

            echo json_encode([
                'success' => true,
                'message' => 'Roles eliminados correctamente'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }
}
?>
