<?php

class Compra
{

    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    // Listar compras registradas

    
public function listarCompras(
    $busqueda = '',
    $estado = '',
    $idProveedor = '',
    $fechaInicio = '',
    $fechaFin = '',
    $limite = 10,
    $offset = 0
) {
    $sql = "SELECT
                c.id_compra,
                c.fecha_compra,
                c.numero_documento,
                c.estado,
                p.nombre_empresa,
                u.nombre_completo,
                COALESCE(SUM(dc.subtotal), 0) AS total
            FROM compras c
            INNER JOIN proveedores p
                ON c.id_proveedor = p.id_proveedor
            INNER JOIN usuarios u
                ON c.id_usuario = u.id_usuario
            LEFT JOIN detalle_compra dc
                ON c.id_compra = dc.id_compra
            WHERE (
                p.nombre_empresa LIKE :busqueda
                OR c.numero_documento LIKE :busqueda
            )";

    $parametros = [
        ':busqueda' => "%{$busqueda}%"
    ];

    if (in_array($estado, ['RECIBIDA', 'ANULADA'], true)) {
        $sql .= " AND c.estado = :estado";
        $parametros[':estado'] = $estado;
    }

    if (filter_var($idProveedor, FILTER_VALIDATE_INT)) {
        $sql .= " AND c.id_proveedor = :proveedor";
        $parametros[':proveedor'] = $idProveedor;
    }

    if ($fechaInicio !== '') {
        $sql .= " AND c.fecha_compra >= :fecha_inicio";
        $parametros[':fecha_inicio'] = $fechaInicio . ' 00:00:00';
    }

    if ($fechaFin !== '') {
        $sql .= " AND c.fecha_compra <= :fecha_fin";
        $parametros[':fecha_fin'] = $fechaFin . ' 23:59:59';
    }

    $sql .= " GROUP BY
                c.id_compra,
                c.fecha_compra,
                c.numero_documento,
                c.estado,
                p.nombre_empresa,
                u.nombre_completo
              ORDER BY c.fecha_compra DESC
              LIMIT " . max(1, (int)$limite) . "
              OFFSET " . max(0, (int)$offset);

    $consulta = $this->conexion->prepare($sql);
    $consulta->execute($parametros);

    return $consulta->fetchAll(PDO::FETCH_ASSOC);
}



