<?php
/**
 * Cron Job para actualizar listas de espera de secciones y laboratorios.
 *
 * Este script promueve estudiantes de EN_ESPERA a MATRICULADO cuando hay cupos disponibles,
 * tanto para secciones como para laboratorios, manteniendo el orden de inscripción.
 *
 * @package CronJobs
 * @author Ruben Diaz
 * @version 1.0
 */

require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Actualiza las listas de espera para secciones y laboratorios.
 *
 * @return void
 */
function actualizarListasEspera() {
    $database = new Database();
    $conn = $database->getConnection();
    
    try {
        // Obtener IDs de estados necesarios
        $queryEstados = "SELECT 
            (SELECT estado_matricula_id FROM EstadoMatricula WHERE nombre = 'MATRICULADO') AS matriculado,
            (SELECT estado_matricula_id FROM EstadoMatricula WHERE nombre = 'EN_ESPERA') AS en_espera,
            (SELECT estado_seccion_id FROM EstadoSeccion WHERE nombre = 'ACTIVA') AS activo";
        
        $result = $conn->query($queryEstados);
        
        if ($result->num_rows === 0) {
            throw new Exception("No se pudieron obtener los estados necesarios");
        }
        
        $estados = $result->fetch_assoc();
        
        // 1. Procesar secciones con cupos disponibles
        procesarSecciones($conn, $estados);
        
        // 2. Procesar laboratorios con cupos disponibles
        procesarLaboratorios($conn, $estados);
        
        echo "Proceso de actualización de listas de espera completado con éxito";
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    } finally {
        $conn->close();
    }
}

/**
 * Procesa las secciones con cupos disponibles
 */
function procesarSecciones($conn, $estados) {
    // Obtener secciones activas con cupos disponibles
    $querySecciones = "SELECT s.seccion_id, s.cupos,
                      (SELECT COUNT(*) FROM Matricula m 
                       WHERE m.seccion_id = s.seccion_id 
                       AND m.estado_matricula_id = ?) AS matriculados
                      FROM Seccion s
                      WHERE s.estado_seccion_id = ?
                      HAVING matriculados < cupos";
    
    $stmt = $conn->prepare($querySecciones);
    $stmt->bind_param("ii", $estados['matriculado'], $estados['activo']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($seccion = $result->fetch_assoc()) {
        $vacantes = $seccion['cupos'] - $seccion['matriculados'];
        
        // Obtener estudiantes en espera ordenados
        $queryEspera = "SELECT matricula_id 
                       FROM Matricula 
                       WHERE seccion_id = ? 
                       AND estado_matricula_id = ?
                       ORDER BY orden_inscripcion ASC
                       LIMIT ?";
        
        $stmt2 = $conn->prepare($queryEspera);
        $stmt2->bind_param("iii", $seccion['seccion_id'], $estados['en_espera'], $vacantes);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        
        // Promover estudiantes a matriculado
        while ($matricula = $result2->fetch_assoc()) {
            $update = "UPDATE Matricula 
                      SET estado_matricula_id = ?, orden_inscripcion = NULL 
                      WHERE matricula_id = ?";
            
            $stmt3 = $conn->prepare($update);
            $stmt3->bind_param("ii", $estados['matriculado'], $matricula['matricula_id']);
            $stmt3->execute();
            $stmt3->close();
        }
        
        // Reordenar lista de espera restante
        reordenarListaEspera($conn, 'seccion', $seccion['seccion_id'], $estados['en_espera']);
        
        $stmt2->close();
    }
    
    $stmt->close();
}

/**
 * Procesa los laboratorios con cupos disponibles
 */
function procesarLaboratorios($conn, $estados) {
    // Obtener laboratorios activos con cupos disponibles
    $queryLabs = "SELECT l.laboratorio_id, l.cupos,
                 (SELECT COUNT(*) FROM Matricula m 
                  WHERE m.laboratorio_id = l.laboratorio_id 
                  AND m.estado_laboratorio_id = ?) AS matriculados
                 FROM Laboratorio l
                 WHERE l.estado_seccion_id = ?
                 HAVING matriculados < cupos";
    
    $stmt = $conn->prepare($queryLabs);
    $stmt->bind_param("ii", $estados['matriculado'], $estados['activo']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($lab = $result->fetch_assoc()) {
        $vacantes = $lab['cupos'] - $lab['matriculados'];
        
        // Obtener estudiantes en espera ordenados
        $queryEspera = "SELECT matricula_id 
                       FROM Matricula 
                       WHERE laboratorio_id = ? 
                       AND estado_laboratorio_id = ?
                       ORDER BY orden_inscripcion_lab ASC
                       LIMIT ?";
        
        $stmt2 = $conn->prepare($queryEspera);
        $stmt2->bind_param("iii", $lab['laboratorio_id'], $estados['en_espera'], $vacantes);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        
        // Promover estudiantes a matriculado
        while ($matricula = $result2->fetch_assoc()) {
            $update = "UPDATE Matricula 
                      SET estado_laboratorio_id = ?, orden_inscripcion_lab = NULL 
                      WHERE matricula_id = ?";
            
            $stmt3 = $conn->prepare($update);
            $stmt3->bind_param("ii", $estados['matriculado'], $matricula['matricula_id']);
            $stmt3->execute();
            $stmt3->close();
        }
        
        // Reordenar lista de espera restante
        reordenarListaEspera($conn, 'laboratorio', $lab['laboratorio_id'], $estados['en_espera']);
        
        $stmt2->close();
    }
    
    $stmt->close();
}

/**
 * Reordena la lista de espera restante
 */
function reordenarListaEspera($conn, $tipo, $id, $estadoEnEspera) {
    $campoId = ($tipo == 'seccion') ? 'seccion_id' : 'laboratorio_id';
    $campoOrden = ($tipo == 'seccion') ? 'orden_inscripcion' : 'orden_inscripcion_lab';
    $campoEstado = ($tipo == 'seccion') ? 'estado_matricula_id' : 'estado_laboratorio_id';
    
    // Primero actualizar a orden temporal para evitar duplicados
    $updateTemp = "UPDATE Matricula 
                  SET $campoOrden = $campoOrden + 10000 
                  WHERE $campoId = ? AND $campoEstado = ?";
    
    $stmt = $conn->prepare($updateTemp);
    $stmt->bind_param("ii", $id, $estadoEnEspera);
    $stmt->execute();
    $stmt->close();
    
    // Luego asignar el orden correcto
    $updateFinal = "UPDATE Matricula m
                   JOIN (
                       SELECT matricula_id, 
                       ROW_NUMBER() OVER (ORDER BY $campoOrden) AS new_order
                       FROM Matricula
                       WHERE $campoId = ? AND $campoEstado = ?
                   ) AS ranked
                   ON m.matricula_id = ranked.matricula_id
                   SET m.$campoOrden = ranked.new_order";
    
    $stmt = $conn->prepare($updateFinal);
    $stmt->bind_param("ii", $id, $estadoEnEspera);
    $stmt->execute();
    $stmt->close();
}

actualizarListasEspera();
?>