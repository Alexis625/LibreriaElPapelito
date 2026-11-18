
<?php

class Producto {

    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Obtener todos los productos
    
    // Listar productos según su estado
    
    // Listar y buscar productos según su estado
    public function listarProductos($estado = null, $busqueda = '') {

        $sql = "SELECT
                    p.id_producto,
                    p.codigo,
                    p.nombre,
                    p.descripcion,
                    p.precio_compra,
                    p.precio_venta,
                    p.existencia,
                    p.stock_minimo,
                    p.activo,
                    c.nombre AS categoria
                FROM productos p
                LEFT JOIN categorias c
                    ON p.id_categoria = c.id_categoria
                WHERE 1 = 1";

        // Filtrar por estado
        if ($estado !== null) {
            $sql .= " AND p.activo = :estado";
        }

        // Buscar por código o nombre
        if ($busqueda !== '') {
            $sql .= " AND (
                        p.codigo LIKE :busqueda
                        OR p.nombre LIKE :busqueda
                    )";
        }

        $sql .= " ORDER BY p.id_producto DESC";

        $consulta = $this->conexion->prepare($sql);

        if ($estado !== null) {
            $consulta->bindValue(':estado', $estado, PDO::PARAM_INT);
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




    // Registrar un nuevo producto
    public function registrarProducto($datos) {

        $sql = "INSERT INTO productos (
                    id_categoria,
                    codigo,
                    nombre,
                    descripcion,
                    precio_compra,
                    precio_venta,
                    existencia,
                    stock_minimo
                ) VALUES (
                    :id_categoria,
                    :codigo,
                    :nombre,
                    :descripcion,
                    :precio_compra,
                    :precio_venta,
                    :existencia,
                    :stock_minimo
                )";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ':id_categoria' => $datos['id_categoria'],
            ':codigo' => $datos['codigo'],
            ':nombre' => $datos['nombre'],
            ':descripcion' => $datos['descripcion'],
            ':precio_compra' => $datos['precio_compra'],
            ':precio_venta' => $datos['precio_venta'],
            ':existencia' => $datos['existencia'],
            ':stock_minimo' => $datos['stock_minimo']
        ]);
    }


    // Buscar un producto por su identificador
    public function obtenerProducto($idProducto) {

        $sql = "SELECT
                    id_producto,
                    codigo,
                    nombre,
                    id_categoria,
                    descripcion,
                    precio_compra,
                    precio_venta,
                    existencia,
                    stock_minimo
                FROM productos
                WHERE id_producto = :id_producto
                LIMIT 1";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            ':id_producto' => $idProducto
        ]);

        return $consulta->fetch(PDO::FETCH_ASSOC);
    }


    // Actualizar un producto existente
    public function actualizarProducto($idProducto, $datos) {

        $sql = "UPDATE productos SET
                    id_categoria = :id_categoria,
                    codigo = :codigo,
                    nombre = :nombre,
                    descripcion = :descripcion,
                    precio_compra = :precio_compra,
                    precio_venta = :precio_venta,
                    existencia = :existencia,
                    stock_minimo = :stock_minimo,
                    fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_producto = :id_producto";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ':id_categoria' => $datos['id_categoria'],
            ':codigo' => $datos['codigo'],
            ':nombre' => $datos['nombre'],
            ':descripcion' => $datos['descripcion'],
            ':precio_compra' => $datos['precio_compra'],
            ':precio_venta' => $datos['precio_venta'],
            ':existencia' => $datos['existencia'],
            ':stock_minimo' => $datos['stock_minimo'],
            ':id_producto' => $idProducto
        ]);
    }


    // Cambiar el estado de un producto
    public function cambiarEstado($idProducto, $estado) {

        $sql = "UPDATE productos
                SET activo = :activo,
                    fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_producto = :id_producto";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ':activo' => $estado,
            ':id_producto' => $idProducto
        ]);
    }


    // Ajustar las existencias y registrar el movimiento
    public function ajustarExistencia(
        $idProducto,
        $idUsuario,
        $cantidad,
        $tipo,
        $observaciones
    ) {

        try {

            $this->conexion->beginTransaction();

            // Consultar existencia actual y bloquear el registro
            $sql = "SELECT existencia
                    FROM productos
                    WHERE id_producto = :id_producto
                    FOR UPDATE";

            $consulta = $this->conexion->prepare($sql);

            $consulta->execute([
                ':id_producto' => $idProducto
            ]);

            $existenciaAnterior = $consulta->fetchColumn();

            if ($existenciaAnterior === false) {
                throw new Exception("Producto no encontrado.");
            }

            // Calcular nueva existencia
            if ($tipo === 'AJUSTE_ENTRADA') {

                $existenciaNueva = $existenciaAnterior + $cantidad;

            } else {

                $existenciaNueva = $existenciaAnterior - $cantidad;

                if ($existenciaNueva < 0) {
                    throw new Exception("Existencias insuficientes.");
                }

            }

            // Actualizar existencias
            $sql = "UPDATE productos
                    SET existencia = :existencia,
                        fecha_actualizacion = CURRENT_TIMESTAMP
                    WHERE id_producto = :id_producto";

            $consulta = $this->conexion->prepare($sql);

            $consulta->execute([
                ':existencia' => $existenciaNueva,
                ':id_producto' => $idProducto
            ]);

            // Registrar movimiento
            $sql = "INSERT INTO movimientos_inventario (
                        id_producto,
                        id_usuario,
                        tipo,
                        cantidad,
                        existencia_anterior,
                        existencia_nueva,
                        observaciones
                    ) VALUES (
                        :id_producto,
                        :id_usuario,
                        :tipo,
                        :cantidad,
                        :existencia_anterior,
                        :existencia_nueva,
                        :observaciones
                    )";

            $consulta = $this->conexion->prepare($sql);

            $consulta->execute([
                ':id_producto' => $idProducto,
                ':id_usuario' => $idUsuario,
                ':tipo' => $tipo,
                ':cantidad' => $cantidad,
                ':existencia_anterior' => $existenciaAnterior,
                ':existencia_nueva' => $existenciaNueva,
                ':observaciones' => $observaciones
            ]);

            $this->conexion->commit();

            return true;

        } catch (Exception $e) {

            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            return false;
        }
    }


}