    // Obtener información general de una compra
    public function obtenerCompra($idCompra)
    {
        $sql = "SELECT
                c.id_compra,
                c.fecha_compra,
                c.numero_documento,
                c.observaciones,
                c.estado,
                p.nombre_empresa,
                u.nombre_completo,
                COALESCE(SUM(dc.subtotal), 0) AS total
            FROM compras c
            INNER JOIN proveedores p
                ON c.id_proveedor = p.id_proveedor
            INNER JOIN usuarios u
                ON c.id_usuario = u.id_usuario
            LEFT JOIN detalle_compra dc
                ON c.id_compra = dc.id_compra
            WHERE c.id_compra = :id
            GROUP BY
                c.id_compra,
                c.fecha_compra,
                c.numero_documento,
                c.observaciones,
                c.estado,
                p.nombre_empresa,
                u.nombre_completo";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([':id' => $idCompra]);

        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener los productos incluidos en una compra
    public function obtenerDetallesCompra($idCompra)
    {
        $sql = "SELECT
                dc.id_detalle_compra,
                dc.id_producto,
                p.codigo,
                p.nombre,
                dc.cantidad,
                dc.costo_unitario,
                dc.subtotal
            FROM detalle_compra dc
            INNER JOIN productos p
                ON dc.id_producto = p.id_producto
            WHERE dc.id_compra = :id
            ORDER BY p.nombre ASC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([':id' => $idCompra]);

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener proveedores activos
    public function listarProveedores()
    {

        $sql = "SELECT id_proveedor, nombre_empresa
                FROM proveedores
                WHERE activo = 1
                ORDER BY nombre_empresa ASC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener productos activos
    public function listarProductos()
    {

        $sql = "SELECT
                    id_producto,
                    codigo,
                    nombre,
                    precio_compra,
                    existencia
                FROM productos
                WHERE activo = 1
                ORDER BY nombre ASC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }


    // Registrar una compra y actualizar el inventario
    public function registrarCompra(
        $idProveedor,
        $idUsuario,
        $numeroDocumento,
        $observaciones,
        $productos,
        $cantidades,
        $costos
    ) {

        try {

            // Validar información general
            if (
                !filter_var($idProveedor, FILTER_VALIDATE_INT) ||
                !filter_var($idUsuario, FILTER_VALIDATE_INT) ||
                !is_array($productos) ||
                !is_array($cantidades) ||
                !is_array($costos) ||
                count($productos) === 0 ||
                count($productos) !== count($cantidades) ||
                count($productos) !== count($costos)
            ) {
                throw new Exception("Los datos de la compra no son válidos.");
            }

            // Normalizar los índices de los arreglos recibidos
            $productos = array_values($productos);
            $cantidades = array_values($cantidades);
            $costos = array_values($costos);

            // Evitar productos repetidos en una misma compra
            $productosUnicos = array_unique(
                array_map('strval', $productos)
            );

            if (count($productosUnicos) !== count($productos)) {
                throw new Exception(
                    "No puedes agregar el mismo producto más de una vez."
                );
            }

            $this->conexion->beginTransaction();

            // Verificar que el proveedor esté activo
            $sqlProveedor = "SELECT id_proveedor
                             FROM proveedores
                             WHERE id_proveedor = :id
                             AND activo = 1
                             FOR UPDATE";

            $consultaProveedor = $this->conexion->prepare($sqlProveedor);

            $consultaProveedor->execute([
                ':id' => $idProveedor
            ]);

            if (!$consultaProveedor->fetch()) {
                throw new Exception("El proveedor no existe o está inactivo.");
            }

            // Registrar la compra
            $sqlCompra = "INSERT INTO compras (
                            id_proveedor,
                            id_usuario,
                            numero_documento,
                            observaciones,
                            estado
                          ) VALUES (
                            :proveedor,
                            :usuario,
                            :documento,
                            :observaciones,
                            'RECIBIDA'
                          )";

            $consultaCompra = $this->conexion->prepare($sqlCompra);

            $consultaCompra->execute([
                ':proveedor' => $idProveedor,
                ':usuario' => $idUsuario,
                ':documento' => $numeroDocumento ?: null,
                ':observaciones' => $observaciones ?: null
            ]);

            $idCompra = $this->conexion->lastInsertId();

            // Preparar consultas de productos
            $sqlProducto = "SELECT id_producto, existencia
                            FROM productos
                            WHERE id_producto = :id
                            AND activo = 1
                            FOR UPDATE";

            $consultaProducto = $this->conexion->prepare($sqlProducto);

            $sqlDetalle = "INSERT INTO detalle_compra (
                            id_compra,
                            id_producto,
                            cantidad,
                            costo_unitario
                           ) VALUES (
                            :compra,
                            :producto,
                            :cantidad,
                            :costo
                           )";

            $consultaDetalle = $this->conexion->prepare($sqlDetalle);

            $sqlActualizar = "UPDATE productos
                              SET existencia = :existencia,
                                  precio_compra = :costo
                              WHERE id_producto = :producto";

            $consultaActualizar = $this->conexion->prepare($sqlActualizar);

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
                                'ENTRADA_COMPRA',
                                :cantidad,
                                :anterior,
                                :nueva,
                                CURRENT_TIMESTAMP,
                                :referencia,
                                :observaciones
                              )";

            $consultaMovimiento = $this->conexion->prepare($sqlMovimiento);

            // Procesar cada producto
            foreach ($productos as $i => $idProducto) {

                $idProducto = filter_var(
                    $idProducto,
                    FILTER_VALIDATE_INT
                );

                $cantidad = filter_var(
                    $cantidades[$i],
                    FILTER_VALIDATE_INT
                );

                $costo = $costos[$i];

                if (
                    !$idProducto ||
                    !$cantidad ||
                    $cantidad <= 0 ||
                    !is_numeric($costo) ||
                    !is_finite((float) $costo) ||
                    (float) $costo < 0 ||
                    (float) $costo > 99999999.99
                ) {
                    throw new Exception("Hay productos con cantidades o costos inválidos.");
                }

                $costo = round((float) $costo, 2);

                // Consultar existencia actual y bloquear el producto
                $consultaProducto->execute([
                    ':id' => $idProducto
                ]);

                $producto = $consultaProducto->fetch(PDO::FETCH_ASSOC);

                if (!$producto) {
                    throw new Exception("Uno de los productos no existe o está inactivo.");
                }

                $existenciaAnterior = (int) $producto['existencia'];
                $existenciaNueva = $existenciaAnterior + $cantidad;

                // Registrar detalle de compra
                $consultaDetalle->execute([
                    ':compra' => $idCompra,
                    ':producto' => $idProducto,
                    ':cantidad' => $cantidad,
                    ':costo' => $costo
                ]);

                // Actualizar existencia y último costo de compra
                $consultaActualizar->execute([
                    ':existencia' => $existenciaNueva,
                    ':costo' => $costo,
                    ':producto' => $idProducto
                ]);

                // Registrar movimiento de inventario
                $consultaMovimiento->execute([
                    ':producto' => $idProducto,
                    ':usuario' => $idUsuario,
                    ':cantidad' => $cantidad,
                    ':anterior' => $existenciaAnterior,
                    ':nueva' => $existenciaNueva,
                    ':referencia' => 'COMPRA #' . $idCompra,
                    ':observaciones' => 'Entrada por compra'
                ]);

            }

            $this->conexion->commit();

            return [
                'exito' => true,
                'id_compra' => $idCompra
            ];

        } catch (Throwable $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            error_log("Error al registrar compra: " . $e->getMessage());

            return [
                'exito' => false,
                'mensaje' => 'No fue posible registrar la compra.'
            ];
        }

    }

