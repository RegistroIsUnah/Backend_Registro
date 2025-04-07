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
     * Modelo de secciones
     * @var Seccion
     */
    private $modelo;
    
    /**
     * Constructor - Inicializa el modelo
     */
    public function __construct()
    {
        require_once __DIR__ . '/../models/Seccion.php';
        $this->modelo = new Seccion();
    }


   /**
     * Valida y procesa la creación de una sección.
     *
     * Se espera recibir los siguientes campos en $data:
     * - clase_id
     * - docente_id
     * - periodo_academico_id
     * - aula_id
     * - cupos
     * - hora_inicio (formato "HH:MM:SS")
     * - hora_fin (formato "HH:MM:SS")
     * - dias (cadena de días separados por comas, ej: "Lunes,Miércoles")
     *
     * @param array $data Datos recibidos del endpoint (JSON).
     * @return void
     */
    public function crearSeccion($data, $files) {
        // Validar campos requeridos (clase_id, docente_id, periodo_academico_id, etc.)
        $required = ['clase_id', 'docente_id', 'periodo_academico_id', 'aula_id', 'hora_inicio', 'hora_fin', 'cupos', 'dias'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Falta el campo $field"]);
                exit;
            }
        }

        // Convertir numéricos
        $clase_id             = intval($data['clase_id']);
        $docente_id           = intval($data['docente_id']);
        $periodo_academico_id = intval($data['periodo_academico_id']);
        $aula_id              = intval($data['aula_id']);
        $cupos                = intval($data['cupos']);
        $hora_inicio          = $data['hora_inicio'];
        $hora_fin             = $data['hora_fin'];
        $dias                 = $data['dias'];

        // Manejo de archivo de video (opcional).
        $video_url = null;
        if (isset($files['video']) && $files['video']['error'] === UPLOAD_ERR_OK) {
            // Validar tipo de archivo de video 
            $allowedVideoTypes = ['video/mp4', 'video/avi', 'video/mpeg', 'video/quicktime'];
            if (!in_array($files['video']['type'], $allowedVideoTypes)) {
                http_response_code(400);
                echo json_encode(['error' => 'Tipo de video no permitido']);
                exit;
            }
            // Subir el archivo
            $uploadsDirVideo = __DIR__ . '/../../uploads/videos/';
            if (!is_dir($uploadsDirVideo)) {
                mkdir($uploadsDirVideo, 0755, true);
            }
            $extVideo = pathinfo($files['video']['name'], PATHINFO_EXTENSION);
            $videoName = uniqid('video_', true) . '.' . $extVideo;
            $fullPathVideo = $uploadsDirVideo . $videoName;

            if (!move_uploaded_file($files['video']['tmp_name'], $fullPathVideo)) {
                http_response_code(500);
                echo json_encode(['error' => 'No se pudo guardar el video']);
                exit;
            }
            // Guardar la ruta relativa
            $video_url = 'uploads/videos/' . $videoName;
        }

        try {
            // Instanciar el modelo y crear la sección
            $seccionModel = new Seccion();
            $seccion_id = $seccionModel->crearSeccion(
                $clase_id,
                $docente_id,
                $periodo_academico_id,
                $aula_id,
                $hora_inicio,
                $hora_fin,
                $cupos,
                $dias,
                $video_url
            );
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }

        http_response_code(200);
        echo json_encode([
            'seccion_id' => $seccion_id,
            'message' => 'Sección creada exitosamente'
        ]);
    }

    /**
     * Valida y procesa la modificación de una sección.
     *
     * Se espera recibir en $data los siguientes campos:
     * - seccion_id (requerido)
     * - docente_id (opcional)
     * - aula_id (opcional)
     * - estado (opcional, 'ACTIVA' o 'CANCELADA')
     * - motivo_cancelacion (opcional, requerido si estado es 'CANCELADA')
     * - cupos (opcional)
     * - video_url (opcional)
     *
     * @param array $data Datos recibidos del endpoint.
     * @return void
     */
    public function modificarSeccion($data) {
        // Validar campos requeridos
        if (!isset($data['seccion_id']) || empty($data['seccion_id'])) {
            http_response_code(400);
            echo json_encode(['error' => "Falta el campo seccion_id"]);
            exit;
        }
        
        // Asignar variables con valores de la solicitud
        $seccion_id = intval($data['seccion_id']);
        $docente_id = isset($data['docente_id']) && $data['docente_id'] !== "" ? intval($data['docente_id']) : null;
        $aula_id = isset($data['aula_id']) && $data['aula_id'] !== "" ? intval($data['aula_id']) : null;
        $estado = isset($data['estado']) && $data['estado'] !== "" ? $data['estado'] : null;
        $motivo_cancelacion = isset($data['motivo_cancelacion']) && $data['motivo_cancelacion'] !== "" ? $data['motivo_cancelacion'] : null;
        $cupos = isset($data['cupos']) && $data['cupos'] !== "" ? intval($data['cupos']) : null;
        $video_url = isset($data['video_url']) && $data['video_url'] !== "" ? $data['video_url'] : null;

        try {
            // Instanciar el modelo Seccion y llamar a la función para modificar la sección
            $seccionModel = new Seccion();
            $mensaje = $seccionModel->modificarSeccion(
                $seccion_id,
                $docente_id,
                $aula_id,
                $estado,
                $motivo_cancelacion,
                $cupos,
                $video_url
            );
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }

        http_response_code(200);
        echo json_encode(['message' => $mensaje]);
    }

     /**
     * Obtiene las secciones de una clase y envía la respuesta en JSON.
     *
     * @param int $clase_id ID de la clase.
     * @return void
     */
    public function getSeccionesPorClase($clase_id) {
        try {
            $seccionModel = new Seccion();
            $secciones = $seccionModel->obtenerSeccionesPorClase($clase_id);
            http_response_code(200);
            echo json_encode($secciones);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Obtiene las secciones de una clase en estado activo y envía la respuesta en JSON.
     *
     * @param int $clase_id ID de la clase.
     * @return void
     */
    public function getSeccionesPorClaseMatricula($clase_id) {
        try {
            $seccionModel = new Seccion();
            $secciones = $seccionModel->obtenerSeccionesPorClaseMatricula($clase_id);
            http_response_code(200);
            echo json_encode($secciones);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }


    /**
     * Genera un reporte de secciones
     *
     * @param int $deptId ID del departamento
     * @param string $formato Formato de salida (json, csv, pdf)
     */
    public function generarReporte(int $deptId, string $formato)
    {
        try {
            $datos = $this->modelo->getSeccionesByDepartamento($deptId);
            $nombreDepartamento = $this->modelo->getNombreDepartamento($deptId);

            switch (strtolower($formato)) {
                case 'csv':
                    $resultado = $this->generarCSV($datos, $nombreDepartamento);
                    echo json_encode($resultado); 
                    break;
                case 'pdf':
                    $resultado = $this->generarPDF($datos, $nombreDepartamento);
                    echo json_encode($resultado); 
                    break;
                
                default:
                    echo json_encode($datos);
            }
        } catch (Exception $e) {
            throw new Exception("Error al generar reporte: " . $e->getMessage());
        }
    }

    /**
     * Genera un csv
     *
     * @param string $nombreDepartamento nombre del departamento
     * @param array  $datos un arreglo de datos
     */
    private function generarCSV(array $datos, string $nombreDepartamento)
    {
        $nombreArchivo = "reporte_secciones_dept_{$nombreDepartamento}_" . date('YmdHis') . '.csv';
        $rutaCompleta = __DIR__ . '/../../uploads/reporte_secciones/' . $nombreArchivo;
        
        if (!file_exists(dirname($rutaCompleta))) {
            mkdir(dirname($rutaCompleta), 0777, true);
        }

        $fp = fopen($rutaCompleta, 'w');
        fputcsv($fp, array_keys($datos[0]));
        foreach ($datos as $fila) {
            fputcsv($fp, $fila);
        }
        fclose($fp);

        echo json_encode([
            'success' => true,
            'file' => '/uploads/reporte_secciones/' . $nombreArchivo
        ]);
    }

    /**
     * Genera un PDF con el reporte de secciones usando DomPDF
     * 
     * @param array $datos Datos de las secciones
     * @param string $nombreDepartamento nombre del departamento
     * @return array Resultado con la ruta del archivo
     */
    public function generarPDF(array $datos, string $nombreDepartamento): array
    {
        // Cargar Dompdf manualmente
        require_once __DIR__ . '/../dompdf/autoload.inc.php';
        
        // Configurar opciones
        $options = new Dompdf\Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Helvetica');
        $options->set('isPhpEnabled', true); // Permite usar PHP en el HTML
        
        $dompdf = new Dompdf\Dompdf($options);
        
        // Construir HTML del reporte
        $html = $this->generarHTMLReporte($datos, $nombreDepartamento);
        
        // Procesar PDF
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Guardar archivo
        $nombreArchivo = "reporte_secciones_{$nombreDepartamento}_" . date('YmdHis') . '.pdf';
        $rutaCompleta = __DIR__ . '/../../uploads/reporte_secciones/' . $nombreArchivo;
        
        // Crear directorio si no existe
        if (!file_exists(dirname($rutaCompleta))) {
            mkdir(dirname($rutaCompleta), 0777, true);
        }
        
        file_put_contents($rutaCompleta, $dompdf->output());
        
        return [
            'success' => true,
            'file' => '/uploads/reporte_secciones/' . $nombreArchivo
        ];
    }
    
    /**
     * Genera el HTML para el reporte
     * 
     * @param array $datos Datos de las secciones
     * @param string $nombreDepartamento nombre del departamento
     * @return string HTML formateado
     */
    private function generarHTMLReporte(array $datos, string $nombreDepartamento): string
    {
        // Fecha en español
        setlocale(LC_TIME, 'es_HN.UTF-8');
        //$fechaGeneracion = strftime("%d de %B de %Y a las %H:%M:%S");
        
        // CSS mejorado para el reporte
        $css = "
        <style>
            @page { margin: 2cm; }
            body { 
                font-family: 'Roboto', 'Helvetica Neue', Arial, sans-serif; 
                font-size: 12px;
                line-height: 1.5;
                color: #333;
                margin: 0;
                padding: 0;
            }
            .header {
                position: relative;
                text-align: center;
                padding-bottom: 20px;
                border-bottom: 2px solid #00529b;
                margin-bottom: 30px;
            }
            .logo {
                max-width: 180px;
                margin-bottom: 10px;
            }
            h1 {
                color: #00529b;
                font-size: 20px;
                font-weight: 600;
                margin: 5px 0;
            }
            h2 {
                color: #666;
                font-size: 16px;
                font-weight: 400;
                margin: 5px 0 20px 0;
            }
            .resumen {
                background-color: #f8f9fa;
                border-left: 4px solid #00529b;
                padding: 10px 15px;
                margin-bottom: 20px;
                font-size: 13px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 11px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            thead {
                display: table-header-group;
            }
            th {
                background-color: #00529b;
                color: white;
                padding: 10px 8px;
                text-align: left;
                font-weight: 600;
                border: 1px solid #004081;
            }
            td {
                padding: 8px;
                border: 1px solid #ddd;
                vertical-align: middle;
            }
            .fila-par { background-color: #f9f9f9; }
            .fila-impar { background-color: white; }
            .texto-centro { text-align: center; }
            .texto-derecha { text-align: right; }
            .resaltado { font-weight: bold; }
            .footer {
                margin-top: 30px;
                padding-top: 10px;
                border-top: 1px solid #ddd;
                font-size: 10px;
                color: #666;
                text-align: center;
            }
            .page-number:before { content: counter(page); }
        </style>
        ";
        
        // Encabezados de tabla y anchos de columna
        $headers = ['ID', 'Sección', 'Código', 'Asignatura', 'Docente', 'Matriculados', 'Cupos'];
        $widths = ['7%', '8%', '12%', '35%', '23%', '8%', '7%'];
        
        // Calcular estadísticas
        $totalSecciones = count($datos);
        $totalEstudiantes = 0;
        $totalCupos = 0;
        
        foreach ($datos as $fila) {
            $totalEstudiantes += intval($fila['estudiantes_matriculados']);
            $totalCupos += intval($fila['cupos_habilitados']);
        }
        $porcentajeOcupacion = ($totalCupos > 0) ? round(($totalEstudiantes / $totalCupos) * 100, 1) : 0;
        
        // Construir filas de datos
        $filas = '';
        $contador = 0;
        
        foreach ($datos as $fila) {
            $claseFila = ($contador % 2 === 0) ? 'fila-par' : 'fila-impar';
            $ocupacionSeccion = ($fila['cupos_habilitados'] > 0) ? 
                round(($fila['estudiantes_matriculados'] / $fila['cupos_habilitados']) * 100) : 0;
            
            // Determinar si la sección está llena o casi llena
            $claseOcupacion = '';
            if ($ocupacionSeccion >= 95) {
                $claseOcupacion = 'style="color: #e74c3c; font-weight: bold;"';
            } elseif ($ocupacionSeccion >= 85) {
                $claseOcupacion = 'style="color: #e67e22; font-weight: bold;"';
            }
            
            $filas .= "<tr class='$claseFila'>";
            $filas .= "<td class='texto-centro'>{$this->esc($fila['seccion_id'])}</td>";
            $filas .= "<td class='texto-centro'>{$this->esc($fila['numero_seccion'])}</td>";
            $filas .= "<td>{$this->esc($fila['codigo_clase'])}</td>";
            $filas .= "<td>{$this->esc($fila['nombre_clase'])}</td>";
            $filas .= "<td>{$this->esc($fila['docente_asignado'])}</td>";
            $filas .= "<td class='texto-centro' $claseOcupacion>{$this->esc($fila['estudiantes_matriculados'])}</td>";
            $filas .= "<td class='texto-centro'>{$this->esc($fila['cupos_habilitados'])}</td>";
            $filas .= "</tr>";
            $contador++;
        }
        
        // HTML completo con elementos mejorados
        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
            <title>Reporte de Secciones - $nombreDepartamento</title>
            $css
        </head>
        <body>
            <div class='header'>
                <img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=' class='logo' alt='Logo UNAH'>
                <h1>Universidad Nacional Autónoma de Honduras</h1>
                <h2>Reporte de Secciones - $nombreDepartamento</h2>
            </div>
            
            <div class='resumen'>
                <strong>Resumen:</strong> Este reporte presenta un total de <span class='resaltado'>$totalSecciones secciones</span> 
                con <span class='resaltado'>$totalEstudiantes estudiantes matriculados</span> de un total de 
                <span class='resaltado'>$totalCupos cupos disponibles</span>. 
                Porcentaje de ocupación: <span class='resaltado'>$porcentajeOcupacion%</span>.
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th style='width: {$widths[0]};'>ID</th>
                        <th style='width: {$widths[1]};'>Sección</th>
                        <th style='width: {$widths[2]};'>Código</th>
                        <th style='width: {$widths[3]};'>Asignatura</th>
                        <th style='width: {$widths[4]};'>Docente</th>
                        <th style='width: {$widths[5]};'>Matriculados</th>
                        <th style='width: {$widths[6]};'>Cupos</th>
                    </tr>
                </thead>
                <tbody>
                    $filas
                </tbody>
                <tfoot>
                    <tr style='background-color: #e8f4fc; font-weight: bold;'>
                        <td colspan='5' class='texto-derecha'>Total:</td>
                        <td class='texto-centro'>$totalEstudiantes</td>
                        <td class='texto-centro'>$totalCupos</td>
                    </tr>
                </tfoot>
            </table>
            
            <div class='footer'>
                <p>Generado el " . date('d/m/Y H:i:s') . " | Página <span class='page-number'></span></p>
                <p>Sistema de Gestión Académica - UNAH © " . date('Y') . "</p>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Escapa caracteres especiales para HTML
     * 
     * @param mixed $valor Valor a escapar
     * @return string Valor escapado
     */
    private function esc($valor): string
    {
        return htmlspecialchars($valor ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
?>
