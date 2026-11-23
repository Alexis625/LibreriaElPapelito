
<?php

class MovimientoInventario
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    // ==========================================
    // LISTAR MOVIMIENTOS DE INVENTARIO
    // ==========================================

    public function listarMovimientos(
        $busqueda = '',
        $tipo = '',
        $fechaInicio = '',
        $fechaFin = '',
        $limite = 10,
        $offset = 0
    ) {
        $sql = "SELECT
                    m.id_movimiento,
                    m.id_producto,
                    m.id_usuario,
                    m.tipo,
                    m.cantidad,
                    m.existencia_anterior,
                    m.existencia_nueva,
                    m.fecha_movimiento,
                    m.referencia,
                    m.observaciones,
                    p.codigo,
                    p.nombre AS producto,
                    u.nombre_completo AS usuario
                FROM movimientos_inventario m
                INNER JOIN productos p
                    ON m.id_producto = p.id_producto
                INNER JOIN usuarios u
                    ON m.id_usuario = u.id_usuario
                WHERE (
                    p.codigo LIKE :busqueda
                    OR p.nombre LIKE :busqueda
                    OR u.nombre_completo LIKE :busqueda
                    OR m.referencia LIKE :busqueda
                )";

        $parametros = [
            ':busqueda' => "%{$busqueda}%"
        ];

        $tiposValidos = [
            'ENTRADA_COMPRA',
            'SALIDA_VENTA',
            'AJUSTE_ENTRADA',
            'AJUSTE_SALIDA'
        ];

        if (in_array($tipo, $tiposValidos, true)) {
            $sql .= " AND m.tipo = :tipo";
            $parametros[':tipo'] = $tipo;
        }

        if ($fechaInicio !== '') {
            $sql .= " AND m.fecha_movimiento >= :fecha_inicio";
            $parametros[':fecha_inicio'] = $fechaInicio . ' 00:00:00';
        }

        if ($fechaFin !== '') {
            $sql .= " AND m.fecha_movimiento <= :fecha_fin";
            $parametros[':fecha_fin'] = $fechaFin . ' 23:59:59';
        }

        $sql .= " ORDER BY m.fecha_movimiento DESC, m.id_movimiento DESC
                  LIMIT " . max(1, (int)$limite) . "
                  OFFSET " . max(0, (int)$offset);

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($parametros);

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==========================================
    // CONTAR MOVIMIENTOS
    // ==========================================

    public function contarMovimientos(
        $busqueda = '',
        $tipo = '',
        $fechaInicio = '',
        $fechaFin = ''
    ) {
        $sql = "SELECT COUNT(*)
                FROM movimientos_inventario m
                INNER JOIN productos p
                    ON m.id_producto = p.id_producto
                INNER JOIN usuarios u
                    ON m.id_usuario = u.id_usuario
                WHERE (
                    p.codigo LIKE :busqueda
                    OR p.nombre LIKE :busqueda
                    OR u.nombre_completo LIKE :busqueda
                    OR m.referencia LIKE :busqueda
                )";

        $parametros = [
            ':busqueda' => "%{$busqueda}%"
        ];

        $tiposValidos = [
            'ENTRADA_COMPRA',
            'SALIDA_VENTA',
            'AJUSTE_ENTRADA',
            'AJUSTE_SALIDA'
        ];

        if (in_array($tipo, $tiposValidos, true)) {
            $sql .= " AND m.tipo = :tipo";
            $parametros[':tipo'] = $tipo;
        }

        if ($fechaInicio !== '') {
            $sql .= " AND m.fecha_movimiento >= :fecha_inicio";
            $parametros[':fecha_inicio'] = $fechaInicio . ' 00:00:00';
        }

        if ($fechaFin !== '') {
            $sql .= " AND m.fecha_movimiento <= :fecha_fin";
            $parametros[':fecha_fin'] = $fechaFin . ' 23:59:59';
        }

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($parametros);

        return (int)$consulta->fetchColumn();
    }

    public function obtenerResumen()
{
    $sql = "SELECT
                COUNT(*) AS total_movimientos,

                COALESCE(SUM(
                    CASE
                        WHEN tipo = 'ENTRADA_COMPRA'
                        THEN cantidad
                        ELSE 0
                    END
                ), 0) AS total_entradas,

                COALESCE(SUM(
                    CASE
                        WHEN tipo = 'SALIDA_VENTA'
                        THEN cantidad
                        ELSE 0
                    END
                ), 0) AS total_salidas,

                COALESCE(SUM(
                    CASE
                        WHEN tipo IN ('AJUSTE_ENTRADA', 'AJUSTE_SALIDA')
                        THEN 1
                        ELSE 0
                    END
                ), 0) AS total_ajustes

            FROM movimientos_inventario";

    $consulta = $this->conexion->prepare($sql);
    $consulta->execute();

    return $consulta->fetch(PDO::FETCH_ASSOC);
}
}
