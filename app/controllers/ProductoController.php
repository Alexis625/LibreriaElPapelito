<?php

require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/CategoriaController.php';

class ProductoController
{

    private $conexion;
    private $producto;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->producto = new Producto($conexion);
    }

    // Mostrar listado de productos y categorías


    public function index()
    {

        // Obtener el filtro de estado
        $estadoFiltro = $_GET['estado'] ?? 'todos';

        // Determinar el estado que se consultará
        $estado = null;

        if ($estadoFiltro === 'activos') {
            $estado = 1;
        } elseif ($estadoFiltro === 'inactivos') {
            $estado = 0;
        } else {
            $estadoFiltro = 'todos';
        }

        // Obtener el texto de búsqueda
        $busqueda = trim($_GET['busqueda'] ?? '');

        // Obtener productos según los filtros
        $productos = $this->producto->listarProductos(
            $estado,
            $busqueda
        );

        // Obtener categorías
        $categoriaController = new CategoriaController($this->conexion);

        $categorias = $categoriaController->index();

        require_once __DIR__ . '/../views/admin/productos.php';
    }

    // Registrar un nuevo producto
    public function crear()
    {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        $datos = [
            'codigo' => trim($_POST['codigo'] ?? ''),
            'nombre' => trim($_POST['nombre'] ?? ''),
            'id_categoria' => $_POST['id_categoria'] ?? '',
            'precio_compra' => $_POST['precio_compra'] ?? '',
            'precio_venta' => $_POST['precio_venta'] ?? '',
            'existencia' => $_POST['existencia'] ?? '',
            'stock_minimo' => $_POST['stock_minimo'] ?? '',
            'descripcion' => trim($_POST['descripcion'] ?? '')
        ];

        // Validar campos obligatorios
        foreach ([
            'codigo',
            'nombre',
            'id_categoria',
            'precio_compra',
            'precio_venta',
            'existencia',
            'stock_minimo'
        ] as $campo) {

            if ($datos[$campo] === '') {
                return false;
            }
        }

        // Validar números
        if (
            !filter_var($datos['id_categoria'], FILTER_VALIDATE_INT) ||
            !is_numeric($datos['precio_compra']) ||
            !is_numeric($datos['precio_venta']) ||
            !is_numeric($datos['existencia']) ||
            !is_numeric($datos['stock_minimo'])
        ) {
            return false;
        }

        // Validar que los valores no sean negativos
        if (
            $datos['precio_compra'] < 0 ||
            $datos['precio_venta'] < 0 ||
            $datos['existencia'] < 0 ||
            $datos['stock_minimo'] < 0
        ) {
            return false;
        }

        return $this->producto->registrarProducto($datos);
    }


    // Obtener los datos de un producto para editarlo
    public function editar()
    {

        $idProducto = filter_input(
            INPUT_GET,
            'id',
            FILTER_VALIDATE_INT
        );

        if (!$idProducto || $idProducto <= 0) {
            return false;
        }

        return $this->producto->obtenerProducto($idProducto);
    }


    // Actualizar un producto existente
    public function actualizar()
    {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        $idProducto = filter_input(
            INPUT_POST,
            'id_producto',
            FILTER_VALIDATE_INT
        );

        if (!$idProducto || $idProducto <= 0) {
            return false;
        }

        // Comprobar que el producto exista
        $productoExistente = $this->producto->obtenerProducto($idProducto);

        if (!$productoExistente) {
            return false;
        }

        $datos = [
            'codigo' => trim($_POST['codigo'] ?? ''),
            'nombre' => trim($_POST['nombre'] ?? ''),
            'id_categoria' => $_POST['id_categoria'] ?? '',
            'precio_compra' => $_POST['precio_compra'] ?? '',
            'precio_venta' => $_POST['precio_venta'] ?? '',
            'existencia' => $_POST['existencia'] ?? '',
            'stock_minimo' => $_POST['stock_minimo'] ?? '',
            'descripcion' => trim($_POST['descripcion'] ?? '')
        ];

        // Validar campos obligatorios
        foreach ([
            'codigo',
            'nombre',
            'id_categoria',
            'precio_compra',
            'precio_venta',
            'existencia',
            'stock_minimo'
        ] as $campo) {

            if ($datos[$campo] === '') {
                return false;
            }
        }

        // Validar valores numéricos
        if (
            !filter_var($datos['id_categoria'], FILTER_VALIDATE_INT) ||
            !is_numeric($datos['precio_compra']) ||
            !is_numeric($datos['precio_venta']) ||
            !is_numeric($datos['existencia']) ||
            !is_numeric($datos['stock_minimo'])
        ) {
            return false;
        }

        // Evitar valores negativos
        if (
            $datos['precio_compra'] < 0 ||
            $datos['precio_venta'] < 0 ||
            $datos['existencia'] < 0 ||
            $datos['stock_minimo'] < 0
        ) {
            return false;
        }

        return $this->producto->actualizarProducto(
            $idProducto,
            $datos
        );
    }


    // Activar o desactivar un producto
    public function cambiarEstado()
    {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        $idProducto = filter_input(
            INPUT_POST,
            'id_producto',
            FILTER_VALIDATE_INT
        );

        $estado = filter_input(
            INPUT_POST,
            'estado',
            FILTER_VALIDATE_INT
        );

        if (
            !$idProducto ||
            $idProducto <= 0 ||
            !in_array($estado, [0, 1], true)
        ) {
            return false;
        }

        // Comprobar que el producto exista
        $productoExistente = $this->producto->obtenerProducto($idProducto);

        if (!$productoExistente) {
            return false;
        }

        return $this->producto->cambiarEstado(
            $idProducto,
            $estado
        );
    }


    // Realizar un ajuste manual de inventario
    public function ajustarInventario() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        // Obtener identificadores
        $idProducto = filter_input(
            INPUT_POST,
            'id_producto',
            FILTER_VALIDATE_INT
        );

        $cantidad = filter_input(
            INPUT_POST,
            'cantidad',
            FILTER_VALIDATE_INT
        );

        $tipo = $_POST['tipo'] ?? '';

        $observaciones = trim(
            $_POST['observaciones'] ?? ''
        );

        // Obtener el usuario de la sesión
        $idUsuario = $_SESSION['usuario']['id_usuario'] ?? null;

        // Validar datos
        if (
            !$idProducto ||
            $idProducto <= 0 ||
            !$idUsuario ||
            $cantidad === false ||
            $cantidad === null ||
            $cantidad <= 0 ||
            !in_array($tipo, ['AJUSTE_ENTRADA', 'AJUSTE_SALIDA'], true)
        ) {
            return false;
        }

        // Comprobar que el producto exista
        $productoExistente = $this->producto->obtenerProducto($idProducto);

        if (!$productoExistente) {
            return false;
        }

        // Ejecutar ajuste de inventario
        return $this->producto->ajustarExistencia(
            $idProducto,
            $idUsuario,
            $cantidad,
            $tipo,
            $observaciones
        );
    }


}
