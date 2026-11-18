
<?php

require_once __DIR__ . '/../models/Usuario.php';

class AuthController {

    private $usuario;

    public function __construct($conexion) {
        $this->usuario = new Usuario($conexion);
    }

    public function login() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $nombreUsuario = trim($_POST['nombre_usuario'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($nombreUsuario === '' || $password === '') {
                $error = "Debes completar todos los campos.";
                require_once __DIR__ . '/../views/auth/login.php';
                return;
            }

            $usuario = $this->usuario->buscarPorUsuario($nombreUsuario);

            if ($usuario && password_verify($password, $usuario['password_hash'])) {

                $_SESSION['usuario'] = [
                    'id_usuario' => $usuario['id_usuario'],
                    'nombre_completo' => $usuario['nombre_completo'],
                    'nombre_usuario' => $usuario['nombre_usuario'],
                    'id_rol' => $usuario['id_rol'],
                    'rol' => $usuario['rol']
                ];

                header('Location: /el_papelito/public/dashboard');
                exit;

            } else {
                $error = "Usuario o contraseña incorrectos.";
                require_once __DIR__ . '/../views/auth/login.php';
                return;
            }
        }

        require_once __DIR__ . '/../views/auth/login.php';
    }
}
