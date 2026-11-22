
<?php

class Venta
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    // ==========================================
    // LISTAR VENTAS
    // ==========================================

    public function listarVentas(
        $busqueda = '',
        $estado = '',
        $fechaInicio = '',
        $fechaFin = '',
        $limite = 10,
        $offset = 0
    ) {
        $sql = "SELECT
                    v.id_venta,
                    v.fecha_venta,
                    v.estado,
                    v.observaciones,
                    COALESCE(c.nombre_completo, 'Consumidor final') AS cliente,
                    u.nombre_completo AS usuario,
                    COALESCE(SUM(dv.subtotal), 0) AS total
                FROM ventas v
                LEFT JOIN clientes c
                    ON v.id_cliente = c.id_cliente
                INNER JOIN usuarios u
                    ON v.id_usuario = u.id_usuario
                LEFT JOIN detalle_venta dv
                    ON v.id_venta = dv.id_venta
                WHERE (
                    c.nombre_completo LIKE :busqueda
                    OR CAST(v.id_venta AS CHAR) LIKE :busqueda
                    OR u.nombre_completo LIKE :busqueda
                )";

        $parametros = [
            ':busqueda' => "%{$busqueda}%"
        ];

        if (in_array($estado, ['COMPLETADA', 'ANULADA'], true)) {
            $sql .= " AND v.estado = :estado";
            $parametros[':estado'] = $estado;
        }

        if ($fechaInicio !== '') {
            $sql .= " AND v.fecha_venta >= :fecha_inicio";
            $parametros[':fecha_inicio'] = $fechaInicio . ' 00:00:00';
        }

        if ($fechaFin !== '') {
            $sql .= " AND v.fecha_venta <= :fecha_fin";
            $parametros[':fecha_fin'] = $fechaFin . ' 23:59:59';
        }

        $sql .= " GROUP BY
                    v.id_venta,
                    v.fecha_venta,
                    v.estado,
                    v.observaciones,
                    c.nombre_completo,
                    u.nombre_completo
                  ORDER BY v.fecha_venta DESC
                  LIMIT " . max(1, (int)$limite) . "
                  OFFSET " . max(0, (int)$offset);

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($parametros);

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==========================================
    // CONTAR VENTAS
    // ==========================================

    public function contarVentas(
        $busqueda = '',
        $estado = '',
        $fechaInicio = '',
        $fechaFin = ''
    ) {
        $sql = "SELECT COUNT(DISTINCT v.id_venta)
                FROM ventas v
                LEFT JOIN clientes c
                    ON v.id_cliente = c.id_cliente
                INNER JOIN usuarios u
                    ON v.id_usuario = u.id_usuario
                WHERE (
                    c.nombre_completo LIKE :busqueda
                    OR CAST(v.id_venta AS CHAR) LIKE :busqueda
                    OR u.nombre_completo LIKE :busqueda
                )";

        $parametros = [
            ':busqueda' => "%{$busqueda}%"
        ];

        if (in_array($estado, ['COMPLETADA', 'ANULADA'], true)) {
            $sql .= " AND v.estado = :estado";
            $parametros[':estado'] = $estado;
        }

        if ($fechaInicio !== '') {
            $sql .= " AND v.fecha_venta >= :fecha_inicio";
            $parametros[':fecha_inicio'] = $fechaInicio . ' 00:00:00';
        }

        if ($fechaFin !== '') {
            $sql .= " AND v.fecha_venta <= :fecha_fin";
            $parametros[':fecha_fin'] = $fechaFin . ' 23:59:59';
        }

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($parametros);

        return (int)$consulta->fetchColumn();
    }

    // ==========================================
    // OBTENER INFORMACIÓN DE UNA VENTA
    // ==========================================

    public function obtenerVenta($idVenta)
    {
        $sql = "SELECT
                    v.id_venta,
                    v.fecha_venta,
                    v.estado,
                    v.observaciones,
                    v.id_cliente,
                    COALESCE(c.nombre_completo, 'Consumidor final') AS cliente,
                    c.telefono,
                    c.correo,
                    u.nombre_completo AS usuario,
                    COALESCE(SUM(dv.subtotal), 0) AS total
                FROM ventas v
                LEFT JOIN clientes c
                    ON v.id_cliente = c.id_cliente
                INNER JOIN usuarios u
                    ON v.id_usuario = u.id_usuario
                LEFT JOIN detalle_venta dv
                    ON v.id_venta = dv.id_venta
                WHERE v.id_venta = :id
                GROUP BY
                    v.id_venta,
                    v.fecha_venta,
                    v.estado,
                    v.observaciones,
                    v.id_cliente,
                    c.nombre_completo,
                    c.telefono,
                    c.correo,
                    u.nombre_completo";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([':id' => $idVenta]);

        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    // ==========================================
    // OBTENER DETALLES DE UNA VENTA
    // ==========================================

    public function obtenerDetallesVenta($idVenta)
    {
        $sql = "SELECT
                    dv.id_detalle_venta,
                    dv.id_producto,
                    p.codigo,
                    p.nombre,
                    dv.cantidad,
                    dv.precio_unitario,
                    dv.costo_unitario,
                    dv.subtotal,
                    dv.utilidad
                FROM detalle_venta dv
                INNER JOIN productos p
                    ON dv.id_producto = p.id_producto
                WHERE dv.id_venta = :id
                ORDER BY p.nombre ASC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([':id' => $idVenta]);

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==========================================
    // LISTAR PRODUCTOS ACTIVOS
    // ==========================================

    public function listarProductos()
    {
        $sql = "SELECT
                    id_producto,
                    codigo,
                    nombre,
                    precio_compra,
                    precio_venta,
                    existencia
                FROM productos
                WHERE activo = 1
                  AND existencia > 0
                ORDER BY nombre ASC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==========================================
    // LISTAR CLIENTES ACTIVOS
    // ==========================================

    public function listarClientes()
    {
        $sql = "SELECT
                    id_cliente,
                    nombre_completo,
                    telefono
                FROM clientes
                WHERE activo = 1
                ORDER BY nombre_completo ASC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==========================================
    // REGISTRAR VENTA
    // ==========================================

    public function registrarVenta(
        $idCliente,
        $idUsuario,
        $observaciones,
        $productos,
        $cantidades
    ) {
        try {

            if (
                !filter_var($idUsuario, FILTER_VALIDATE_INT) ||
                !is_array($productos) ||
                !is_array($cantidades) ||
                count($productos) === 0 ||
                count($productos) !== count($cantidades)
            ) {
                throw new Exception("Los datos de la venta no son válidos.");
            }

            // Validar cliente opcional
            if ($idCliente !== null && $idCliente !== '') {
                $idCliente = filter_var(
                    $idCliente,
                    FILTER_VALIDATE_INT
                );

                if (!$idCliente) {
                    throw new Exception("El cliente seleccionado no es válido.");
                }
            } else {
                $idCliente = null;
            }

            $productos = array_values($productos);
            $cantidades = array_values($cantidades);

            // Evitar productos repetidos
            $productosUnicos = array_unique(
                array_map('strval', $productos)
            );

            if (count($productosUnicos) !== count($productos)) {
                throw new Exception(
                    "No puedes agregar el mismo producto más de una vez."
                );
            }

            $this->conexion->beginTransaction();

            // Verificar cliente si fue seleccionado
            if ($idCliente !== null) {
                $sqlCliente = "SELECT id_cliente
                               FROM clientes
                               WHERE id_cliente = :id
                                 AND activo = 1
                               FOR UPDATE";

                $consultaCliente = $this->conexion->prepare($sqlCliente);
                $consultaCliente->execute([':id' => $idCliente]);

                if (!$consultaCliente->fetch()) {
                    throw new Exception(
                        "El cliente no existe o está inactivo."
                    );
                }
            }

            // Registrar encabezado de la venta
            $sqlVenta = "INSERT INTO ventas (
                            id_cliente,
                            id_usuario,
                            observaciones,
                            estado
                         ) VALUES (
                            :cliente,
                            :usuario,
                            :observaciones,
                            'COMPLETADA'
                         )";

            $consultaVenta = $this->conexion->prepare($sqlVenta);

            $consultaVenta->execute([
                ':cliente' => $idCliente,
                ':usuario' => $idUsuario,
                ':observaciones' => $observaciones ?: null
            ]);

            $idVenta = $this->conexion->lastInsertId();

            // Consultar y bloquear productos
            $sqlProducto = "SELECT
                                id_producto,
                                nombre,
                                precio_compra,
                                precio_venta,
                                existencia
                            FROM productos
                            WHERE id_producto = :id
                              AND activo = 1
                            FOR UPDATE";

            $consultaProducto = $this->conexion->prepare($sqlProducto);

            // Registrar detalle
            $sqlDetalle = "INSERT INTO detalle_venta (
                                id_venta,
                                id_producto,
                                cantidad,
                                precio_unitario,
                                costo_unitario
                           ) VALUES (
                                :venta,
                                :producto,
                                :cantidad,
                                :precio,
                                :costo
                           )";

            $consultaDetalle = $this->conexion->prepare($sqlDetalle);

            // Actualizar inventario
            $sqlActualizar = "UPDATE productos
                              SET existencia = :existencia
                              WHERE id_producto = :id";

            $consultaActualizar = $this->conexion->prepare($sqlActualizar);

            // Registrar movimiento de inventario
            $sqlMovimiento = "INSERT INTO movimientos_inventario (
                                id_producto,
                                id_usuario,
                                tipo,
                                cantidad,
                                existencia_anterior,
                                existencia_nueva,
                                fecha_movimiento,
                                referencia,
                                observaciones
                              ) VALUES (
                                :producto,
                                :usuario,
                                'SALIDA_VENTA',
                                :cantidad,
                                :anterior,
                                :nueva,
                                CURRENT_TIMESTAMP,
                                :referencia,
                                :observaciones
                              )";

            $consultaMovimiento = $this->conexion->prepare($sqlMovimiento);

            // Procesar productos
            foreach ($productos as $i => $idProducto) {

                $idProducto = filter_var(
                    $idProducto,
                    FILTER_VALIDATE_INT
                );

                $cantidad = filter_var(
                    $cantidades[$i],
                    FILTER_VALIDATE_INT
                );

                if (
                    !$idProducto ||
                    !$cantidad ||
                    $cantidad <= 0
                ) {
                    throw new Exception(
                        "Hay productos o cantidades inválidas."
                    );
                }

                $consultaProducto->execute([
                    ':id' => $idProducto
                ]);

                $producto = $consultaProducto->fetch(PDO::FETCH_ASSOC);

                if (!$producto) {
                    throw new Exception(
                        "Uno de los productos no existe o está inactivo."
                    );
                }

                $existenciaAnterior = (int)$producto['existencia'];

                if ($existenciaAnterior < $cantidad) {
                    throw new Exception(
                        "No hay suficientes existencias de: "
                        . $producto['nombre']
                    );
                }

                $precio = (float)$producto['precio_venta'];
                $costo = (float)$producto['precio_compra'];

                $existenciaNueva = $existenciaAnterior - $cantidad;

                // Guardar detalle con precios obtenidos de la BD
                $consultaDetalle->execute([
                    ':venta' => $idVenta,
                    ':producto' => $idProducto,
                    ':cantidad' => $cantidad,
                    ':precio' => $precio,
                    ':costo' => $costo
                ]);

                // Descontar inventario
                $consultaActualizar->execute([
                    ':existencia' => $existenciaNueva,
                    ':id' => $idProducto
                ]);

                // Registrar movimiento
                $consultaMovimiento->execute([
                    ':producto' => $idProducto,
                    ':usuario' => $idUsuario,
                    ':cantidad' => $cantidad,
                    ':anterior' => $existenciaAnterior,
                    ':nueva' => $existenciaNueva,
                    ':referencia' => 'VENTA #' . $idVenta,
                    ':observaciones' => 'Salida por venta'
                ]);
            }

            $this->conexion->commit();

            return [
                'exito' => true,
                'id_venta' => $idVenta
            ];

        } catch (Throwable $e) {

            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            error_log("Error al registrar venta: " . $e->getMessage());

            return [
                'exito' => false,
                'mensaje' => $e->getMessage()
            ];
        }
    }

    // ==========================================
    // ANULAR VENTA Y DEVOLVER EXISTENCIAS
    // ==========================================

    public function anularVenta($idVenta, $idUsuario)
    {
        try {

            if (
                !filter_var($idVenta, FILTER_VALIDATE_INT) ||
                !filter_var($idUsuario, FILTER_VALIDATE_INT)
            ) {
                throw new Exception("Los datos de la venta no son válidos.");
            }

            $this->conexion->beginTransaction();

            // Consultar y bloquear venta
            $sqlVenta = "SELECT id_venta, estado
                         FROM ventas
                         WHERE id_venta = :id
                         FOR UPDATE";

            $consultaVenta = $this->conexion->prepare($sqlVenta);
            $consultaVenta->execute([':id' => $idVenta]);

            $venta = $consultaVenta->fetch(PDO::FETCH_ASSOC);

            if (!$venta) {
                throw new Exception("La venta no existe.");
            }

            if ($venta['estado'] !== 'COMPLETADA') {
                throw new Exception("Esta venta ya fue anulada.");
            }

            // Consultar detalles
            $sqlDetalles = "SELECT id_producto, cantidad
                            FROM detalle_venta
                            WHERE id_venta = :id";

            $consultaDetalles = $this->conexion->prepare($sqlDetalles);
            $consultaDetalles->execute([':id' => $idVenta]);

            $detalles = $consultaDetalles->fetchAll(PDO::FETCH_ASSOC);

            if (empty($detalles)) {
                throw new Exception(
                    "La venta no tiene productos registrados."
                );
            }

            // Consultar productos
            $sqlProducto = "SELECT existencia
                            FROM productos
                            WHERE id_producto = :id
                            FOR UPDATE";

            $consultaProducto = $this->conexion->prepare($sqlProducto);

            // Actualizar existencias
            $sqlActualizar = "UPDATE productos
                              SET existencia = :existencia
                              WHERE id_producto = :id";

            $consultaActualizar = $this->conexion->prepare($sqlActualizar);

            // Registrar devolución al inventario
            $sqlMovimiento = "INSERT INTO movimientos_inventario (
                                id_producto,
                                id_usuario,
                                tipo,
                                cantidad,
                                existencia_anterior,
                                existencia_nueva,
                                fecha_movimiento,
                                referencia,
                                observaciones
                              ) VALUES (
                                :producto,
                                :usuario,
                                'AJUSTE_ENTRADA',
                                :cantidad,
                                :anterior,
                                :nueva,
                                CURRENT_TIMESTAMP,
                                :referencia,
                                :observaciones
                              )";

            $consultaMovimiento = $this->conexion->prepare($sqlMovimiento);

            foreach ($detalles as $detalle) {

                $idProducto = (int)$detalle['id_producto'];
                $cantidad = (int)$detalle['cantidad'];

                $consultaProducto->execute([
                    ':id' => $idProducto
                ]);

                $producto = $consultaProducto->fetch(PDO::FETCH_ASSOC);

                if (!$producto) {
                    throw new Exception(
                        "Uno de los productos de la venta ya no existe."
                    );
                }

                $existenciaAnterior = (int)$producto['existencia'];
                $existenciaNueva = $existenciaAnterior + $cantidad;

                $consultaActualizar->execute([
                    ':existencia' => $existenciaNueva,
                    ':id' => $idProducto
                ]);

                $consultaMovimiento->execute([
                    ':producto' => $idProducto,
                    ':usuario' => $idUsuario,
                    ':cantidad' => $cantidad,
                    ':anterior' => $existenciaAnterior,
                    ':nueva' => $existenciaNueva,
                    ':referencia' => 'ANULACION VENTA #' . $idVenta,
                    ':observaciones' => 'Devolución por anulación de venta'
                ]);
            }

            // Cambiar estado de venta
            $sqlEstado = "UPDATE ventas
                          SET estado = 'ANULADA'
                          WHERE id_venta = :id";

            $consultaEstado = $this->conexion->prepare($sqlEstado);
            $consultaEstado->execute([':id' => $idVenta]);

            $this->conexion->commit();

            return ['exito' => true];

        } catch (Throwable $e) {

            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            error_log("Error al anular venta: " . $e->getMessage());

            return [
                'exito' => false,
                'mensaje' => $e->getMessage()
            ];
        }
    }
}
