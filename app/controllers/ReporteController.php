<?php

require_once __DIR__ . '/../models/Reporte.php';

class ReporteController
{
    private Reporte $reporte;

    private const TIPOS_VALIDOS = [
        'ventas' => ['ganancias_mes', 'productos_mas_vendidos', 'historial_ventas'],
        'compras' => ['historial_compras'],
        'inventario' => ['control_inventario', 'baja_existencia']
    ];

    public function __construct(PDO $conexion)
    {
        $this->reporte = new Reporte($conexion);
    }

    public function index(): void
    {
        $periodoSeleccionado = $_GET['periodo'] ?? 'mes_actual';
        [$desde, $hasta] = $this->obtenerFechas();

        $periodoDesde = $desde;
        $periodoHasta = $hasta;
        $reportes = $this->reporte->obtenerResumenPrincipal($desde, $hasta);

        // Ventas y compras dependen del rango seleccionado; inventario siempre es actual.
        $periodoTexto = date('d/m/Y', strtotime($desde)) . ' - ' . date('d/m/Y', strtotime($hasta));
        foreach ($reportes as &$item) {
            if ($item['codigo'] !== 'INV') {
                $item['periodo'] = $periodoTexto;
            }
            $item['url'] .= '&' . http_build_query([
                'desde' => $desde,
                'hasta' => $hasta,
                'periodo' => $periodoSeleccionado
            ]);
        }
        unset($item);

        require __DIR__ . '/../views/admin/reportes.php';
    }

    public function detalle(): void
    {
        $categoria = $_GET['reporte'] ?? 'ventas';
        $tipo = $_GET['tipo'] ?? 'ganancias_mes';

        if (!isset(self::TIPOS_VALIDOS[$categoria]) || !in_array($tipo, self::TIPOS_VALIDOS[$categoria], true)) {
            http_response_code(404);
            echo 'El reporte solicitado no existe.';
            return;
        }

        [$desde, $hasta] = $this->obtenerFechas();
        $titulo = $this->obtenerTitulo($tipo);
        $datos = [];

        switch ($tipo) {
            case 'ganancias_mes':
                $datos = $this->reporte->obtenerGanancias($desde, $hasta);
                break;
            case 'productos_mas_vendidos':
                $datos = $this->reporte->obtenerProductosMasVendidos($desde, $hasta);
                break;
            case 'historial_ventas':
                $datos = $this->reporte->obtenerHistorialVentas($desde, $hasta);
                break;
            case 'historial_compras':
                $datos = $this->reporte->obtenerHistorialCompras($desde, $hasta);
                break;
            case 'control_inventario':
                $datos = $this->reporte->obtenerControlInventario();
                break;
            case 'baja_existencia':
                $datos = $this->reporte->obtenerBajaExistencia();
                break;
        }

        require __DIR__ . '/../views/admin/reportes_detalle.php';
    }

