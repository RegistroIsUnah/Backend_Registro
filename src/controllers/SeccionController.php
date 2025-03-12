<?php
/**
 * Controlador de Seccion
 *
 * Maneja la lógica de negocio para crear una sección.
 *
 * @package Controllers
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

require_once __DIR__ . '/../models/Seccion.php';

class SeccionController {
    /**
     * Valida y procesa la creación de una sección.
     *
     * Se espera recibir los siguientes campos en $data:
     * - clase_id
     * - docente_id
     * - periodo_academico_id
     * - aula_id
     * - hora_inicio (formato "HH:MM:SS")
     * - hora_fin (formato "HH:MM:SS")
     * - cupos
     * - dias (cadena de días separados por comas, ej: "Lunes,Miércoles")
     *
     * @param array $data Datos recibidos del endpoint (JSON).
     * @return void
     */
    public function crearSeccion($data) {
        // Validar campos requeridos
        $required = ['clase_id', 'docente_id', 'periodo_academico_id', 'aula_id', 'hora_inicio', 'hora_fin', 'cupos', 'dias'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Falta el campo $field"]);
                exit;
            }
        }
        // Validar formato de hora (ejemplo: "08:00:00")
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $data['hora_inicio'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Formato de hora_inicio inválido']);
            exit;
        }
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $data['hora_fin'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Formato de hora_fin inválido']);
            exit;
        }
        // Convertir a enteros los campos numéricos
        $clase_id = intval($data['clase_id']);
        $docente_id = intval($data['docente_id']);
        $periodo_academico_id = intval($data['periodo_academico_id']);
        $aula_id = intval($data['aula_id']);
        $cupos = intval($data['cupos']);
        $hora_inicio = $data['hora_inicio'];
        $hora_fin = $data['hora_fin'];
        $dias = $data['dias'];

        try {
            $seccionModel = new Seccion();
            $seccion_id = $seccionModel->crearSeccion($clase_id, $docente_id, $periodo_academico_id, $aula_id, $hora_inicio, $hora_fin, $cupos, $dias);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }

        http_response_code(200);
        echo json_encode(['seccion_id' => $seccion_id, 'message' => 'Sección creada exitosamente']);
    }

    /**
     * Valida y procesa la modificación de una sección.
     *
     * Se espera recibir en $data (vía JSON) los siguientes campos:
     * - seccion_id (requerido)
     * - docente_id (opcional, para modificar; si no se envía, se pasa NULL)
     * - aula_id (opcional, para modificar; si no se envía, se pasa NULL)
     * - estado (opcional, 'ACTIVA' o 'CANCELADA'; si se envía 'CANCELADA', se debe incluir motivo_cancelacion)
     * - motivo_cancelacion (opcional, pero requerido si estado es 'CANCELADA')
     *
     * @param array $data Datos recibidos del endpoint.
     * @return void
     */
    public function modificarSeccion($data) {
        // Validar que se haya enviado el campo seccion_id
        if (!isset($data['seccion_id']) || empty($data['seccion_id'])) {
            http_response_code(400);
            echo json_encode(['error' => "Falta el campo seccion_id"]);
            exit;
        }
        $seccion_id = intval($data['seccion_id']);

        // Los demás campos son opcionales. Si no se envían, se asigna NULL.
        $docente_id = isset($data['docente_id']) && $data['docente_id'] !== "" ? intval($data['docente_id']) : null;
        $aula_id = isset($data['aula_id']) && $data['aula_id'] !== "" ? intval($data['aula_id']) : null;
        $estado = isset($data['estado']) && $data['estado'] !== "" ? $data['estado'] : null;
        $motivo_cancelacion = isset($data['motivo_cancelacion']) && $data['motivo_cancelacion'] !== "" ? $data['motivo_cancelacion'] : null;

        try {
            $seccionModel = new Seccion();
            $mensaje = $seccionModel->modificarSeccion($seccion_id, $docente_id, $aula_id, $estado, $motivo_cancelacion);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }

        http_response_code(200);
        echo json_encode(['message' => $mensaje]);
    }
}
?>
