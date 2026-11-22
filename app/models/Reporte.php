<?php

class Reporte
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    private function ejecutar(string $sql, array $parametros = []): PDOStatement
    {
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($parametros);
        return $consulta;
    }

    /** Resumen de las tres filas que aparecen en la vista principal. */
    public function obtenerResumenPrincipal(string $desde, string $hasta): array
    {
        $ventas = $this->ejecutar(
            "SELECT COUNT(DISTINCT v.id_venta) AS registros,
                    COALESCE(SUM(dv.subtotal), 0) AS resultado
             FROM ventas v
             LEFT JOIN detalle_venta dv ON dv.id_venta = v.id_venta
             WHERE v.estado = 'COMPLETADA'
               AND v.fecha_venta >= :desde AND v.fecha_venta <= :hasta",
            [':desde' => $desde . ' 00:00:00', ':hasta' => $hasta . ' 23:59:59']
        )->fetch(PDO::FETCH_ASSOC);

        $compras = $this->ejecutar(
            "SELECT COUNT(DISTINCT c.id_compra) AS registros,
                    COALESCE(SUM(dc.subtotal), 0) AS resultado
             FROM compras c
             LEFT JOIN detalle_compra dc ON dc.id_compra = c.id_compra
             WHERE c.estado = 'RECIBIDA'
               AND c.fecha_compra >= :desde AND c.fecha_compra <= :hasta",
            [':desde' => $desde . ' 00:00:00', ':hasta' => $hasta . ' 23:59:59']
        )->fetch(PDO::FETCH_ASSOC);

        $inventario = $this->ejecutar(
            "SELECT COUNT(*) AS registros,
                    COALESCE(SUM(existencia), 0) AS resultado
             FROM productos
             WHERE activo = 1"
        )->fetch(PDO::FETCH_ASSOC);

        return [
            [
                'codigo' => 'VTA',
                'reporte' => 'Ventas',
                'periodo' => $this->nombrePeriodo($desde),
                'registros' => (int) ($ventas['registros'] ?? 0),
                'resultado' => (float) ($ventas['resultado'] ?? 0),
                'formato' => 'moneda',
                'estado' => 'Generado',
                'url' => '/el_papelito/public/reportes/detalle?reporte=ventas&tipo=ganancias_mes'
            ],
            [
                'codigo' => 'COM',
                'reporte' => 'Compras',
                'periodo' => $this->nombrePeriodo($desde),
                'registros' => (int) ($compras['registros'] ?? 0),
                'resultado' => (float) ($compras['resultado'] ?? 0),
                'formato' => 'moneda',
                'estado' => 'Generado',
                'url' => '/el_papelito/public/reportes/detalle?reporte=compras&tipo=historial_compras'
            ],
            [
                'codigo' => 'INV',
                'reporte' => 'Inventario',
                'periodo' => 'Actual',
                'registros' => (int) ($inventario['registros'] ?? 0),
                'resultado' => (int) ($inventario['resultado'] ?? 0),
                'formato' => 'unidades',
                'estado' => 'Generado',
                'url' => '/el_papelito/public/reportes/detalle?reporte=inventario&tipo=control_inventario'
            ]
        ];
    }

    public function obtenerGanancias(string $desde, string $hasta): array
    {
        $resumen = $this->ejecutar(
            "SELECT COALESCE(SUM(dv.subtotal), 0) AS ventas,
                    COALESCE(SUM(dv.cantidad * dv.costo_unitario), 0) AS costos,
                    COALESCE(SUM(dv.utilidad), 0) AS ganancias
             FROM ventas v
             INNER JOIN detalle_venta dv ON dv.id_venta = v.id_venta
             WHERE v.estado = 'COMPLETADA'
               AND v.fecha_venta >= :desde AND v.fecha_venta <= :hasta",
            [':desde' => $desde . ' 00:00:00', ':hasta' => $hasta . ' 23:59:59']
        )->fetch(PDO::FETCH_ASSOC);

        $detalle = $this->ejecutar(
            "SELECT v.id_venta, v.fecha_venta, u.nombre_completo,
                    COALESCE(SUM(dv.subtotal), 0) AS ventas,
                    COALESCE(SUM(dv.cantidad * dv.costo_unitario), 0) AS costos,
                    COALESCE(SUM(dv.utilidad), 0) AS ganancias
             FROM ventas v
             INNER JOIN usuarios u ON u.id_usuario = v.id_usuario
             LEFT JOIN detalle_venta dv ON dv.id_venta = v.id_venta
             WHERE v.estado = 'COMPLETADA'
               AND v.fecha_venta >= :desde AND v.fecha_venta <= :hasta
             GROUP BY v.id_venta, v.fecha_venta, u.nombre_completo
             ORDER BY v.fecha_venta DESC, v.id_venta DESC",
            [':desde' => $desde . ' 00:00:00', ':hasta' => $hasta . ' 23:59:59']
        )->fetchAll(PDO::FETCH_ASSOC);

        return ['resumen' => $resumen, 'detalle' => $detalle];
    }

    public function obtenerProductosMasVendidos(string $desde, string $hasta): array
    {
        return $this->ejecutar(
            "SELECT p.codigo, p.nombre AS producto,
                    COALESCE(c.nombre, 'Sin categoría') AS categoria,
                    SUM(dv.cantidad) AS unidades_vendidas,
                    SUM(dv.subtotal) AS total_vendido
             FROM detalle_venta dv
             INNER JOIN ventas v ON v.id_venta = dv.id_venta
             INNER JOIN productos p ON p.id_producto = dv.id_producto
             LEFT JOIN categorias c ON c.id_categoria = p.id_categoria
             WHERE v.estado = 'COMPLETADA'
               AND v.fecha_venta >= :desde AND v.fecha_venta <= :hasta
             GROUP BY p.id_producto, p.codigo, p.nombre, c.nombre
             ORDER BY unidades_vendidas DESC, p.nombre ASC",
            [':desde' => $desde . ' 00:00:00', ':hasta' => $hasta . ' 23:59:59']
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerHistorialVentas(string $desde, string $hasta): array
    {
        return $this->ejecutar(
            "SELECT v.id_venta, v.fecha_venta, u.nombre_completo AS usuario,
                    v.estado, COALESCE(SUM(dv.subtotal), 0) AS total
             FROM ventas v
             INNER JOIN usuarios u ON u.id_usuario = v.id_usuario
             LEFT JOIN detalle_venta dv ON dv.id_venta = v.id_venta
             WHERE v.fecha_venta >= :desde AND v.fecha_venta <= :hasta
             GROUP BY v.id_venta, v.fecha_venta, u.nombre_completo, v.estado
             ORDER BY v.fecha_venta DESC, v.id_venta DESC",
            [':desde' => $desde . ' 00:00:00', ':hasta' => $hasta . ' 23:59:59']
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerHistorialCompras(string $desde, string $hasta): array
    {
        return $this->ejecutar(
            "SELECT c.id_compra, c.fecha_compra, c.numero_documento,
                    pr.nombre_empresa AS proveedor, u.nombre_completo AS usuario,
                    c.estado, COALESCE(SUM(dc.subtotal), 0) AS total
             FROM compras c
             INNER JOIN proveedores pr ON pr.id_proveedor = c.id_proveedor
             INNER JOIN usuarios u ON u.id_usuario = c.id_usuario
             LEFT JOIN detalle_compra dc ON dc.id_compra = c.id_compra
             WHERE c.fecha_compra >= :desde AND c.fecha_compra <= :hasta
             GROUP BY c.id_compra, c.fecha_compra, c.numero_documento,
                      pr.nombre_empresa, u.nombre_completo, c.estado
             ORDER BY c.fecha_compra DESC, c.id_compra DESC",
            [':desde' => $desde . ' 00:00:00', ':hasta' => $hasta . ' 23:59:59']
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerControlInventario(): array
    {
        return $this->ejecutar(
            "SELECT p.codigo, p.nombre AS producto, p.existencia,
                    CASE WHEN p.existencia > 0 THEN 'Disponible' ELSE 'Agotado' END AS disponibilidad
             FROM productos p
             WHERE p.activo = 1
             ORDER BY p.nombre ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerBajaExistencia(): array
    {
        return $this->ejecutar(
            "SELECT p.codigo, p.nombre AS producto, p.existencia,
                    p.stock_minimo
             FROM productos p
             WHERE p.activo = 1 AND p.existencia <= p.stock_minimo
             ORDER BY p.existencia ASC, p.nombre ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    private function nombrePeriodo(string $fecha): string
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        $marca = strtotime($fecha);
        return $meses[(int) date('n', $marca)] . ' ' . date('Y', $marca);
    }
}
