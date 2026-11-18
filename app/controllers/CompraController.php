
<?php

require_once __DIR__ . '/../models/Compra.php';

class CompraController {

    private $conexion;
    private $compra;

    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->compra = new Compra($conexion);
    }

    // Mostrar listado de compras
    
// Mostrar listado de compras

// Mostrar listado de compras con paginación
public function index() {

    // Obtener criterios de búsqueda
    $busqueda = trim($_GET['busqueda'] ?? '');
    $estado = $_GET['estado'] ?? '';
    $idProveedor = $_GET['id_proveedor'] ?? '';
    $fechaInicio = $_GET['fecha_inicio'] ?? '';
    $fechaFin = $_GET['fecha_fin'] ?? '';

    // Configurar paginación
    $limite = 10;

    $pagina = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT);

    if (!$pagina || $pagina < 1) {
        $pagina = 1;
    }

    // Contar compras que coinciden con los filtros
    $totalCompras = $this->compra->contarCompras(
        $busqueda,
        $estado,
        $idProveedor,
        $fechaInicio,
        $fechaFin
    );

    // Calcular páginas disponibles
    $totalPaginas = max(1, (int) ceil($totalCompras / $limite));

    // Evitar páginas fuera del rango
    if ($pagina > $totalPaginas) {
        $pagina = $totalPaginas;
    }

    // Calcular desplazamiento
    $offset = ($pagina - 1) * $limite;

    // Consultar compras de la página actual
    $compras = $this->compra->listarCompras(
        $busqueda,
        $estado,
        $idProveedor,
        $fechaInicio,
        $fechaFin,
        $limite,
        $offset
    );

    // Obtener datos para los formularios
    $proveedores = $this->compra->listarProveedores();
    $productos = $this->compra->listarProductos();

    // Cargar vista
    require_once __DIR__ . '/../views/admin/compras.php';
}


    // Consultar los detalles de una compra
public function detalle() {
    $idCompra = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if (!$idCompra || $idCompra <= 0) {
        header('Location: /el_papelito/public/compras');
        exit;
    }

    $compra = $this->compra->obtenerCompra($idCompra);

    if (!$compra) {
        header('Location: /el_papelito/public/compras');
        exit;
    }

    $detalles = $this->compra->obtenerDetallesCompra($idCompra);

    require_once __DIR__ . '/../views/admin/detalle_compra.php';
}

// Obtener detalles de una compra mediante AJAX
public function obtenerDetalleAjax() {
    header('Content-Type: application/json; charset=utf-8');

    $idCompra = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if (!$idCompra || $idCompra <= 0) {
        echo json_encode([
            'exito' => false,
            'mensaje' => 'El identificador de la compra no es válido.'
        ]);
        exit;
    }

    $compra = $this->compra->obtenerCompra($idCompra);

    if (!$compra) {
        echo json_encode([
            'exito' => false,
            'mensaje' => 'No se encontró la compra.'
        ]);
        exit;
    }

    $detalles = $this->compra->obtenerDetallesCompra($idCompra);

    echo json_encode([
        'exito' => true,
        'compra' => $compra,
        'detalles' => $detalles
    ]);

    exit;
}


    // Procesar el registro de una compra
    public function crear() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /el_papelito/public/compras');
            exit;
        }
        // Validar token de seguridad CSRF
        $tokenFormulario = $_POST['csrf_token'] ?? '';
        $tokenSesion = $_SESSION['csrf_token'] ?? '';

        if (
            empty($tokenFormulario) ||
            empty($tokenSesion) ||
            !hash_equals($tokenSesion, $tokenFormulario)
        ) {
            header('Location: /el_papelito/public/compras?registro=error');
            exit;
        }

        // Invalidar el token utilizado
        unset($_SESSION['csrf_token']);


        $idProveedor = $_POST['id_proveedor'] ?? null;

        $numeroDocumento = trim($_POST['numero_documento'] ?? '');

        $observaciones = trim($_POST['observaciones'] ?? '');

        $productos = $_POST['productos'] ?? [];

        $cantidades = $_POST['cantidades'] ?? [];

        $costos = $_POST['costos'] ?? [];

        $idUsuario = $_SESSION['usuario']['id_usuario'] ?? null;

        // Verificar que exista un usuario autenticado
        if (!$idUsuario) {
            header('Location: /el_papelito/public/login');
            exit;
        }

        // Registrar compra mediante el modelo
        $resultado = $this->compra->registrarCompra(
            $idProveedor,
            $idUsuario,
            $numeroDocumento,
            $observaciones,
            $productos,
            $cantidades,
            $costos
        );

        // Redireccionar según el resultado
        if ($resultado['exito']) {

            header('Location: /el_papelito/public/compras?registro=exitoso');
            exit;

        } else {

            header('Location: /el_papelito/public/compras?registro=error');
            exit;

        }

    }

    // Procesar la anulación de una compra
public function anular() {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /el_papelito/public/compras');
        exit;
    }

    // Validar token CSRF
    $tokenFormulario = $_POST['csrf_token'] ?? '';
    $tokenSesion = $_SESSION['csrf_token'] ?? '';

    if (
        empty($tokenFormulario) ||
        empty($tokenSesion) ||
        !hash_equals($tokenSesion, $tokenFormulario)
    ) {
        header('Location: /el_papelito/public/compras?anulacion=error');
        exit;
    }

    unset($_SESSION['csrf_token']);

    $idCompra = $_POST['id_compra'] ?? null;
    $idUsuario = $_SESSION['usuario']['id_usuario'] ?? null;

    if (!$idUsuario) {
        header('Location: /el_papelito/public/login');
        exit;
    }

    $resultado = $this->compra->anularCompra(
        $idCompra,
        $idUsuario
    );

    if ($resultado['exito']) {
        header('Location: /el_papelito/public/compras?anulacion=exitosa');
        exit;
    }

    $_SESSION['error_anulacion'] = $resultado['mensaje'];

    header('Location: /el_papelito/public/compras?anulacion=error');
    exit;
}
}
