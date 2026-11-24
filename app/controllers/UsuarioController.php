
<?php

require_once __DIR__ . '/../models/Usuario.php';

class UsuarioController
{
    private $conexion;
    private $usuario;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->usuario = new Usuario($conexion);
    }

    // ==========================================
    // VALIDACIONES
    // ==========================================

    private function validarNombreCompleto($nombre)
    {
        return preg_match(
            "/^[\p{L}]+(?:[ '-][\p{L}]+)*$/u",
            $nombre
        ) === 1;
    }

    private function generarNombreUsuario($nombreCompleto)
    {
        $palabras = preg_split(
            '/\s+/u',
            trim(mb_strtolower($nombreCompleto, 'UTF-8')),
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        if (count($palabras) < 2) {
            return false;
        }

        $primerNombre = $palabras[0];

        $primerApellido = count($palabras) >= 3
            ? $palabras[2]
            : $palabras[1];

        return $primerApellido . '.' . $primerNombre;
    }

    private function validarNombreUsuario($nombreCompleto, $nombreUsuario)
    {
        $usuarioEsperado = $this->generarNombreUsuario(
            $nombreCompleto
        );

        if ($usuarioEsperado === false) {
            return false;
        }

        return mb_strtolower(trim($nombreUsuario), 'UTF-8')
            === $usuarioEsperado;
    }

    private function validarCorreo($correo)
    {
        return $correo === ''
            || filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
    }

    // ==========================================
    // VALIDAR DUPLICADOS
    // ==========================================

    private function validarDatosDuplicados($datos, $idExcluir = null)
    {
        $nombreUsuarioExiste = $this->usuario->existeNombreUsuario(
            $datos['nombre_usuario'],
            $idExcluir
        );

        $correoExiste = $datos['correo'] !== ''
            && $this->usuario->existeCorreo(
                $datos['correo'],
                $idExcluir
            );

        if ($nombreUsuarioExiste && $correoExiste) {
            $_SESSION['mensaje_usuario'] =
                'El nombre de usuario y el correo electrónico ya están registrados.';

            return false;
        }

        if ($nombreUsuarioExiste) {
            $_SESSION['mensaje_usuario'] =
                'El nombre de usuario ya está registrado.';

            return false;
        }

        if ($correoExiste) {
            $_SESSION['mensaje_usuario'] =
                'El correo electrónico ya está registrado.';

            return false;
        }

        return true;
    }

    // ==========================================
    // MOSTRAR USUARIOS
    // ==========================================

    public function index()
    {
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

        $usuarios = $this->usuario->listarUsuarios(
            $estado,
            $busqueda
        );

        $roles = $this->usuario->listarRoles();

        require_once __DIR__ . '/../views/admin/usuarios.php';
    }

    // ==========================================
    // REGISTRAR USUARIO
    // ==========================================

    public function crear()
    {
        unset($_SESSION['mensaje_usuario']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        $datos = [
            'id_rol' => $_POST['id_rol'] ?? '',
            'nombre_completo' => trim($_POST['nombre_completo'] ?? ''),
            'nombre_usuario' => trim($_POST['nombre_usuario'] ?? ''),
            'correo' => trim($_POST['correo'] ?? ''),
            'password' => $_POST['password'] ?? ''
        ];

        foreach ([
            'id_rol',
            'nombre_completo',
            'nombre_usuario',
            'password'
        ] as $campo) {
            if ($datos[$campo] === '') {
                return false;
            }
        }

        if (!$this->validarNombreCompleto($datos['nombre_completo'])) {
            return false;
        }

        if (!$this->validarNombreUsuario(
            $datos['nombre_completo'],
            $datos['nombre_usuario']
        )) {
            return false;
        }

        $idRol = filter_var(
            $datos['id_rol'],
            FILTER_VALIDATE_INT
        );

        if (!$idRol || $idRol <= 0) {
            return false;
        }

        $roles = $this->usuario->listarRoles();
        $rolesValidos = array_column($roles, 'id_rol');

        if (!in_array($idRol, $rolesValidos)) {
            return false;
        }

        if (!$this->validarCorreo($datos['correo'])) {
            return false;
        }

        if (strlen($datos['password']) < 8) {
            return false;
        }

        // Comprobar si el usuario o correo ya existen
        if (!$this->validarDatosDuplicados($datos)) {
            return false;
        }

        return $this->usuario->registrarUsuario($datos);
    }

    // ==========================================
    // OBTENER USUARIO PARA EDITAR
    // ==========================================

    public function editar()
    {
        $idUsuario = filter_input(
            INPUT_GET,
            'id',
            FILTER_VALIDATE_INT
        );

        if (!$idUsuario || $idUsuario <= 0) {
            return false;
        }

        return $this->usuario->obtenerUsuario($idUsuario);
    }

    // ==========================================
    // ACTUALIZAR USUARIO
    // ==========================================

    public function actualizar()
    {
        unset($_SESSION['mensaje_usuario']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        $idUsuario = filter_input(
            INPUT_POST,
            'id_usuario',
            FILTER_VALIDATE_INT
        );

        if (!$idUsuario || $idUsuario <= 0) {
            return false;
        }

        $usuarioExistente = $this->usuario->obtenerUsuario(
            $idUsuario
        );

        if (!$usuarioExistente) {
            return false;
        }

        $datos = [
            'id_rol' => $_POST['id_rol'] ?? '',
            'nombre_completo' => trim($_POST['nombre_completo'] ?? ''),
            'nombre_usuario' => trim($_POST['nombre_usuario'] ?? ''),
            'correo' => trim($_POST['correo'] ?? ''),
            'password' => $_POST['password'] ?? ''
        ];

        foreach ([
            'id_rol',
            'nombre_completo',
            'nombre_usuario'
        ] as $campo) {
            if ($datos[$campo] === '') {
                return false;
            }
        }

        if (!$this->validarNombreCompleto($datos['nombre_completo'])) {
            return false;
        }

        // Validar nombre de usuario solamente si cambia el nombre completo
if (
    $datos['nombre_completo'] !== $usuarioExistente['nombre_completo'] &&
    !$this->validarNombreUsuario(
        $datos['nombre_completo'],
        $datos['nombre_usuario']
    )
) {
    return false;
}

        $idRol = filter_var(
            $datos['id_rol'],
            FILTER_VALIDATE_INT
        );

        if (!$idRol || $idRol <= 0) {
            return false;
        }

        $roles = $this->usuario->listarRoles();
        $rolesValidos = array_column($roles, 'id_rol');

        if (!in_array($idRol, $rolesValidos)) {
            return false;
        }

        if (!$this->validarCorreo($datos['correo'])) {
            return false;
        }

        if (
            $datos['password'] !== '' &&
            strlen($datos['password']) < 8
        ) {
            return false;
        }

        // Excluir al propio usuario de la comprobación
        if (!$this->validarDatosDuplicados($datos, $idUsuario)) {
            return false;
        }

        return $this->usuario->actualizarUsuario(
            $idUsuario,
            $datos
        );
    }

    // ==========================================
    // ACTIVAR / DESACTIVAR USUARIO
    // ==========================================

    public function cambiarEstado()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        $idUsuario = filter_input(
            INPUT_POST,
            'id_usuario',
            FILTER_VALIDATE_INT
        );

        $estado = filter_input(
            INPUT_POST,
            'estado',
            FILTER_VALIDATE_INT
        );

        if (
            !$idUsuario ||
            $idUsuario <= 0 ||
            !in_array($estado, [0, 1], true)
        ) {
            return false;
        }

        $usuarioExistente = $this->usuario->obtenerUsuario(
            $idUsuario
        );

        if (!$usuarioExistente) {
            return false;
        }

        return $this->usuario->cambiarEstado(
            $idUsuario,
            $estado
        );
    }
}
