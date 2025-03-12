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
}
?>
