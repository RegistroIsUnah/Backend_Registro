<?php
/**
 * Cron Job para actualizar el estado de ProcesosExcepcionales.
 *
 * Este script actualiza el estado de las entradas en la tabla ProcesosExcepcionales a 'INACTIVO'
 * cuando la fecha_fin es menor que la fecha actual y el estado es 'ACTIVO'.
 * Se ejecuta mediante un cron job en el servidor.
 *
 * @package CronJobs
 * @author Ruben Diaz
 * @version 1.0
 * 
 */

require_once __DIR__ . '/../modules/config/DataBase.php';

/**
 * Actualiza el estado de los procesos excepcionales.
 *
 * Se conecta a la base de datos utilizando el archivo DataBase.php y ejecuta la consulta
 * para actualizar a 'INACTIVO' aquellos procesos excepcionales cuya fecha_fin ya ha pasado.
 *
 * @return void
 */
function actualizarEstadoProcesosExcepcionales() {
    $database = new Database();
    $conn = $database->getConnection();

    $query = "UPDATE ProcesosExcepcionales SET estado = 'INACTIVO' WHERE fecha_fin < NOW() AND estado = 'ACTIVO'";
    if ($conn->query($query)) {
        echo "Estado actualizado correctamente";
    } else {
        echo "Error en la actualización: " . $conn->error;
    }
    $conn->close();
}

actualizarEstadoProcesosExcepcionales();
