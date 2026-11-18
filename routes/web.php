<?php

require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/ProductoController.php';
require_once __DIR__ . '/../app/controllers/ProveedorController.php';

// Obtener la ruta solicitada
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$base = '/el_papelito/public';

$ruta = substr($uri, strlen($base));

$ruta = $ruta === '' ? '/' : $ruta;

// Crear el controlador de autenticación
$authController = new AuthController($conexion);

switch ($ruta) {

    case '/':
        $controller = new HomeController();
        $controller->index();
        break;

    case '/login':
        $authController->login();
        break;

    case '/logout':
        session_unset();
        session_destroy();

        header('Location: /el_papelito/public/login');
        exit;

    case '/dashboard':

        if (!isset($_SESSION['usuario'])) {
            header('Location: /el_papelito/public/login');
            exit;
        }

        $dashboardController = new DashboardController($conexion);
        $dashboardController->index();

        break;

    case '/productos':

        if (!isset($_SESSION['usuario'])) {
            header('Location: /el_papelito/public/login');
            exit;
        }

        $productoController = new ProductoController($conexion);
        $productoController->index();

        break;

    case '/categorias/listar':

        if (!isset($_SESSION['usuario'])) {
            header('Location: /el_papelito/public/login');
            exit;
        }

        $categoriaController = new CategoriaController($conexion);

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(
            $categoriaController->index(),
            JSON_UNESCAPED_UNICODE
        );

        break;

    case '/categorias/crear':

        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'exito' => false,
                'mensaje' => 'Método no permitido.'
            ]);
            exit;
        }

        $categoriaController = new CategoriaController($conexion);

        $resultado = $categoriaController->guardar();

        echo json_encode([
            'exito' => $resultado,
            'mensaje' => $resultado
                ? 'Categoría registrada correctamente.'
                : 'No se pudo registrar la categoría.'
        ], JSON_UNESCAPED_UNICODE);

        break;

    case '/categorias/eliminar':

        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'exito' => false,
                'mensaje' => 'Método no permitido.'
            ]);
            exit;
        }

        $categoriaController = new CategoriaController($conexion);

        $resultado = $categoriaController->eliminar();

        echo json_encode([
            'exito' => $resultado,
            'mensaje' => $resultado
                ? 'Categoría eliminada correctamente.'
                : 'No se pudo eliminar. Verifica si tiene productos asociados.'
        ], JSON_UNESCAPED_UNICODE);

        break;

    case '/productos/crear':

        if (!isset($_SESSION['usuario'])) {
            header('Location: /el_papelito/public/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /el_papelito/public/productos');
            exit;
        }

        $productoController = new ProductoController($conexion);

        $resultado = $productoController->crear();

        if ($resultado) {
            header('Location: /el_papelito/public/productos?registro=exitoso');
        } else {
            header('Location: /el_papelito/public/productos?registro=error');
        }

        exit;

    case '/productos/editar':

        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');

        $productoController = new ProductoController($conexion);

        $producto = $productoController->editar();

        if ($producto) {
            echo json_encode($producto, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);

            echo json_encode([
                'exito' => false,
                'mensaje' => 'Producto no encontrado.'
            ], JSON_UNESCAPED_UNICODE);
        }

        break;

    case '/productos/actualizar':

        if (!isset($_SESSION['usuario'])) {
            header('Location: /el_papelito/public/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /el_papelito/public/productos');
            exit;
        }

        $productoController = new ProductoController($conexion);

        $resultado = $productoController->actualizar();

        if ($resultado) {
            header('Location: /el_papelito/public/productos?actualizacion=exitosa');
        } else {
            header('Location: /el_papelito/public/productos?actualizacion=error');
        }

        exit;

    case '/productos/estado':

        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /el_papelito/public/productos');
            exit;
        }

        $productoController = new ProductoController($conexion);

        $resultado = $productoController->cambiarEstado();

        if ($resultado) {
            header('Location: /el_papelito/public/productos?estado=exitoso');
        } else {
            header('Location: /el_papelito/public/productos?estado=error');
        }

        exit;

    case '/productos/ajustar-inventario':

        if (!isset($_SESSION['usuario'])) {
            header('Location: /el_papelito/public/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /el_papelito/public/productos');
            exit;
        }

        $productoController = new ProductoController($conexion);

        $resultado = $productoController->ajustarInventario();

        if ($resultado) {
            header('Location: /el_papelito/public/productos?inventario=exitoso');
        } else {
            header('Location: /el_papelito/public/productos?inventario=error');
        }

        exit;

        // Listar proveedores
case '/proveedores':
    if (!isset($_SESSION['usuario'])) {
        header('Location: /el_papelito/public/login');
        exit;
    }

    $controlador = new ProveedorController($conexion);
    $controlador->index();
    break;

// Registrar proveedor
case '/proveedores/crear':
    if (!isset($_SESSION['usuario'])) {
        header('Location: /el_papelito/public/login');
        exit;
    }

    $controlador = new ProveedorController($conexion);
    $resultado = $controlador->crear();

    header('Location: /el_papelito/public/proveedores?registro=' .
        ($resultado ? 'exitoso' : 'error'));
    exit;

// Consultar proveedor
case '/proveedores/editar':
    if (!isset($_SESSION['usuario'])) {
        http_response_code(401);
        exit;
    }

    $controlador = new ProveedorController($conexion);
    header('Content-Type: application/json');
    echo json_encode($controlador->editar());
    exit;

// Actualizar proveedor
case '/proveedores/actualizar':
    if (!isset($_SESSION['usuario'])) {
        header('Location: /el_papelito/public/login');
        exit;
    }

    $controlador = new ProveedorController($conexion);
    $resultado = $controlador->actualizar();

    header('Location: /el_papelito/public/proveedores?actualizacion=' .
        ($resultado ? 'exitosa' : 'error'));
    exit;

// Cambiar estado del proveedor
case '/proveedores/estado':
    if (!isset($_SESSION['usuario'])) {
        header('Location: /el_papelito/public/login');
        exit;
    }

    $controlador = new ProveedorController($conexion);
    $resultado = $controlador->cambiarEstado();

    header('Location: /el_papelito/public/proveedores?estado=' .
        ($resultado ? 'exitoso' : 'error'));
    exit;


// Eliminar proveedor
case '/proveedores/eliminar':
    if (!isset($_SESSION['usuario'])) {
        header('Location: /el_papelito/public/login');
        exit;
    }

    $controlador = new ProveedorController($conexion);
    $resultado = $controlador->eliminar();

    if ($resultado) {
        header('Location: /el_papelito/public/proveedores?eliminacion=exitosa');
    } else {
        header('Location: /el_papelito/public/proveedores?eliminacion=error');
    }
    exit;


    default:
        http_response_code(404);
        echo "Página no encontrada";
        break;
}
