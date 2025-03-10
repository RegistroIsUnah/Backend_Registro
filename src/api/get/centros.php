<?php

// Archivo: src/api/get/centros.php

/**
 * API para obtener la lista de centros.
 *
 * @author Ruben Diaz
 * @version 1.0
 *
 * Métodos soportados:
 * - GET: Retorna la lista de centros en formato JSON.
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve la lista de centros.
 * - 500 Internal Server Error: En caso de error al obtener los datos.
 * 
 * Ejemplo respuesta
 * 
 * [
 *   {
 *       "centro_id": "1",
 *       "nombre": "Centro Universitario Regional Tegucigalpa"
 *   },
 *   {
 *       "centro_id": "2",
 *       "nombre": "Centro Universitario Regional San Pedro Sula"
 *   }
 * ]
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../modules/config/Environments.php';
require_once __DIR__ . '/../../modules/config/DataBase.php';

$db = new DataBase();
$conn = $db->getConnection();

/**
 * Obtiene la lista de centros desde la base de datos.
 *
 * @param mysqli $conn Conexión a la base de datos.
 * @return array Lista de centros.
 */
function getCentros($conn) {
    $centros = [];
    $sql = "SELECT centro_id, nombre FROM Centro";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $centros[] = $row;
        }
    }
    return $centros;
}

$centros = getCentros($conn);
echo json_encode($centros);
?>
