
<?php

require_once __DIR__ . '/../models/MovimientoInventario.php';

class MovimientoInventarioController
{
    private $modelo;

    public function __construct($conexion)
    {
        $this->modelo = new MovimientoInventario($conexion);
    }

    public function index()
    {
        $busqueda = trim($_GET['busqueda'] ?? '');
        $tipo = $_GET['tipo'] ?? '';
        $fechaInicio = $_GET['fecha_inicio'] ?? '';
        $fechaFin = $_GET['fecha_fin'] ?? '';

        if (
            $fechaInicio !== '' &&
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio)
        ) {
            $fechaInicio = '';
        }

        if (
            $fechaFin !== '' &&
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)
        ) {
            $fechaFin = '';
        }

        if (
            $fechaInicio !== '' &&
            $fechaFin !== '' &&
            $fechaInicio > $fechaFin
        ) {
            $fechaInicio = '';
            $fechaFin = '';
        }

        $porPagina = 10;

        $pagina = filter_var(
            $_GET['pagina'] ?? 1,
            FILTER_VALIDATE_INT
        );

        $pagina = max(1, $pagina ?: 1);

        // Obtener resumen general
        $resumen = $this->modelo->obtenerResumen();

        // Contar movimientos según los filtros
        $totalMovimientos = $this->modelo->contarMovimientos(
            $busqueda,
            $tipo,
            $fechaInicio,
            $fechaFin
        );

        // Calcular paginación
        $totalPaginas = max(
            1,
            (int) ceil($totalMovimientos / $porPagina)
        );

        $pagina = min($pagina, $totalPaginas);
        $offset = ($pagina - 1) * $porPagina;

        // Obtener movimientos
        $movimientos = $this->modelo->listarMovimientos(
            $busqueda,
            $tipo,
            $fechaInicio,
            $fechaFin,
            $porPagina,
            $offset
        );

        // Cargar vista
        require __DIR__ . '/../views/admin/movimientos.php';
    }
}
