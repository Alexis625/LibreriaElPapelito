
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

    // Validar que el nombre contenga solamente letras
    private function validarNombreCompleto($nombre)
    {
        return preg_match(
            "/^[\p{L}]+(?:[ '-][\p{L}]+)*$/u",
            $nombre
        ) === 1;
    }

    // Generar nombre de usuario: apellido.nombre
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

        // Dos nombres y dos apellidos:
        // primer nombre = posición 0
        // primer apellido = posición 2

        $primerNombre = $palabras[0];

        $primerApellido = count($palabras) >= 3
            ? $palabras[2]
            : $palabras[1];

        return $primerApellido . '.' . $primerNombre;
    }

    // Validar que el usuario enviado corresponda al nombre
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

    // Validar correo electrónico
    private function validarCorreo($correo)
    {
        return $correo === ''
            || filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
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

        // Campos obligatorios
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

        // Validar nombre completo
        if (!$this->validarNombreCompleto($datos['nombre_completo'])) {
            return false;
        }

        // Validar nombre de usuario automático
        if (!$this->validarNombreUsuario(
            $datos['nombre_completo'],
            $datos['nombre_usuario']
        )) {
            return false;
        }

        // Validar rol
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

        // Validar correo
        if (!$this->validarCorreo($datos['correo'])) {
            return false;
        }

        // Validar contraseña
        if (strlen($datos['password']) < 8) {
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

        // Campos obligatorios
        foreach ([
            'id_rol',
            'nombre_completo',
            'nombre_usuario'
        ] as $campo) {
            if ($datos[$campo] === '') {
                return false;
            }
        }

        // Validar nombre completo
        if (!$this->validarNombreCompleto($datos['nombre_completo'])) {
            return false;
        }

        // Validar nombre de usuario automático
        if (!$this->validarNombreUsuario(
            $datos['nombre_completo'],
            $datos['nombre_usuario']
        )) {
            return false;
        }

        // Validar rol
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

        // Validar correo
        if (!$this->validarCorreo($datos['correo'])) {
            return false;
        }

        // Validar contraseña nueva, si se proporciona
        if (
            $datos['password'] !== '' &&
            strlen($datos['password']) < 8
        ) {
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