    public function exportar(): void
    {
        $categoria = $_GET['reporte'] ?? 'resumen';
        $tipo = $_GET['tipo'] ?? '';
        [$desde, $hasta] = $this->obtenerFechas();

        $filas = [];
        $nombre = 'reporte_' . date('Ymd_His') . '.csv';

        if ($categoria === 'resumen') {
            $resumen = $this->reporte->obtenerResumenPrincipal($desde, $hasta);
            $filas[] = ['Reporte', 'Periodo', 'Operaciones / productos', 'Resultado', 'Estado'];
            foreach ($resumen as $item) {
                $resultado = $item['formato'] === 'moneda'
                    ? number_format((float) $item['resultado'], 2, '.', '')
                    : (string) $item['resultado'];
                $periodoExportado = $item['codigo'] === 'INV' ? 'Actual' : date('d/m/Y', strtotime($desde)) . ' - ' . date('d/m/Y', strtotime($hasta));
                $filas[] = [$item['reporte'], $periodoExportado, $item['registros'], $resultado, $item['estado']];
            }
            $nombre = 'resumen_reportes_' . date('Ymd') . '.csv';
        } else {
            if (!isset(self::TIPOS_VALIDOS[$categoria]) || !in_array($tipo, self::TIPOS_VALIDOS[$categoria], true)) {
                http_response_code(400);
                echo 'Parámetros de exportación no válidos.';
                return;
            }

            switch ($tipo) {
                case 'ganancias_mes':
                    $resultado = $this->reporte->obtenerGanancias($desde, $hasta);
                    $filas[] = ['Fecha', 'Operación', 'Usuario', 'Ventas', 'Costos', 'Ganancias'];
                    foreach ($resultado['detalle'] as $r) {
                        $filas[] = [$r['fecha_venta'], 'Venta #' . $r['id_venta'], $r['nombre_completo'], $r['ventas'], $r['costos'], $r['ganancias']];
                    }
                    break;
                case 'productos_mas_vendidos':
                    $filas[] = ['Producto', 'Código', 'Unidades vendidas'];
                    foreach ($this->reporte->obtenerProductosMasVendidos($desde, $hasta) as $r) {
                        $filas[] = [$r['producto'], $r['codigo'], $r['unidades_vendidas']];
                    }
                    break;
                case 'historial_ventas':
                    $filas[] = ['Fecha', 'Operación', 'Usuario', 'Total'];
                    foreach ($this->reporte->obtenerHistorialVentas($desde, $hasta) as $r) {
                        $filas[] = [$r['fecha_venta'], 'Venta #' . $r['id_venta'], $r['usuario'], $r['total']];
                    }
                    break;
                case 'historial_compras':
                    $filas[] = ['Fecha', 'Operación', 'Proveedor', 'Total'];
                    foreach ($this->reporte->obtenerHistorialCompras($desde, $hasta) as $r) {
                        $filas[] = [$r['fecha_compra'], 'Compra #' . $r['id_compra'], $r['proveedor'], $r['total']];
                    }
                    break;
                case 'control_inventario':
                    $filas[] = ['Código', 'Producto', 'Existencia', 'Disponibilidad'];
                    foreach ($this->reporte->obtenerControlInventario() as $r) {
                        $filas[] = [$r['codigo'], $r['producto'], $r['existencia'], $r['disponibilidad']];
                    }
                    break;
                case 'baja_existencia':
                    $filas[] = ['Producto', 'Existencia actual', 'Mínimo establecido'];
                    foreach ($this->reporte->obtenerBajaExistencia() as $r) {
                        $filas[] = [$r['producto'], $r['existencia'], $r['stock_minimo']];
                    }
                    break;
            }
            $nombre = $tipo . '_' . date('Ymd') . '.csv';
        }


        if (ob_get_level() > 0) {
            ob_clean();
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombre . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $salida = fopen('php://output', 'w');
        // BOM para que Excel reconozca correctamente los acentos en UTF-8.
        fwrite($salida, "\xEF\xBB\xBF");

        foreach ($filas as $fila) {
            fputcsv($salida, $fila, ';');
        }
        
        fclose($salida);
    }

    private function obtenerFechas(): array
    {
        $hoy = new DateTimeImmutable('today');
        $periodo = $_GET['periodo'] ?? '';

        // Si se entra desde un detalle antiguo que solo envía fechas,
        // se conservan esas fechas como consulta personalizada.
        $desdeRecibido = $this->validarFecha($_GET['desde'] ?? '');
        $hastaRecibido = $this->validarFecha($_GET['hasta'] ?? '');

        switch ($periodo) {
            case 'mes_anterior':
                $inicio = $hoy->modify('first day of last month');
                $fin = $hoy->modify('last day of last month');
                break;

            case 'ultimos_3_meses':
                $inicio = $hoy->modify('first day of -2 months');
                $fin = $hoy;
                break;

            case 'anio_actual':
                $inicio = $hoy->setDate((int) $hoy->format('Y'), 1, 1);
                $fin = $hoy;
                break;

            case 'personalizado':
                $inicio = $desdeRecibido ? new DateTimeImmutable($desdeRecibido) : $hoy->modify('first day of this month');
                $fin = $hastaRecibido ? new DateTimeImmutable($hastaRecibido) : $hoy;
                break;

            case 'mes_actual':
                $inicio = $hoy->modify('first day of this month');
                $fin = $hoy;
                break;

            default:
                if ($desdeRecibido || $hastaRecibido) {
                    $inicio = $desdeRecibido ? new DateTimeImmutable($desdeRecibido) : $hoy->modify('first day of this month');
                    $fin = $hastaRecibido ? new DateTimeImmutable($hastaRecibido) : $hoy;
                } else {
                    $inicio = $hoy->modify('first day of this month');
                    $fin = $hoy;
                }
                break;
        }

        if ($inicio > $fin) {
            [$inicio, $fin] = [$fin, $inicio];
        }

        return [$inicio->format('Y-m-d'), $fin->format('Y-m-d')];
    }

    private function validarFecha(string $fecha): ?string
    {
        $objeto = DateTime::createFromFormat('!Y-m-d', $fecha);
        return $objeto && $objeto->format('Y-m-d') === $fecha ? $fecha : null;
    }

    private function obtenerTitulo(string $tipo): string
    {
        $titulos = [
            'ganancias_mes' => 'Ganancias del mes',
            'productos_mas_vendidos' => 'Productos más vendidos',
            'historial_ventas' => 'Historial de ventas',
            'historial_compras' => 'Historial de compras',
            'control_inventario' => 'Control de inventario',
            'baja_existencia' => 'Productos con baja existencia'
        ];
        return $titulos[$tipo] ?? 'Detalle del reporte';
    }
}
