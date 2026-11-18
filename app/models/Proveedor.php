
<?php

class Proveedor {

    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Listar proveedores
    public function listarProveedores($estado = null, $busqueda = '') {

        $sql = "SELECT *
                FROM proveedores
                WHERE 1 = 1";

        $parametros = [];

        // Filtrar por estado
        if ($estado !== null) {
            $sql .= " AND activo = :activo";
            $parametros[':activo'] = $estado;
        }

        // Buscar por empresa o contacto
        if (!empty($busqueda)) {
            $sql .= " AND (
                        nombre_empresa LIKE :busqueda
                        OR nombre_contacto LIKE :busqueda
                        OR telefono LIKE :busqueda
                    )";

            $parametros[':busqueda'] = "%{$busqueda}%";
        }

        $sql .= " ORDER BY nombre_empresa ASC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($parametros);

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }


    // Registrar proveedor
    public function registrarProveedor($datos) {

        $sql = "INSERT INTO proveedores (
                    nombre_empresa,
                    nombre_contacto,
                    telefono,
                    correo,
                    direccion
                ) VALUES (
                    :nombre_empresa,
                    :nombre_contacto,
                    :telefono,
                    :correo,
                    :direccion
                )";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ':nombre_empresa' => $datos['nombre_empresa'],
            ':nombre_contacto' => $datos['nombre_contacto'],
            ':telefono' => $datos['telefono'],
            ':correo' => $datos['correo'],
            ':direccion' => $datos['direccion']
        ]);
    }

    // Obtener proveedor por ID
    public function obtenerProveedor($idProveedor) {

        $sql = "SELECT *
                FROM proveedores
                WHERE id_proveedor = :id_proveedor";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            ':id_proveedor' => $idProveedor
        ]);

        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar proveedor
    public function actualizarProveedor($idProveedor, $datos) {

        $sql = "UPDATE proveedores
                SET nombre_empresa = :nombre_empresa,
                    nombre_contacto = :nombre_contacto,
                    telefono = :telefono,
                    correo = :correo,
                    direccion = :direccion
                WHERE id_proveedor = :id_proveedor";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ':nombre_empresa' => $datos['nombre_empresa'],
            ':nombre_contacto' => $datos['nombre_contacto'],
            ':telefono' => $datos['telefono'],
            ':correo' => $datos['correo'],
            ':direccion' => $datos['direccion'],
            ':id_proveedor' => $idProveedor
        ]);
    }

    // Cambiar estado del proveedor
    public function cambiarEstado($idProveedor, $estado) {

        $sql = "UPDATE proveedores
                SET activo = :activo
                WHERE id_proveedor = :id_proveedor";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ':activo' => $estado,
            ':id_proveedor' => $idProveedor
        ]);
    }


    // Eliminar proveedor
    public function eliminarProveedor($idProveedor) {

        $sql = "DELETE FROM proveedores
                WHERE id_proveedor = :id_proveedor
                AND activo = 0
                AND NOT EXISTS (
                    SELECT 1
                    FROM compras
                    WHERE compras.id_proveedor = proveedores.id_proveedor
                )";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ':id_proveedor' => $idProveedor
        ]) && $consulta->rowCount() > 0;
    }


}
