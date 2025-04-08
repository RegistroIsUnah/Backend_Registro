<?php
/**
 * Controlador para manejar Rol
 *
 * @package Controllers
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

require_once __DIR__ . '/../models/Rol.php';

class RolController
{
    private $modelo;

    /**
     * Constructor del controlador.
     */
    public function __construct()
    {
        $this->modelo = new Rol();  
    }

    /**
     * Lista todos los roles con su ID y nombre.
     */
    public function listarRoles()
    {
        try {
            // Obtener los roles
            $roles = $this->modelo->obtenerRoles();
            
            // Si no se encuentran roles, retornar mensaje
            if (empty($roles)) {
                echo json_encode(['error' => 'No se encontraron roles en la base de datos.']);
                return;
            }

            // Devolver los roles en formato JSON
            echo json_encode(['roles' => $roles]);

        } catch (Exception $e) {
            echo json_encode(['error' => 'Error al obtener los roles: ' . $e->getMessage()]);
        }
    }
}
?>