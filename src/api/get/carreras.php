<?php

// Archivo: src/api/get/carreras.php

/**
 * API para obtener la lista de carreras.
 *
 * Permite filtrar carreras por centro a través del parámetro GET 'centro_id'.
 * servidor:puerto/src/api/get/carreras.php?centro_id=1
 * Si no se especifica, retorna todas las carreras.
 *
 * @author Ruben Diaz
 * @version 1.0
 *
 * Métodos soportados:
 * - GET: Retorna la lista de carreras en formato JSON.
 *
 * Parámetros:
 * - centro_id (opcional): ID del centro para filtrar las carreras.
 *
 * Respuestas HTTP:
 * - 200 OK: Devuelve la lista de carreras.
 * - 500 Internal Server Error: En caso de error al obtener los datos.
 * 
 * Ejemplo respuesta
 * 
 * [
 *   {
 *       "carrera_id": "1",
 *       "nombre": "Ingeniería en Sistemas"
 *   },
 *   {
 *       "carrera_id": "2",
 *       "nombre": "Ingeniería Civil"
 *   }
 * ]
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../modules/config/Environments.php';
require_once __DIR__ . '/../../modules/config/DataBase.php';

$db = new DataBase();
$conn = $db->getConnection();

/**
 * Obtiene la lista de carreras.
 *
 * Si se especifica un centro_id, retorna solo las carreras asociadas a ese centro.
 * De lo contrario, retorna todas las carreras.
 *
 * @param mysqli $conn Conexión a la base de datos.
 * @param int|null $centro_id (Opcional) ID del centro para filtrar carreras.
 * @return array Lista de carreras.
 */
function getCarreras($conn, $centro_id = null) {
    $carreras = [];
    if ($centro_id !== null) {
        // Se asume que existe una relación entre centros y carreras en la tabla CentroCarrera
        $stmt = $conn->prepare("
            SELECT c.carrera_id, c.nombre 
            FROM Carrera c
            INNER JOIN CentroCarrera cc ON c.carrera_id = cc.carrera_id
            WHERE cc.centro_id = ?
        ");
        $stmt->bind_param("i", $centro_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $carreras[] = $row;
        }
        $stmt->close();
    } else {
        $sql = "SELECT carrera_id, nombre FROM Carrera";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $carreras[] = $row;
        }
    }
    return $carreras;
}

// Obtener el parámetro centro_id si se envía
$centro_id = isset($_GET['centro_id']) ? intval($_GET['centro_id']) : null;

$carreras = getCarreras($conn, $centro_id);
echo json_encode($carreras);
?>
