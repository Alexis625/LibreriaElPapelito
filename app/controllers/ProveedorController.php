
<?php

require_once __DIR__ . '/../models/Proveedor.php';

class ProveedorController {

    private $conexion;
    private $proveedor;

    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->proveedor = new Proveedor($conexion);
    }

    // Mostrar listado de proveedores
    public function index() {

        $estadoFiltro = $_GET['estado'] ?? 'todos';
        $estado = null;

        if ($estadoFiltro === 'activos') {
            $estado = 1;
        } elseif ($estadoFiltro === 'inactivos') {
            $estado = 0;
        } else {
            $estadoFiltro = 'todos';
        }

        $busqueda = trim($_GET['busqueda'] ?? '');

        $proveedores = $this->proveedor->listarProveedores(
            $estado,
            $busqueda
        );

        require_once __DIR__ . '/../views/admin/proveedores.php';
    }


    // Registrar proveedor
    public function crear() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        $datos = [
            'nombre_empresa' => trim($_POST['nombre_empresa'] ?? ''),
            'nombre_contacto' => trim($_POST['nombre_contacto'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'correo' => trim($_POST['correo'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? '')
        ];

        if ($datos['nombre_empresa'] === '') {
            return false;
        }

        return $this->proveedor->registrarProveedor($datos);
    }

    // Consultar proveedor para editar
    public function editar() {

        $idProveedor = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$idProveedor || $idProveedor <= 0) {
            return false;
        }

        return $this->proveedor->obtenerProveedor($idProveedor);
    }

    // Actualizar proveedor
    public function actualizar() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        $idProveedor = filter_input(INPUT_POST, 'id_proveedor', FILTER_VALIDATE_INT);

        $datos = [
            'nombre_empresa' => trim($_POST['nombre_empresa'] ?? ''),
            'nombre_contacto' => trim($_POST['nombre_contacto'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'correo' => trim($_POST['correo'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? '')
        ];

        if (!$idProveedor || $idProveedor <= 0 || $datos['nombre_empresa'] === '') {
            return false;
        }

        if (!$this->proveedor->obtenerProveedor($idProveedor)) {
            return false;
        }

        return $this->proveedor->actualizarProveedor($idProveedor, $datos);
    }

    // Activar o desactivar proveedor
    public function cambiarEstado() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        $idProveedor = filter_input(INPUT_POST, 'id_proveedor', FILTER_VALIDATE_INT);
        $estado = filter_input(INPUT_POST, 'estado', FILTER_VALIDATE_INT);

        if (
            !$idProveedor || $idProveedor <= 0 ||
            !in_array($estado, [0, 1], true)
        ) {
            return false;
        }

        if (!$this->proveedor->obtenerProveedor($idProveedor)) {
            return false;
        }

        return $this->proveedor->cambiarEstado($idProveedor, $estado);
    }

    
    // Eliminar proveedor
    public function eliminar() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        $idProveedor = filter_input(
            INPUT_POST,
            'id_proveedor',
            FILTER_VALIDATE_INT
        );

        if (!$idProveedor || $idProveedor <= 0) {
            return false;
        }

        $proveedorExistente = $this->proveedor->obtenerProveedor($idProveedor);

        if (!$proveedorExistente) {
            return false;
        }

        // Solo permitir eliminar proveedores inactivos
        if ((int) $proveedorExistente['activo'] !== 0) {
            return false;
        }

        return $this->proveedor->eliminarProveedor($idProveedor);
    }

}