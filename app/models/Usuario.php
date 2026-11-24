
<?php

class Usuario
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    // ==========================================
    // INICIO DE SESIÓN
    // ==========================================

    // Buscar un usuario por su nombre de usuario
    public function buscarPorUsuario($nombreUsuario)
    {
        $sql = "SELECT
                    u.id_usuario,
                    u.nombre_completo,
                    u.nombre_usuario,
                    u.password_hash,
                    u.id_rol,
                    r.nombre AS rol
                FROM usuarios u
                INNER JOIN roles r
                    ON u.id_rol = r.id_rol
                WHERE u.nombre_usuario = :usuario
                AND u.activo = 1
                LIMIT 1";

        $consulta = $this->conexion->prepare($sql);

        $consulta->bindParam(':usuario', $nombreUsuario);

        $consulta->execute();

        return $consulta->fetch(PDO::FETCH_ASSOC);
    }


    // ==========================================
    // ADMINISTRACIÓN DE USUARIOS
    // ==========================================

    // Listar y buscar usuarios según su estado
    public function listarUsuarios($estado = null, $busqueda = '')
    {
        $sql = "SELECT
                    u.id_usuario,
                    u.id_rol,
                    u.nombre_completo,
                    u.nombre_usuario,
                    u.correo,
                    u.activo,
                    u.fecha_creacion,
                    r.nombre AS rol
                FROM usuarios u
                INNER JOIN roles r
                    ON u.id_rol = r.id_rol
                WHERE 1 = 1";

        if ($estado !== null) {
            $sql .= " AND u.activo = :estado";
        }

        if ($busqueda !== '') {
            $sql .= " AND (
                        u.nombre_completo LIKE :busqueda
                        OR u.nombre_usuario LIKE :busqueda
                        OR u.correo LIKE :busqueda
                    )";
        }

        $sql .= " ORDER BY u.id_usuario DESC";

        $consulta = $this->conexion->prepare($sql);

        if ($estado !== null) {
            $consulta->bindValue(
                ':estado',
                $estado,
                PDO::PARAM_INT
            );
        }

        if ($busqueda !== '') {
            $consulta->bindValue(
                ':busqueda',
                '%' . $busqueda . '%',
                PDO::PARAM_STR
            );
        }

        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }


    // Obtener los roles disponibles
    public function listarRoles()
    {
        $sql = "SELECT
                    id_rol,
                    nombre,
                    descripcion
                FROM roles
                ORDER BY id_rol ASC";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }


    // ==========================================
    // REGISTRAR USUARIO
    // ==========================================

    public function registrarUsuario($datos)
    {
        $sql = "INSERT INTO usuarios (
                    id_rol,
                    nombre_completo,
                    nombre_usuario,
                    correo,
                    password_hash
                ) VALUES (
                    :id_rol,
                    :nombre_completo,
                    :nombre_usuario,
                    :correo,
                    :password_hash
                )";

        try {

            $consulta = $this->conexion->prepare($sql);

            return $consulta->execute([
                ':id_rol' => $datos['id_rol'],
                ':nombre_completo' => $datos['nombre_completo'],
                ':nombre_usuario' => $datos['nombre_usuario'],
                ':correo' => $datos['correo'] !== ''
                    ? $datos['correo']
                    : null,
                ':password_hash' => password_hash(
                    $datos['password'],
                    PASSWORD_DEFAULT
                )
            ]);

        } catch (PDOException $e) {

            // Código MySQL 1062: registro duplicado
            if (
                isset($e->errorInfo[1]) &&
                (int) $e->errorInfo[1] === 1062
            ) {
                return false;
            }

            throw $e;
        }
    }


    // ==========================================
    // OBTENER USUARIO
    // ==========================================

    public function obtenerUsuario($idUsuario)
    {
        $sql = "SELECT
                    id_usuario,
                    id_rol,
                    nombre_completo,
                    nombre_usuario,
                    correo,
                    activo,
                    fecha_creacion
                FROM usuarios
                WHERE id_usuario = :id_usuario
                LIMIT 1";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            ':id_usuario' => $idUsuario
        ]);

        return $consulta->fetch(PDO::FETCH_ASSOC);
    }


    // ==========================================
    // ACTUALIZAR USUARIO
    // ==========================================

    public function actualizarUsuario($idUsuario, $datos)
    {
        $sql = "UPDATE usuarios SET
                    id_rol = :id_rol,
                    nombre_completo = :nombre_completo,
                    nombre_usuario = :nombre_usuario,
                    correo = :correo";

        $parametros = [
            ':id_rol' => $datos['id_rol'],
            ':nombre_completo' => $datos['nombre_completo'],
            ':nombre_usuario' => $datos['nombre_usuario'],
            ':correo' => $datos['correo'] !== ''
                ? $datos['correo']
                : null,
            ':id_usuario' => $idUsuario
        ];

        // Cambiar contraseña solamente si se proporciona una nueva
        if (!empty($datos['password'])) {

            $sql .= ",
                    password_hash = :password_hash";

            $parametros[':password_hash'] = password_hash(
                $datos['password'],
                PASSWORD_DEFAULT
            );
        }

        $sql .= " WHERE id_usuario = :id_usuario";

        try {

            $consulta = $this->conexion->prepare($sql);

            return $consulta->execute($parametros);

        } catch (PDOException $e) {

            // Código MySQL 1062: correo o usuario duplicado
            if (
                isset($e->errorInfo[1]) &&
                (int) $e->errorInfo[1] === 1062
            ) {
                return false;
            }

            throw $e;
        }
    }


    // ==========================================
    // ACTIVAR O DESACTIVAR USUARIO
    // ==========================================

    public function cambiarEstado($idUsuario, $estado)
    {
        $sql = "UPDATE usuarios
                SET activo = :activo
                WHERE id_usuario = :id_usuario";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ':activo' => $estado,
            ':id_usuario' => $idUsuario
        ]);
    }


    // ==========================================
    // COMPROBAR DATOS DUPLICADOS
    // ==========================================

    // Comprobar si el nombre de usuario ya existe
    public function existeNombreUsuario($nombreUsuario, $idExcluir = null)
    {
        $sql = "SELECT COUNT(*)
                FROM usuarios
                WHERE nombre_usuario = :nombre_usuario";

        if ($idExcluir !== null) {
            $sql .= " AND id_usuario != :id_usuario";
        }

        $consulta = $this->conexion->prepare($sql);

        $parametros = [
            ':nombre_usuario' => $nombreUsuario
        ];

        if ($idExcluir !== null) {
            $parametros[':id_usuario'] = $idExcluir;
        }

        $consulta->execute($parametros);

        return (int) $consulta->fetchColumn() > 0;
    }

    // Comprobar si el correo ya existe
    public function existeCorreo($correo, $idExcluir = null)
    {
        if ($correo === '') {
            return false;
        }

        $sql = "SELECT COUNT(*)
                FROM usuarios
                WHERE correo = :correo";

        if ($idExcluir !== null) {
            $sql .= " AND id_usuario != :id_usuario";
        }

        $consulta = $this->conexion->prepare($sql);

        $parametros = [
            ':correo' => $correo
        ];

        if ($idExcluir !== null) {
            $parametros[':id_usuario'] = $idExcluir;
        }

        $consulta->execute($parametros);

        return (int) $consulta->fetchColumn() > 0;
    }

}
