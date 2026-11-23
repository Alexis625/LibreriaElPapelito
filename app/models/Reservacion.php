
<?php

class Reservacion
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    // =====================================
    // LISTAR RESERVACIONES
    // =====================================

    public function listarReservaciones(
        $busqueda = '',
        $estado = '',
        $fechaInicio = '',
        $fechaFin = '',
        $limite = 10,
        $offset = 0
    ) {
        $sql = "SELECT
                    r.id_reserva,
                    r.fecha_reserva,
                    r.fecha_limite_retiro,
                    r.estado,
                    r.observaciones,
                    c.id_cliente,
                    c.nombre_completo,
                    c.telefono,
                    COUNT(dr.id_detalle_reserva) AS cantidad_productos,
                    COALESCE(SUM(dr.cantidad), 0) AS unidades
                FROM reservas r
                INNER JOIN clientes c
                    ON r.id_cliente = c.id_cliente
                LEFT JOIN detalle_reserva dr
                    ON r.id_reserva = dr.id_reserva
                WHERE (
                    c.nombre_completo LIKE :busqueda
                    OR c.telefono LIKE :busqueda
                    OR CAST(r.id_reserva AS CHAR) LIKE :busqueda
                )";

        $parametros = [
            ':busqueda' => "%{$busqueda}%"
        ];

        if (in_array($estado, [
            'PENDIENTE',
            'CONFIRMADA',
            'ENTREGADA',
            'CANCELADA'
        ], true)) {
            $sql .= " AND r.estado = :estado";
            $parametros[':estado'] = $estado;
        }

        if ($fechaInicio !== '') {
            $sql .= " AND r.fecha_reserva >= :fecha_inicio";
            $parametros[':fecha_inicio'] = $fechaInicio . ' 00:00:00';
        }

        if ($fechaFin !== '') {
            $sql .= " AND r.fecha_reserva <= :fecha_fin";
            $parametros[':fecha_fin'] = $fechaFin . ' 23:59:59';
        }

        $sql .= " GROUP BY
                    r.id_reserva,
                    r.fecha_reserva,
                    r.fecha_limite_retiro,
                    r.estado,
                    r.observaciones,
                    c.id_cliente,
                    c.nombre_completo,
                    c.telefono
                  ORDER BY r.fecha_reserva DESC
                  LIMIT " . max(1, (int) $limite) . "
                  OFFSET " . max(0, (int) $offset);

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($parametros);

        $reservaciones = $consulta->fetchAll(PDO::FETCH_ASSOC);

        foreach ($reservaciones as &$reservacion) {
            $datosRegistro = $this->separarDatosObservacion(
                $reservacion['observaciones'] ?? null
            );

            $reservacion['registrado_por'] = $datosRegistro['registrado_por'];
            $reservacion['observaciones_texto'] = $datosRegistro['observaciones'];
            $reservacion['observaciones'] = $datosRegistro['observaciones'];
        }
        unset($reservacion);

        return $reservaciones;
    }

    // =====================================
    // CONTAR RESERVACIONES
    // =====================================

    public function contarReservaciones(
        $busqueda = '',
        $estado = '',
        $fechaInicio = '',
        $fechaFin = ''
    ) {
        $sql = "SELECT COUNT(DISTINCT r.id_reserva) AS total
                FROM reservas r
                INNER JOIN clientes c
                    ON r.id_cliente = c.id_cliente
                WHERE (
                    c.nombre_completo LIKE :busqueda
                    OR c.telefono LIKE :busqueda
                    OR CAST(r.id_reserva AS CHAR) LIKE :busqueda
                )";

        $parametros = [
            ':busqueda' => "%{$busqueda}%"
        ];

        if (in_array($estado, [
            'PENDIENTE',
            'CONFIRMADA',
            'ENTREGADA',
            'CANCELADA'
        ], true)) {
            $sql .= " AND r.estado = :estado";
            $parametros[':estado'] = $estado;
        }

        if ($fechaInicio !== '') {
            $sql .= " AND r.fecha_reserva >= :fecha_inicio";
            $parametros[':fecha_inicio'] = $fechaInicio . ' 00:00:00';
        }

        if ($fechaFin !== '') {
            $sql .= " AND r.fecha_reserva <= :fecha_fin";
            $parametros[':fecha_fin'] = $fechaFin . ' 23:59:59';
        }

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($parametros);

        return (int) $consulta->fetchColumn();
    }

    // =====================================
    // OBTENER INFORMACION DE UNA RESERVACION
    // =====================================

    public function obtenerReservacion($idReserva)
    {
        $sql = "SELECT
                    r.id_reserva,
                    r.fecha_reserva,
                    r.fecha_limite_retiro,
                    r.estado,
                    r.observaciones,
                    c.id_cliente,
                    c.nombre_completo,
                    c.telefono,
                    c.correo,
                    c.direccion
                FROM reservas r
                INNER JOIN clientes c
                    ON r.id_cliente = c.id_cliente
                WHERE r.id_reserva = :id";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            ':id' => $idReserva
        ]);

        $reservacion = $consulta->fetch(PDO::FETCH_ASSOC);

        if ($reservacion) {
            $datosRegistro = $this->separarDatosObservacion(
                $reservacion['observaciones'] ?? null
            );

            $reservacion['registrado_por'] = $datosRegistro['registrado_por'];
            $reservacion['observaciones_texto'] = $datosRegistro['observaciones'];
            $reservacion['observaciones'] = $datosRegistro['observaciones'];
        }

        return $reservacion;
    }

    // =====================================
    // SEPARAR DATOS GUARDADOS EN OBSERVACIONES
    // =====================================

    private function separarDatosObservacion($valor)
    {
        if ($valor === null || $valor === '') {
            return [
                'registrado_por' => 'No disponible',
                'observaciones' => null
            ];
        }

        $datos = json_decode($valor, true);

        // Compatibilidad con reservaciones antiguas que guardaban
        // únicamente texto en el campo observaciones.
        if (
            !is_array($datos) ||
            !array_key_exists('registrado_por', $datos) ||
            !array_key_exists('observaciones', $datos)
        ) {
            return [
                'registrado_por' => 'No disponible',
                'observaciones' => $valor
            ];
        }

        return [
            'registrado_por' => $datos['registrado_por'] ?: 'No disponible',
            'observaciones' => $datos['observaciones'] ?? null
        ];
    }

    // =====================================
    // OBTENER PRODUCTOS DE UNA RESERVACION
    // =====================================

    public function obtenerDetallesReservacion($idReserva)
    {
        $sql = "SELECT
                    dr.id_detalle_reserva,
                    dr.id_producto,
                    p.codigo,
                    p.nombre,
                    p.precio_venta,
                    dr.cantidad,
                    (dr.cantidad * p.precio_venta) AS subtotal
                FROM detalle_reserva dr
                INNER JOIN productos p
                    ON dr.id_producto = p.id_producto
                WHERE dr.id_reserva = :id
                ORDER BY p.nombre ASC";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            ':id' => $idReserva
        ]);

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    // =====================================
    // LISTAR CLIENTES ACTIVOS
    // =====================================

    public function listarClientes()
    {
        $sql = "SELECT
                    id_cliente,
                    nombre_completo,
                    telefono,
                    correo
                FROM clientes
                WHERE activo = 1
                ORDER BY nombre_completo ASC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    // =====================================
    // LISTAR PRODUCTOS ACTIVOS
    // =====================================

    public function listarProductos()
    {
        $sql = "SELECT
                    id_producto,
                    codigo,
                    nombre,
                    precio_venta,
                    existencia
                FROM productos
                WHERE activo = 1
                ORDER BY nombre ASC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    // =====================================
    // FUNCIONES AUXILIARES DE INVENTARIO Y VENTAS
    // =====================================

    private function prepararDatosObservacion($valor)
    {
        if ($valor === null || $valor === '') {
            return [
                'registrado_por' => 'No disponible',
                'observaciones' => null,
                'inventario_descontado' => false
            ];
        }

        $datos = json_decode($valor, true);

        // Las reservaciones antiguas pueden tener texto plano.
        if (!is_array($datos)) {
            return [
                'registrado_por' => 'No disponible',
                'observaciones' => $valor,
                'inventario_descontado' => false
            ];
        }

        return [
            'registrado_por' => $datos['registrado_por'] ?? 'No disponible',
            'observaciones' => $datos['observaciones'] ?? null,
            'inventario_descontado' => !empty($datos['inventario_descontado'])
        ];
    }

    private function guardarDatosObservacion($datos)
    {
        $json = json_encode(
            $datos,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($json === false || strlen($json) > 500) {
            throw new Exception(
                'Las observaciones son demasiado extensas para guardar el control de la reservación.'
            );
        }

        return $json;
    }

    private function registrarMovimiento(
        $idProducto,
        $idUsuario,
        $tipo,
        $cantidad,
        $existenciaAnterior,
        $existenciaNueva,
        $referencia,
        $observaciones
    ) {
        $sql = "INSERT INTO movimientos_inventario (
                    id_producto,
                    id_usuario,
                    tipo,
                    cantidad,
                    existencia_anterior,
                    existencia_nueva,
                    referencia,
                    observaciones
                ) VALUES (
                    :producto,
                    :usuario,
                    :tipo,
                    :cantidad,
                    :anterior,
                    :nueva,
                    :referencia,
                    :observaciones
                )";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            ':producto' => $idProducto,
            ':usuario' => $idUsuario,
            ':tipo' => $tipo,
            ':cantidad' => $cantidad,
            ':anterior' => $existenciaAnterior,
            ':nueva' => $existenciaNueva,
            ':referencia' => $referencia,
            ':observaciones' => $observaciones
        ]);
    }

    // =====================================
    // REGISTRAR RESERVACION
    // =====================================

    public function registrarReservacion(
        $registradoPor,
        $idCliente,
        $fechaLimite,
        $observaciones,
        $productos,
        $cantidades,
        $idUsuario
    ) {
        try {
            $registradoPor = trim((string) $registradoPor);
            $observaciones = trim((string) ($observaciones ?? ''));

            if (
                $registradoPor === '' ||
                !filter_var($idCliente, FILTER_VALIDATE_INT) ||
                !filter_var($idUsuario, FILTER_VALIDATE_INT) ||
                !is_array($productos) ||
                !is_array($cantidades) ||
                empty($productos) ||
                count($productos) !== count($cantidades)
            ) {
                throw new Exception('Los datos de la reservación no son válidos.');
            }

            $productos = array_values($productos);
            $cantidades = array_values($cantidades);

            if (count(array_unique(array_map('strval', $productos))) !== count($productos)) {
                throw new Exception('No puedes agregar el mismo producto más de una vez.');
            }

            if ($fechaLimite !== '') {
                $fecha = DateTime::createFromFormat('Y-m-d', $fechaLimite);
                if (!$fecha || $fecha->format('Y-m-d') !== $fechaLimite) {
                    throw new Exception('La fecha límite de retiro no es válida.');
                }
            }

            $this->conexion->beginTransaction();

            $consultaCliente = $this->conexion->prepare(
                "SELECT id_cliente FROM clientes
                 WHERE id_cliente = :id AND activo = 1 FOR UPDATE"
            );
            $consultaCliente->execute([':id' => $idCliente]);
            if (!$consultaCliente->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception('El cliente no existe o está inactivo.');
            }

            // Primero se validan y bloquean todos los productos.
            $consultaProducto = $this->conexion->prepare(
                "SELECT id_producto, nombre, existencia
                 FROM productos
                 WHERE id_producto = :id AND activo = 1 FOR UPDATE"
            );

            $productosValidados = [];
            foreach ($productos as $indice => $idProducto) {
                $cantidad = filter_var($cantidades[$indice], FILTER_VALIDATE_INT);
                if (!filter_var($idProducto, FILTER_VALIDATE_INT) || $cantidad === false || $cantidad <= 0) {
                    throw new Exception('Uno de los productos o cantidades no es válido.');
                }

                $consultaProducto->execute([':id' => $idProducto]);
                $producto = $consultaProducto->fetch(PDO::FETCH_ASSOC);
                if (!$producto) {
                    throw new Exception('Uno de los productos no existe o está inactivo.');
                }
                if ((int) $producto['existencia'] < $cantidad) {
                    throw new Exception(
                        'Existencias insuficientes para "' . $producto['nombre'] . '". Disponibles: ' .
                        $producto['existencia'] . ', solicitadas: ' . $cantidad . '.'
                    );
                }

                $productosValidados[] = [
                    'id_producto' => (int) $idProducto,
                    'cantidad' => $cantidad,
                    'existencia' => (int) $producto['existencia'],
                    'nombre' => $producto['nombre']
                ];
            }

            $datosObservacion = [
                'registrado_por' => $registradoPor,
                'observaciones' => $observaciones !== '' ? $observaciones : null,
                'inventario_descontado' => true
            ];
            $observacionesJson = $this->guardarDatosObservacion($datosObservacion);

            $consultaReserva = $this->conexion->prepare(
                "INSERT INTO reservas (
                    id_cliente, fecha_limite_retiro, estado, observaciones
                 ) VALUES (:cliente, :fecha, 'PENDIENTE', :observaciones)"
            );
            $consultaReserva->execute([
                ':cliente' => $idCliente,
                ':fecha' => $fechaLimite !== '' ? $fechaLimite : null,
                ':observaciones' => $observacionesJson
            ]);
            $idReserva = $this->conexion->lastInsertId();

            $consultaDetalle = $this->conexion->prepare(
                "INSERT INTO detalle_reserva (id_reserva, id_producto, cantidad)
                 VALUES (:reserva, :producto, :cantidad)"
            );
            $consultaActualizarStock = $this->conexion->prepare(
                "UPDATE productos SET existencia = :nueva WHERE id_producto = :id"
            );

            foreach ($productosValidados as $producto) {
                $nuevaExistencia = $producto['existencia'] - $producto['cantidad'];

                $consultaDetalle->execute([
                    ':reserva' => $idReserva,
                    ':producto' => $producto['id_producto'],
                    ':cantidad' => $producto['cantidad']
                ]);
                $consultaActualizarStock->execute([
                    ':nueva' => $nuevaExistencia,
                    ':id' => $producto['id_producto']
                ]);

                $this->registrarMovimiento(
                    $producto['id_producto'], $idUsuario, 'AJUSTE_SALIDA',
                    $producto['cantidad'], $producto['existencia'], $nuevaExistencia,
                    'RESERVA #' . $idReserva, 'Salida por creación de reservación'
                );
            }

            $this->conexion->commit();

            return [
                'exito' => true,
                'mensaje' => 'Reservación registrada correctamente y existencias descontadas.',
                'id_reserva' => $idReserva
            ];
        } catch (Exception $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            return ['exito' => false, 'mensaje' => $e->getMessage()];
        }
    }

    // =====================================
    // ACTUALIZAR ESTADO DE RESERVACION
    // =====================================

    public function actualizarEstado($idReserva, $estado, $idUsuario)
    {
        try {
            $estadosPermitidos = ['PENDIENTE', 'CONFIRMADA', 'ENTREGADA', 'CANCELADA'];
            if (
                !filter_var($idReserva, FILTER_VALIDATE_INT) ||
                !in_array($estado, $estadosPermitidos, true) ||
                !filter_var($idUsuario, FILTER_VALIDATE_INT)
            ) {
                throw new Exception('Los datos para actualizar la reservación no son válidos.');
            }

            $this->conexion->beginTransaction();

            $consultaReserva = $this->conexion->prepare(
                "SELECT id_reserva, id_cliente, estado, observaciones
                 FROM reservas WHERE id_reserva = :id FOR UPDATE"
            );
            $consultaReserva->execute([':id' => $idReserva]);
            $reserva = $consultaReserva->fetch(PDO::FETCH_ASSOC);

            if (!$reserva) {
                throw new Exception('La reservación no existe.');
            }

            $estadoActual = $reserva['estado'];
            if ($estadoActual === $estado) {
                throw new Exception('La reservación ya se encuentra en ese estado.');
            }

            $transiciones = [
                'PENDIENTE' => ['CONFIRMADA', 'CANCELADA', 'ENTREGADA'],
                'CONFIRMADA' => ['PENDIENTE', 'CANCELADA', 'ENTREGADA'],
                'CANCELADA' => ['PENDIENTE', 'CONFIRMADA'],
                'ENTREGADA' => []
            ];

            if (!in_array($estado, $transiciones[$estadoActual] ?? [], true)) {
                throw new Exception(
                    $estadoActual === 'ENTREGADA'
                        ? 'Una reservación entregada no puede modificarse.'
                        : 'No está permitido cambiar de ' . $estadoActual . ' a ' . $estado . '.'
                );
            }

            $datosObservacion = $this->prepararDatosObservacion($reserva['observaciones']);
            $inventarioDescontado = $datosObservacion['inventario_descontado'];

            $consultaDetalles = $this->conexion->prepare(
                "SELECT dr.id_producto, dr.cantidad, p.nombre, p.existencia,
                        p.precio_venta, p.precio_compra
                 FROM detalle_reserva dr
                 INNER JOIN productos p ON p.id_producto = dr.id_producto
                 WHERE dr.id_reserva = :id
                 ORDER BY dr.id_producto ASC FOR UPDATE"
            );
            $consultaDetalles->execute([':id' => $idReserva]);
            $detalles = $consultaDetalles->fetchAll(PDO::FETCH_ASSOC);

            if (empty($detalles)) {
                throw new Exception('La reservación no contiene productos.');
            }

            $consultaStock = $this->conexion->prepare(
                "UPDATE productos SET existencia = :nueva WHERE id_producto = :id"
            );

            // CANCELAR: devolver existencias solo si esta reserva las había descontado.
            if ($estado === 'CANCELADA' && $inventarioDescontado) {
                foreach ($detalles as $detalle) {
                    $anterior = (int) $detalle['existencia'];
                    $cantidad = (int) $detalle['cantidad'];
                    $nueva = $anterior + $cantidad;

                    $consultaStock->execute([
                        ':nueva' => $nueva,
                        ':id' => $detalle['id_producto']
                    ]);
                    $this->registrarMovimiento(
                        $detalle['id_producto'], $idUsuario, 'AJUSTE_ENTRADA',
                        $cantidad, $anterior, $nueva,
                        'RESERVA #' . $idReserva, 'Devolución por cancelación de reservación'
                    );
                }
                $inventarioDescontado = false;
            }

            // REACTIVAR: comprobar todos los productos antes de descontar cualquiera.
            if ($estadoActual === 'CANCELADA' && in_array($estado, ['PENDIENTE', 'CONFIRMADA'], true)) {
                foreach ($detalles as $detalle) {
                    if ((int) $detalle['existencia'] < (int) $detalle['cantidad']) {
                        throw new Exception(
                            'No hay existencias suficientes para reactivar la reservación. Producto: ' .
                            $detalle['nombre'] . '. Disponibles: ' . $detalle['existencia'] .
                            ', necesarias: ' . $detalle['cantidad'] . '.'
                        );
                    }
                }

                foreach ($detalles as $detalle) {
                    $anterior = (int) $detalle['existencia'];
                    $cantidad = (int) $detalle['cantidad'];
                    $nueva = $anterior - $cantidad;
                    $consultaStock->execute([
                        ':nueva' => $nueva,
                        ':id' => $detalle['id_producto']
                    ]);
                    $this->registrarMovimiento(
                        $detalle['id_producto'], $idUsuario, 'AJUSTE_SALIDA',
                        $cantidad, $anterior, $nueva,
                        'RESERVA #' . $idReserva, 'Salida por reactivación de reservación'
                    );
                }
                $inventarioDescontado = true;
            }

            // ENTREGAR: crear venta. Si es una reserva antigua sin descuento registrado,
            // descontar aquí; si ya estaba descontada, no volver a tocar el inventario.
            if ($estado === 'ENTREGADA') {
                if (!$inventarioDescontado) {
                    foreach ($detalles as $detalle) {
                        if ((int) $detalle['existencia'] < (int) $detalle['cantidad']) {
                            throw new Exception(
                                'No hay existencias suficientes para entregar esta reservación. Producto: ' .
                                $detalle['nombre'] . '. Disponibles: ' . $detalle['existencia'] .
                                ', necesarias: ' . $detalle['cantidad'] . '.'
                            );
                        }
                    }
                    foreach ($detalles as $detalle) {
                        $anterior = (int) $detalle['existencia'];
                        $cantidad = (int) $detalle['cantidad'];
                        $nueva = $anterior - $cantidad;
                        $consultaStock->execute([
                            ':nueva' => $nueva,
                            ':id' => $detalle['id_producto']
                        ]);
                        $this->registrarMovimiento(
                            $detalle['id_producto'], $idUsuario, 'SALIDA_VENTA',
                            $cantidad, $anterior, $nueva,
                            'RESERVA #' . $idReserva, 'Salida al entregar reservación antigua'
                        );
                    }
                    $inventarioDescontado = true;
                }

                $consultaVenta = $this->conexion->prepare(
                    "INSERT INTO ventas (id_cliente, id_usuario, observaciones, estado)
                     VALUES (:cliente, :usuario, :observaciones, 'COMPLETADA')"
                );
                $consultaVenta->execute([
                    ':cliente' => $reserva['id_cliente'],
                    ':usuario' => $idUsuario,
                    ':observaciones' => 'Venta generada por entrega de reservación #' . $idReserva
                ]);
                $idVenta = $this->conexion->lastInsertId();

                $consultaDetalleVenta = $this->conexion->prepare(
                    "INSERT INTO detalle_venta (
                        id_venta, id_producto, cantidad, precio_unitario, costo_unitario
                     ) VALUES (:venta, :producto, :cantidad, :precio, :costo)"
                );

                foreach ($detalles as $detalle) {
                    $consultaDetalleVenta->execute([
                        ':venta' => $idVenta,
                        ':producto' => $detalle['id_producto'],
                        ':cantidad' => $detalle['cantidad'],
                        ':precio' => $detalle['precio_venta'],
                        ':costo' => $detalle['precio_compra']
                    ]);
                }
            }

            $datosObservacion['inventario_descontado'] = $inventarioDescontado;
            $observacionesJson = $this->guardarDatosObservacion($datosObservacion);

            $consultaActualizar = $this->conexion->prepare(
                "UPDATE reservas SET estado = :estado, observaciones = :observaciones
                 WHERE id_reserva = :id"
            );
            $consultaActualizar->execute([
                ':estado' => $estado,
                ':observaciones' => $observacionesJson,
                ':id' => $idReserva
            ]);

            $this->conexion->commit();

            return [
                'exito' => true,
                'mensaje' => $estado === 'ENTREGADA'
                    ? 'Reservación entregada y venta registrada correctamente.'
                    : 'Estado actualizado correctamente.'
            ];
        } catch (Exception $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            return ['exito' => false, 'mensaje' => $e->getMessage()];
        }
    }
}
