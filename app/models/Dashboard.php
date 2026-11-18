
<?php

class Dashboard {

    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Obtener los indicadores principales
    public function obtenerResumen() {

        $sql = "SELECT

                    (SELECT COUNT(*)
                     FROM productos
                     WHERE activo = 1) AS total_productos,

                    (SELECT COUNT(*)
                     FROM productos
                     WHERE activo = 1
                     AND existencia <= stock_minimo) AS stock_bajo,

                    (SELECT COALESCE(SUM(dv.subtotal), 0)
                     FROM ventas v
                     INNER JOIN detalle_venta dv
                        ON v.id_venta = dv.id_venta
                     WHERE v.estado = 'COMPLETADA'
                     AND DATE(v.fecha_venta) = CURDATE()) AS ventas_dia,

                    (SELECT COUNT(*)
                     FROM reservas
                     WHERE estado = 'PENDIENTE') AS reservas_pendientes";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();

        return $consulta->fetch(PDO::FETCH_ASSOC);
    }


// Obtener las ventas de la semana actual
public function obtenerVentasSemana() {

    $sql = "SELECT
                WEEKDAY(v.fecha_venta) AS dia,
                SUM(dv.subtotal) AS total
            FROM ventas v
            INNER JOIN detalle_venta dv
                ON v.id_venta = dv.id_venta
            WHERE v.estado = 'COMPLETADA'
            AND DATE(v.fecha_venta) >=
                DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
            AND DATE(v.fecha_venta) <
                DATE_ADD(
                    DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY),
                    INTERVAL 7 DAY
                )
            GROUP BY WEEKDAY(v.fecha_venta)
            ORDER BY dia";

    $consulta = $this->conexion->prepare($sql);
    $consulta->execute();

    return $consulta->fetchAll(PDO::FETCH_KEY_PAIR);
}


// Obtener productos con stock bajo
public function obtenerProductosStockBajo() {

    $sql = "SELECT
                nombre,
                existencia,
                stock_minimo
            FROM productos
            WHERE activo = 1
            AND existencia <= stock_minimo
            ORDER BY existencia ASC
            LIMIT 5";

    $consulta = $this->conexion->prepare($sql);
    $consulta->execute();

    return $consulta->fetchAll(PDO::FETCH_ASSOC);
}

}
