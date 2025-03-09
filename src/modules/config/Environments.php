<?php

/**
 * Clase para leer configuraciones del archivo de entorno (.env).
 * 
 * @author Ruben Diaz
 * @version 1.0
 */
class Environments
{
    /**
     * Lee las variables de entorno desde un archivo .env y las devuelve en un array asociativo.
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
            // Ignorar comentarios
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            $result[$key] = $value;
        }

        return $result;
    }
}
?>