    // Anular una compra y revertir las existencias
    public function anularCompra($idCompra, $idUsuario)
    {
        try {
            if (
                !filter_var($idCompra, FILTER_VALIDATE_INT) ||
                !filter_var($idUsuario, FILTER_VALIDATE_INT)
            ) {
                throw new Exception("Los datos de la compra no son válidos.");
            }

            $this->conexion->beginTransaction();

            // Consultar y bloquear la compra
            $sqlCompra = "SELECT id_compra, estado
                      FROM compras
                      WHERE id_compra = :id
                      FOR UPDATE";

            $consultaCompra = $this->conexion->prepare($sqlCompra);
            $consultaCompra->execute([':id' => $idCompra]);

            $compra = $consultaCompra->fetch(PDO::FETCH_ASSOC);

            if (!$compra) {
                throw new Exception("La compra no existe.");
            }

            if ($compra['estado'] !== 'RECIBIDA') {
                throw new Exception("Esta compra ya fue anulada.");
            }

            // Obtener los productos de la compra
            $sqlDetalles = "SELECT id_producto, cantidad
                        FROM detalle_compra
                        WHERE id_compra = :id";

            $consultaDetalles = $this->conexion->prepare($sqlDetalles);
            $consultaDetalles->execute([':id' => $idCompra]);

            $detalles = $consultaDetalles->fetchAll(PDO::FETCH_ASSOC);

            if (empty($detalles)) {
                throw new Exception("La compra no tiene productos registrados.");
            }

            // Consultas para actualizar inventario y registrar movimientos
            $sqlProducto = "SELECT existencia
                        FROM productos
                        WHERE id_producto = :id
                        FOR UPDATE";

            $consultaProducto = $this->conexion->prepare($sqlProducto);

            $sqlActualizar = "UPDATE productos
                          SET existencia = :existencia
                          WHERE id_producto = :id";

            $consultaActualizar = $this->conexion->prepare($sqlActualizar);

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
                            'AJUSTE_SALIDA',
                            :cantidad,
                            :anterior,
                            :nueva,
                            CURRENT_TIMESTAMP,
                            :referencia,
                            :observaciones
                          )";

            $consultaMovimiento = $this->conexion->prepare($sqlMovimiento);

            foreach ($detalles as $detalle) {

                $idProducto = $detalle['id_producto'];
                $cantidad = (int) $detalle['cantidad'];

                $consultaProducto->execute([':id' => $idProducto]);

                $producto = $consultaProducto->fetch(PDO::FETCH_ASSOC);

                if (!$producto) {
                    throw new Exception("Uno de los productos ya no existe.");
                }

                $existenciaAnterior = (int) $producto['existencia'];

                if ($existenciaAnterior < $cantidad) {
                    throw new Exception(
                        "No hay suficientes existencias para anular la compra. Producto ID: "
                        . $idProducto
                    );
                }

                $existenciaNueva = $existenciaAnterior - $cantidad;

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
                    ':referencia' => 'ANULACION COMPRA #' . $idCompra,
                    ':observaciones' => 'Reversión de compra anulada'
                ]);
            }

            // Actualizar estado de la compra
            $sqlEstado = "UPDATE compras
                      SET estado = 'ANULADA'
                      WHERE id_compra = :id";

            $consultaEstado = $this->conexion->prepare($sqlEstado);
            $consultaEstado->execute([':id' => $idCompra]);

            $this->conexion->commit();

            return ['exito' => true];

        } catch (Throwable $e) {

            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            error_log("Error al anular compra: " . $e->getMessage());

            return [
                'exito' => false,
                'mensaje' => $e->getMessage()
            ];
        }
    }


// Contar compras según los filtros aplicados
public function contarCompras(
    $busqueda = '',
    $estado = '',
    $idProveedor = '',
    $fechaInicio = '',
    $fechaFin = ''
) {
    $sql = "SELECT COUNT(*) AS total
            FROM compras c
            INNER JOIN proveedores p
                ON c.id_proveedor = p.id_proveedor
            WHERE (
                p.nombre_empresa LIKE :busqueda
                OR c.numero_documento LIKE :busqueda
            )";

    $parametros = [
        ':busqueda' => "%{$busqueda}%"
    ];

    if (in_array($estado, ['RECIBIDA', 'ANULADA'], true)) {
        $sql .= " AND c.estado = :estado";
        $parametros[':estado'] = $estado;
    }

    if (filter_var($idProveedor, FILTER_VALIDATE_INT)) {
        $sql .= " AND c.id_proveedor = :proveedor";
        $parametros[':proveedor'] = $idProveedor;
    }

    if ($fechaInicio !== '') {
        $sql .= " AND c.fecha_compra >= :fecha_inicio";
        $parametros[':fecha_inicio'] = $fechaInicio . ' 00:00:00';
    }

    if ($fechaFin !== '') {
        $sql .= " AND c.fecha_compra <= :fecha_fin";
        $parametros[':fecha_fin'] = $fechaFin . ' 23:59:59';
    }

    $consulta = $this->conexion->prepare($sql);
    $consulta->execute($parametros);

    return (int) $consulta->fetchColumn();
}

}