
<?php

require_once __DIR__ . '/../models/Reservacion.php';

class ReservacionController
{
    private $modelo;

    public function __construct($conexion)
    {
        $this->modelo = new Reservacion($conexion);
    }

    // =====================================
    // MOSTRAR RESERVACIONES
    // =====================================

    public function index()
    {
        if (!isset($_SESSION['usuario'])) {
            header('Location: /el_papelito/public/login');
            exit;
        }

        $busqueda = trim($_GET['busqueda'] ?? '');
        $estado = $_GET['estado'] ?? '';
        $fechaInicio = $_GET['fecha_inicio'] ?? '';
        $fechaFin = $_GET['fecha_fin'] ?? '';

        $pagina = filter_var(
            $_GET['pagina'] ?? 1,
            FILTER_VALIDATE_INT
        );

        $pagina = max(1, $pagina ?: 1);

        $porPagina = 10;
        $offset = ($pagina - 1) * $porPagina;

        $totalReservaciones = $this->modelo->contarReservaciones(
            $busqueda,
            $estado,
            $fechaInicio,
            $fechaFin
        );

        $totalPaginas = max(
            1,
            (int) ceil($totalReservaciones / $porPagina)
        );

        if ($pagina > $totalPaginas) {
            $pagina = $totalPaginas;
            $offset = ($pagina - 1) * $porPagina;
        }

        $reservaciones = $this->modelo->listarReservaciones(
            $busqueda,
            $estado,
            $fechaInicio,
            $fechaFin,
            $porPagina,
            $offset
        );

        $clientes = $this->modelo->listarClientes();
        $productos = $this->modelo->listarProductos();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        require_once __DIR__ . '/../views/admin/reservaciones.php';
    }

    // =====================================
    // REGISTRAR RESERVACION DESDE ADMIN
    // =====================================

    public function crear()
    {
        if (!isset($_SESSION['usuario'])) {
            header('Location: /el_papelito/public/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /el_papelito/public/reservaciones');
            exit;
        }

        // Validar token CSRF
        if (
            empty($_POST['csrf_token']) ||
            empty($_SESSION['csrf_token']) ||
            !hash_equals(
                $_SESSION['csrf_token'],
                $_POST['csrf_token']
            )
        ) {
            $_SESSION['error_reservacion'] =
                'La solicitud no es válida. Recarga la página e inténtalo nuevamente.';

            header('Location: /el_papelito/public/reservaciones?registro=error');
            exit;
        }

        $usuarioSesion = $_SESSION['usuario'];

        $idUsuario = $usuarioSesion['id_usuario'] ?? null;

        $registradoPor = trim(
            $usuarioSesion['nombre_completo']
            ?? $usuarioSesion['nombre']
            ?? $usuarioSesion['nombre_usuario']
            ?? ''
        );

        if ($registradoPor === '' || !$idUsuario) {
            $_SESSION['error_reservacion'] =
                'No fue posible identificar al usuario que registra la reservación. Cierra sesión e inicia nuevamente.';

            header('Location: /el_papelito/public/reservaciones?registro=error');
            exit;
        }

        $idCliente = $_POST['id_cliente'] ?? null;
        $fechaLimite = trim($_POST['fecha_limite_retiro'] ?? '');
        $observaciones = trim($_POST['observaciones'] ?? '');

        $productos = $_POST['productos'] ?? [];
        $cantidades = $_POST['cantidades'] ?? [];

        // Registrar reservación con el ID del usuario autenticado.
        $resultado = $this->modelo->registrarReservacion(
            $registradoPor,
            $idCliente,
            $fechaLimite,
            $observaciones,
            $productos,
            $cantidades,
            $idUsuario
        );

        if (!empty($resultado['exito'])) {
            header(
                'Location: /el_papelito/public/reservaciones?registro=exitoso'
            );
            exit;
        }

        $_SESSION['error_reservacion'] =
            $resultado['mensaje'] ?? 'No fue posible registrar la reservación.';

        header(
            'Location: /el_papelito/public/reservaciones?registro=error'
        );
        exit;
    }

    // =====================================
    // CONSULTAR DETALLE DE RESERVACION
    // =====================================

    public function obtenerDetalleAjax()
    {
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);

            echo json_encode([
                'exito' => false,
                'mensaje' => 'No tienes autorización para realizar esta consulta.'
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }

        header('Content-Type: application/json; charset=utf-8');

        $idReserva = $_GET['id'] ?? null;

        if (!filter_var($idReserva, FILTER_VALIDATE_INT)) {
            http_response_code(400);

            echo json_encode([
                'exito' => false,
                'mensaje' => 'El identificador de la reservación no es válido.'
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }

        $reservacion = $this->modelo->obtenerReservacion($idReserva);

        if (!$reservacion) {
            http_response_code(404);

            echo json_encode([
                'exito' => false,
                'mensaje' => 'No se encontró la reservación.'
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }

        $detalles = $this->modelo->obtenerDetallesReservacion($idReserva);

        echo json_encode([
            'exito' => true,
            'reservacion' => $reservacion,
            'detalles' => $detalles
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    // =====================================
    // ACTUALIZAR ESTADO
    // =====================================

    public function actualizarEstado()
    {
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        // Validar token CSRF
        if (
            empty($_POST['csrf_token']) ||
            empty($_SESSION['csrf_token']) ||
            !hash_equals(
                $_SESSION['csrf_token'],
                $_POST['csrf_token']
            )
        ) {
            $_SESSION['error_estado_reservacion'] =
                'La solicitud no es válida. Recarga la página e inténtalo nuevamente.';

            header(
                'Location: /el_papelito/public/reservaciones?estado=error'
            );

            exit;
        }

        $idReserva = $_POST['id_reserva'] ?? null;
        $estado = $_POST['estado'] ?? '';

        $idUsuario = $_SESSION['usuario']['id_usuario'] ?? null;

        if (!$idUsuario) {
            $_SESSION['error_estado_reservacion'] =
                'No fue posible identificar al usuario autenticado. Inicia sesión nuevamente.';

            header(
                'Location: /el_papelito/public/reservaciones?estado=error'
            );

            exit;
        }

        // Actualizar estado, inventario y venta según corresponda.
        $resultado = $this->modelo->actualizarEstado(
            $idReserva,
            $estado,
            $idUsuario
        );

        if (!empty($resultado['exito'])) {
            header(
                'Location: /el_papelito/public/reservaciones?estado=exitoso'
            );
            exit;
        }

        $_SESSION['error_estado_reservacion'] =
            $resultado['mensaje'] ?? 'No fue posible actualizar el estado.';

        header(
            'Location: /el_papelito/public/reservaciones?estado=error'
        );

        exit;
    }
}
