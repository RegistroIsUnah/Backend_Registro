<?php

/**
 * Clase para leer configuraciones desde un archivo de entorno (.env).
 *
 * Esta clase provee un método estático para leer un archivo .env y devolver sus variables
 * en forma de array asociativo. Se permite especificar una ruta personalizada; de lo contrario,
 * se utilizará una ruta predeterminada.
 *
 * @package config
 * @author Ruben
 * @version 1.0
 */
class Environments
{
    /**
     * Lee las variables de entorno desde un archivo .env y las devuelve en un array asociativo.
     *
     * Este método carga el archivo .env desde la ruta especificada o, si no se proporciona,
     * utiliza la ruta predeterminada. Se ignoran las líneas que son comentarios (inician con '#')
     * y aquellas que no cumplen con el formato 'clave=valor'.
     *
     * @param string|null $path Ruta opcional del archivo .env. Si no se proporciona, usa la ruta predeterminada.
     * @return array Array asociativo con las variables de entorno cargadas desde el archivo.
     * @throws Exception Si el archivo .env no se encuentra o no se puede leer.
     */
    public static function read($path = null): array
    {
        if (!$path) {
            $path = __DIR__ . '/../../../.env';
        }

        if (!file_exists($path)) {
            throw new Exception("El archivo .env no se encontró en la ruta: $path");
        }

        $result = [];
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Ignorar comentarios o líneas vacías
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }
            
            // Verificar que la línea contenga el separador '='
            if (strpos($line, '=') === false) {
                continue; // Opcional: se podría lanzar una excepción si se requiere un formato estricto
            }
            
            [$key, $value] = explode('=', $line, 2);
            $result[trim($key)] = trim($value);
        }

        return $result;
    }
}
?>
