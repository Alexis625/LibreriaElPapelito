
<?php

require_once __DIR__ . '/../models/Cliente.php';

class CuentaClienteController
{
    private $cliente;

    public function __construct($conexion)
    {
        $this->cliente = new Cliente($conexion);
    }

    // Mostrar y procesar el registro
    public function registro()
    {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $nombre = trim($_POST['nombre_completo'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $correo = trim($_POST['correo'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmarPassword = $_POST['confirmar_password'] ?? '';

            if ($password !== $confirmarPassword) {
                $error = 'Las contraseñas no coinciden.';
            } else {

                $resultado = $this->cliente->registrarCuenta(
                    $nombre,
                    $telefono,
                    $correo,
                    $direccion,
                    $password
                );

                if (!empty($resultado['exito'])) {
                    header('Location: /el_papelito/public/cliente/login?registro=exitoso');
                    exit;
                }

                $error = $resultado['mensaje'] ?? 'No fue posible crear la cuenta.';
            }
        }

        require __DIR__ . '/../views/auth/cliente_registro.php';
    }

    // Mostrar y procesar el inicio de sesión
    public function login()
    {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $correo = trim($_POST['correo'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($correo === '' || $password === '') {
                $error = 'Completa todos los campos.';
            } else {

                $cliente = $this->cliente->verificarCredenciales(
                    $correo,
                    $password
                );

                if ($cliente) {

                    session_regenerate_id(true);

                    $_SESSION['cliente'] = [
                        'id_cliente' => $cliente['id_cliente'],
                        'nombre_completo' => $cliente['nombre_completo'],
                        'correo' => $cliente['correo']
                    ];

                    header('Location: /el_papelito/public/cliente/inicio');
                    exit;

                } else {
                    $error = 'Correo o contraseña incorrectos, o cuenta inactiva.';
                }
            }
        }

        require __DIR__ . '/../views/auth/cliente_login.php';
    }

    // Cerrar sesión del cliente
    public function logout()
    {
        unset($_SESSION['cliente']);

        session_regenerate_id(true);

        header('Location: /el_papelito/public/cliente/login');
        exit;
    }
}
