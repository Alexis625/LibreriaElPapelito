
<?php

require_once __DIR__ . '/../models/Venta.php';

class VentaController
{
    private $conexion;
    private $venta;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->venta = new Venta($conexion);
    }

    // ==========================================
    // MOSTRAR HISTORIAL DE VENTAS
    // ==========================================

    public function index()
    {
        $busqueda = trim($_GET['busqueda'] ?? '');

        $estadoFiltro = $_GET['estado'] ?? 'todos';

        $estado = '';

        if (in_array($estadoFiltro, ['COMPLETADA', 'ANULADA'], true)) {
            $estado = $estadoFiltro;
        } else {
            $estadoFiltro = 'todos';
        }

        $fechaInicio = trim($_GET['fecha_inicio'] ?? '');
        $fechaFin = trim($_GET['fecha_fin'] ?? '');

        // Validar fechas
        if (
            $fechaInicio !== '' &&
            !$this->validarFecha($fechaInicio)
        ) {
            $fechaInicio = '';
        }

        if (
            $fechaFin !== '' &&
            !$this->validarFecha($fechaFin)
        ) {
            $fechaFin = '';
        }

        // Evitar rangos de fechas invertidos
        if (
            $fechaInicio !== '' &&
            $fechaFin !== '' &&
            $fechaInicio > $fechaFin
        ) {
            $fechaInicio = '';
            $fechaFin = '';
        }

        // Paginación
        $limite = 10;

        $pagina = filter_input(
            INPUT_GET,
            'pagina',
            FILTER_VALIDATE_INT
        );

        $pagina = ($pagina && $pagina > 0) ? $pagina : 1;

        $totalVentas = $this->venta->contarVentas(
            $busqueda,
            $estado,
            $fechaInicio,
            $fechaFin
        );

        $totalPaginas = max(
            1,
            (int)ceil($totalVentas / $limite)
        );

        $pagina = min($pagina, $totalPaginas);

        $offset = ($pagina - 1) * $limite;

        $ventas = $this->venta->listarVentas(
            $busqueda,
            $estado,
            $fechaInicio,
            $fechaFin,
            $limite,
            $offset
        );

        // Datos necesarios para registrar ventas
        $clientes = $this->venta->listarClientes();
        $productos = $this->venta->listarProductos();

        require_once __DIR__ . '/../views/admin/ventas.php';
    }

    // ==========================================
    // REGISTRAR VENTA
    // ==========================================

    public function crear()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return [
                'exito' => false,
                'mensaje' => 'Método no permitido.'
            ];
        }

        // Validar sesión
        if (!isset($_SESSION['usuario']['id_usuario'])) {
            return [
                'exito' => false,
                'mensaje' => 'No se encontró el usuario de la sesión.'
            ];
        }

        // Validar token CSRF
        if (!$this->validarTokenCSRF()) {
            return [
                'exito' => false,
                'mensaje' => 'La sesión de seguridad no es válida.'
            ];
        }

        $idCliente = $_POST['id_cliente'] ?? null;

        $observaciones = trim(
            $_POST['observaciones'] ?? ''
        );

        $productos = $_POST['productos'] ?? [];
        $cantidades = $_POST['cantidades'] ?? [];

        if (strlen($observaciones) > 500) {
            return [
                'exito' => false,
                'mensaje' => 'Las observaciones no pueden superar 500 caracteres.'
            ];
        }

        return $this->venta->registrarVenta(
            $idCliente,
            $_SESSION['usuario']['id_usuario'],
            $observaciones,
            $productos,
            $cantidades
        );
    }

    // ==========================================
    // CONSULTAR DETALLE DE VENTA (AJAX)
    // ==========================================

    public function obtenerDetalleAjax()
    {
        header('Content-Type: application/json; charset=utf-8');

        $idVenta = filter_input(
            INPUT_GET,
            'id',
            FILTER_VALIDATE_INT
        );

        if (!$idVenta || $idVenta <= 0) {
            echo json_encode([
                'exito' => false,
                'mensaje' => 'El identificador de venta no es válido.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $venta = $this->venta->obtenerVenta($idVenta);

        if (!$venta) {
            echo json_encode([
                'exito' => false,
                'mensaje' => 'No se encontró la venta.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $detalles = $this->venta->obtenerDetallesVenta($idVenta);

        echo json_encode([
            'exito' => true,
            'venta' => $venta,
            'detalles' => $detalles
        ], JSON_UNESCAPED_UNICODE);
    }

    // ==========================================
    // ANULAR VENTA
    // ==========================================

    public function anular()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return [
                'exito' => false,
                'mensaje' => 'Método no permitido.'
            ];
        }

        if (!isset($_SESSION['usuario']['id_usuario'])) {
            return [
                'exito' => false,
                'mensaje' => 'No se encontró el usuario de la sesión.'
            ];
        }

        if (!$this->validarTokenCSRF()) {
            return [
                'exito' => false,
                'mensaje' => 'La sesión de seguridad no es válida.'
            ];
        }

        $idVenta = filter_input(
            INPUT_POST,
            'id_venta',
            FILTER_VALIDATE_INT
        );

        if (!$idVenta || $idVenta <= 0) {
            return [
                'exito' => false,
                'mensaje' => 'El identificador de venta no es válido.'
            ];
        }

        return $this->venta->anularVenta(
            $idVenta,
            $_SESSION['usuario']['id_usuario']
        );
    }

    // ==========================================
    // VALIDAR TOKEN CSRF
    // ==========================================

    private function validarTokenCSRF()
    {
        return (
            isset($_SESSION['csrf_token']) &&
            isset($_POST['csrf_token']) &&
            is_string($_POST['csrf_token']) &&
            hash_equals(
                $_SESSION['csrf_token'],
                $_POST['csrf_token']
            )
        );
    }

    // ==========================================
    // VALIDAR FORMATO DE FECHA
    // ==========================================

    private function validarFecha($fecha)
    {
        $formato = DateTime::createFromFormat('Y-m-d', $fecha);

        return (
            $formato !== false &&
            $formato->format('Y-m-d') === $fecha
        );
    }
}
