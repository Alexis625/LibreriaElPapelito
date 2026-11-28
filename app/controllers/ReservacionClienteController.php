
<?php

require_once __DIR__ . '/../models/Reservacion.php';

class ReservacionClienteController
{
    private $modelo;

    public function __construct($conexion)
    {
        $this->modelo = new Reservacion($conexion);
    }

    private function verificarCliente()
    {
        if (!isset($_SESSION['cliente']['id_cliente'])) {
            header('Location: /el_papelito/public/cliente/login');
            exit;
        }
    }

    private function redirigirCarrito()
    {
        header('Location: /el_papelito/public/cliente/reservas');
        exit;
    }

    public function agregar()
    {
        $this->verificarCliente();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirigirCarrito();
        }

        $id = (int)($_POST['id_producto'] ?? 0);
        $cantidad = filter_var(
            $_POST['cantidad'] ?? 1,
            FILTER_VALIDATE_INT
        );

        if ($id <= 0 || $cantidad === false || $cantidad <= 0) {
            $_SESSION['mensaje_cliente'] = 'Producto o cantidad no válidos.';
            $this->redirigirCarrito();
        }

        $productos = $this->modelo->listarProductos();
        $productoEncontrado = null;

        foreach ($productos as $producto) {
            if ((int)$producto['id_producto'] === $id) {
                $productoEncontrado = $producto;
                break;
            }
        }

        if (!$productoEncontrado) {
            $_SESSION['mensaje_cliente'] = 'El producto no está disponible.';
            $this->redirigirCarrito();
        }

        $carrito = $_SESSION['carrito_cliente'] ?? [];
        $cantidadNueva = ($carrito[$id] ?? 0) + $cantidad;

        if ($cantidadNueva > (int)$productoEncontrado['existencia']) {
            $_SESSION['mensaje_cliente'] =
                'No hay suficientes existencias. Disponibles: ' .
                $productoEncontrado['existencia'];

            $this->redirigirCarrito();
        }

        $carrito[$id] = $cantidadNueva;
        $_SESSION['carrito_cliente'] = $carrito;

        $_SESSION['mensaje_cliente'] = 'Producto agregado al carrito.';
        $this->redirigirCarrito();
    }

    public function verCarrito()
    {
        $this->verificarCliente();

        $productosBD = $this->modelo->listarProductos();
        $carrito = $_SESSION['carrito_cliente'] ?? [];

        $items = [];
        $total = 0;

        foreach ($carrito as $id => $cantidad) {
            foreach ($productosBD as $producto) {
                if ((int)$producto['id_producto'] === (int)$id) {
                    $subtotal = (float)$producto['precio_venta'] * (int)$cantidad;

                    $producto['cantidad'] = (int)$cantidad;
                    $producto['subtotal'] = $subtotal;

                    $items[] = $producto;
                    $total += $subtotal;
                    break;
                }
            }
        }

        $mensaje = $_SESSION['mensaje_cliente'] ?? '';
        unset($_SESSION['mensaje_cliente']);

        require __DIR__ . '/../views/clientes/reservas.php';
    }


public function actualizarCarrito()
{
    $this->verificarCliente();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->redirigirCarrito();
    }

    $productosBD = $this->modelo->listarProductos();
    $cantidades = $_POST['cantidades'] ?? [];

    $nuevoCarrito = [];
    $mensaje = '';

    foreach ($cantidades as $idProducto => $cantidad) {

        $idProducto = (int)$idProducto;
        $cantidad = filter_var($cantidad, FILTER_VALIDATE_INT);

        if ($idProducto <= 0 || $cantidad === false || $cantidad < 0) {
            continue;
        }

        // Cantidad cero significa eliminar el producto.
        if ($cantidad === 0) {
            continue;
        }

        $productoEncontrado = null;

        foreach ($productosBD as $producto) {
            if ((int)$producto['id_producto'] === $idProducto) {
                $productoEncontrado = $producto;
                break;
            }
        }

        if (!$productoEncontrado) {
            $mensaje = 'Uno de los productos ya no está disponible.';
            continue;
        }

        $existencia = (int)$productoEncontrado['existencia'];

        if ($cantidad > $existencia) {
            $cantidad = $existencia;
            $mensaje = 'Se ajustaron las cantidades según las existencias disponibles.';
        }

        if ($cantidad > 0) {
            $nuevoCarrito[$idProducto] = $cantidad;
        }
    }

    $_SESSION['carrito_cliente'] = $nuevoCarrito;

    $_SESSION['mensaje_cliente'] = $mensaje !== ''
        ? $mensaje
        : 'Carrito actualizado correctamente.';

    $this->redirigirCarrito();
}


    public function confirmar()
    {
        $this->verificarCliente();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirigirCarrito();
        }

        $carrito = $_SESSION['carrito_cliente'] ?? [];

        if (empty($carrito)) {
            $_SESSION['mensaje_cliente'] = 'Tu carrito está vacío.';
            $this->redirigirCarrito();
        }

        $cliente = $_SESSION['cliente'];

        $resultado = $this->modelo->registrarSolicitudCliente(
            $cliente['id_cliente'],
            $cliente['nombre_completo'] ?? 'Cliente',
            array_keys($carrito),
            array_values($carrito),
            trim($_POST['fecha_limite_retiro'] ?? ''),
            trim($_POST['observaciones'] ?? '')
        );

        if (!empty($resultado['exito'])) {
            unset($_SESSION['carrito_cliente']);

            $_SESSION['mensaje_cliente'] =
                '¡Reservación #' . $resultado['id_reserva'] .
                ' enviada! Está pendiente de confirmación.';
        } else {
            $_SESSION['mensaje_cliente'] =
                $resultado['mensaje'] ?? 'No se pudo registrar la reservación.';
        }

        $this->redirigirCarrito();
    }
}
