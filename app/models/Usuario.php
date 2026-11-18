
<?php

class Usuario {

    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function buscarPorUsuario($nombreUsuario) {

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
}
