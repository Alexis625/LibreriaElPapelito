
<?php

require_once __DIR__ . '/../models/Cliente.php';

class ClienteController {

    private $conexion;
    private $cliente;

    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->cliente = new Cliente($conexion);
    }

    // Mostrar listado de clientes
    public function index() {

        $estadoFiltro = $_GET['estado'] ?? 'todos';
        $estado = '';

        if ($estadoFiltro === 'activos') {
            $estado = '1';
        } elseif ($estadoFiltro === 'inactivos') {
            $estado = '0';
        } else {
            $estadoFiltro = 'todos';
        }

        $busqueda = trim($_GET['busqueda'] ?? '');

        // Configuración de paginación
        $porPagina = 10;

        $paginaActual = filter_input(
            INPUT_GET,
            'pagina',
            FILTER_VALIDATE_INT
        );

        $paginaActual = max(1, (int)($paginaActual ?: 1));

        $totalClientes = $this->cliente->contarClientes(
            $busqueda,
            $estado
        );

        $totalPaginas = max(
            1,
            (int)ceil($totalClientes / $porPagina)
        );

        $paginaActual = min($paginaActual, $totalPaginas);

        $offset = ($paginaActual - 1) * $porPagina;

        $clientes = $this->cliente->listarClientes(
            $busqueda,
            $estado,
            $porPagina,
            $offset
        );

        require_once __DIR__ . '/../views/admin/clientes.php';
    }

    // Registrar cliente
    public function crear() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        $datos = [
            'nombre_completo' => trim($_POST['nombre_completo'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'correo' => trim($_POST['correo'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? '')
        ];

        if ($datos['nombre_completo'] === '') {
            return false;
        }

        return $this->cliente->registrarCliente(
            $datos['nombre_completo'],
            $datos['telefono'],
            $datos['correo'],
            $datos['direccion']
        );
    }

    // Consultar cliente para editar
    public function editar() {

        $idCliente = filter_input(
            INPUT_GET,
            'id',
            FILTER_VALIDATE_INT
        );

        if (!$idCliente || $idCliente <= 0) {
            return false;
        }

        return $this->cliente->obtenerCliente($idCliente);
    }

    // Actualizar cliente
    public function actualizar() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        $idCliente = filter_input(
            INPUT_POST,
            'id_cliente',
            FILTER_VALIDATE_INT
        );

        $datos = [
            'nombre_completo' => trim($_POST['nombre_completo'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'correo' => trim($_POST['correo'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? '')
        ];

        if (
            !$idCliente ||
            $idCliente <= 0 ||
            $datos['nombre_completo'] === ''
        ) {
            return false;
        }

        if (!$this->cliente->obtenerCliente($idCliente)) {
            return false;
        }

        return $this->cliente->actualizarCliente(
            $idCliente,
            $datos['nombre_completo'],
            $datos['telefono'],
            $datos['correo'],
            $datos['direccion']
        );
    }

    // Activar o desactivar cliente
    public function cambiarEstado() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        $idCliente = filter_input(
            INPUT_POST,
            'id_cliente',
            FILTER_VALIDATE_INT
        );

        $estado = filter_input(
            INPUT_POST,
            'estado',
            FILTER_VALIDATE_INT
        );

        if (
            !$idCliente ||
            $idCliente <= 0 ||
            !in_array($estado, [0, 1], true)
        ) {
            return false;
        }

        if (!$this->cliente->obtenerCliente($idCliente)) {
            return false;
        }

        return $this->cliente->cambiarEstadoCliente(
            $idCliente,
            $estado
        );
    }

}
