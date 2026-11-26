<?php
$nombreUsuario = $_SESSION['usuario']['nombre_completo'] ?? 'Administrador';
$correoUsuario = $_SESSION['usuario']['correo'] ?? 'admin@papelito.com';
$moneda = static fn($valor) => '$' . number_format((float) $valor, 2, ',', '.');
$entero = static fn($valor) => number_format((int) $valor, 0, ',', '.');
$esInventario = in_array($tipo, ['control_inventario', 'baja_existencia'], true);
$descripcion = [
    'ganancias_mes' => 'Consulta las ventas, costos y ganancias obtenidas durante el período seleccionado.',
    'productos_mas_vendidos' => 'Productos con mayor cantidad de unidades vendidas durante el período.',
    'historial_ventas' => 'Consulta las ventas registradas, el usuario responsable y el total de cada operación.',
    'historial_compras' => 'Consulta las compras registradas, sus proveedores y el total de cada operación.',
    'control_inventario' => 'Consulta los códigos, productos, existencias y disponibilidad actual.',
    'baja_existencia' => 'Identifica los productos cuya existencia es igual o inferior al mínimo establecido.'
][$tipo] ?? '';
$opciones = [
    'ventas' => [
        ['tipo' => 'ganancias_mes', 'titulo' => 'Ganancias del mes'],
        ['tipo' => 'productos_mas_vendidos', 'titulo' => 'Productos más vendidos'],
        ['tipo' => 'historial_ventas', 'titulo' => 'Historial de ventas']
    ],
    'compras' => [['tipo' => 'historial_compras', 'titulo' => 'Historial de compras']],
    'inventario' => [
        ['tipo' => 'control_inventario', 'titulo' => 'Control de inventario'],
        ['tipo' => 'baja_existencia', 'titulo' => 'Baja existencia']
    ]
];
$periodoTexto = date('d/m/Y', strtotime($desde)) . ' – ' . date('d/m/Y', strtotime($hasta));
$baseDetalle = '/el_papelito/public/reportes/detalle';
$exportUrl = '/el_papelito/public/reportes/exportar?' . http_build_query(['reporte' => $categoria, 'tipo' => $tipo, 'desde' => $desde, 'hasta' => $hasta]);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?> | Reportes</title>
    <link rel="stylesheet" href="/el_papelito/public/assets/css/reportes.css">
</head>

<body>
    <div class="app-layout">
        <aside class="sidebar">
            <a class="sidebar-brand" href="/el_papelito/public/dashboard">
                <img src="/el_papelito/public/assets/img/logo_el_papelito.jpeg" alt="Logo El Papelito">
                <span><small>MINI LIBRERÍA</small><strong>EL PAPELITO</strong></span>
            </a>
            <div class="sidebar-section-label">MENÚ PRINCIPAL</div>
            <nav class="sidebar-nav" aria-label="Menú principal">
                <a href="/el_papelito/public/dashboard">Resumen</a>
                <a href="/el_papelito/public/productos">Inventario</a>
                <a href="/el_papelito/public/productos">Productos</a>
                <a href="/el_papelito/public/compras">Compras</a>
                <a href="#" title="Módulo de ventas">Ventas</a>
                <a href="#" title="Módulo de reservas">Reservas</a>
                <a href="/el_papelito/public/proveedores">Proveedores</a>
                <a href="/el_papelito/public/clientes">Clientes</a>
                <a href="#" title="Módulo de usuarios">Usuarios</a>
                <a class="active" href="/el_papelito/public/reportes">Reportes</a>
            </nav>
            <div class="sidebar-session">
                <span class="sidebar-section-label">SESIÓN INICIADA</span>
                <div class="session-user"><span
                        class="avatar-letter"><?= htmlspecialchars(strtoupper(substr($nombreUsuario, 0, 1))) ?></span><span><strong><?= htmlspecialchars($nombreUsuario) ?></strong><small><?= htmlspecialchars($correoUsuario) ?></small></span>
                </div>
            </div>
        </aside>

        <main class="main-area">
            <header class="topbar">
                <div class="topbar-brand"><strong>Mini Librería El Papelito</strong><span>Panel de administración</span>
                </div>
                <div class="topbar-user"><span class="notification-dot"></span>
                    <div><strong><?= htmlspecialchars($nombreUsuario) ?></strong><span>Mi cuenta · <a
                                href="/el_papelito/public/logout">Salir</a></span></div>
                </div>
            </header>

            <section class="page-content detail-page">
                <a class="back-link" href="/el_papelito/public/reportes">← Volver a Reportes</a>
                <div class="page-heading detail-heading"><span
                        class="eyebrow"><?= htmlspecialchars(ucfirst($categoria)) ?></span>
                    <h1><?= htmlspecialchars($titulo) ?></h1>
                    <p><?= htmlspecialchars($descripcion) ?></p>
                </div>

                <nav class="detail-tabs" aria-label="Consultas del reporte">
                    <?php foreach ($opciones[$categoria] as $opcion): ?>
                        <a class="detail-tab <?= $opcion['tipo'] === $tipo ? 'active' : '' ?>"
                            href="<?= $baseDetalle ?>?<?= http_build_query(['reporte' => $categoria, 'tipo' => $opcion['tipo'], 'desde' => $desde, 'hasta' => $hasta]) ?>"><?= htmlspecialchars($opcion['titulo']) ?></a>
                    <?php endforeach; ?>
                </nav>

                <section class="detail-card">
                    <div class="detail-card-heading">
                        <div>
                            <h2>Detalle del reporte</h2>
                            <p><?= $esInventario ? 'Información actual del inventario.' : 'Período consultado: ' . htmlspecialchars($periodoTexto) ?>
                            </p>
                        </div>
                        <a class="btn-primary btn-export" href="<?= htmlspecialchars($exportUrl) ?>">Exportar
                            reporte</a>
                    </div>

                    <?php if (!$esInventario): ?>
                        <form class="date-filters" method="GET" action="/el_papelito/public/reportes/detalle">
                            <input type="hidden" name="reporte" value="<?= htmlspecialchars($categoria) ?>">
                            <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo) ?>">
                            <label>Fecha desde<input type="date" name="desde" value="<?= htmlspecialchars($desde) ?>"
                                    required></label>
                            <label>Fecha hasta<input type="date" name="hasta" value="<?= htmlspecialchars($hasta) ?>"
                                    required></label>
                            <button class="btn-primary" type="submit">Consultar</button>
                        </form>
                    <?php endif; ?>

                    <?php if ($tipo === 'ganancias_mes'): ?>
                        <?php $r = $datos['resumen']; ?>
                        <div class="metric-grid three-metrics">
                            <article class="metric-card"><span>Ventas del
                                    período</span><strong><?= $moneda($r['ventas'] ?? 0) ?></strong></article>
                            <article class="metric-card">
                                <span>Costos</span><strong><?= $moneda($r['costos'] ?? 0) ?></strong></article>
                            <article class="metric-card metric-highlight">
                                <span>Ganancias</span><strong><?= $moneda($r['ganancias'] ?? 0) ?></strong></article>
                        </div>
                        <div class="table-responsive">
                            <table class="reports-table detail-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Operación</th>
                                        <th>Usuario</th>
                                        <th>Ventas</th>
                                        <th>Costos</th>
                                        <th>Ganancias</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($datos['detalle'] as $fila): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i', strtotime($fila['fecha_venta'])) ?></td>
                                            <td>Venta #<?= (int) $fila['id_venta'] ?></td>
                                            <td><?= htmlspecialchars($fila['nombre_completo']) ?></td>
                                            <td><?= $moneda($fila['ventas']) ?></td>
                                            <td><?= $moneda($fila['costos']) ?></td>
                                            <td class="text-positive"><?= $moneda($fila['ganancias']) ?></td>
                                        </tr><?php endforeach; ?>
                                    <?php if (empty($datos['detalle'])): ?>
                                        <tr>
                                            <td colspan="6" class="empty-state">No hay ventas completadas en el período
                                                seleccionado.</td>
                                        </tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    <?php elseif ($tipo === 'productos_mas_vendidos'): ?>
                        <div class="table-responsive">
                            <table class="reports-table detail-table">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Código</th>
                                        <th>Unidades vendidas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($datos as $fila): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($fila['producto']) ?></td>
                                            <td><?= htmlspecialchars($fila['codigo']) ?></td>
                                            <td><?= $entero($fila['unidades_vendidas']) ?></td>
                                        </tr><?php endforeach; ?>
                                    <?php if (empty($datos)): ?>
                                        <tr>
                                            <td colspan="3" class="empty-state">No hay productos vendidos en el período
                                                seleccionado.</td>
                                        </tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    <?php elseif ($tipo === 'historial_ventas'): ?>
                        <div class="table-responsive">
                            <table class="reports-table detail-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Operación</th>
                                        <th>Usuario</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($datos as $fila): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i', strtotime($fila['fecha_venta'])) ?></td>
                                            <td>Venta #<?= (int) $fila['id_venta'] ?></td>
                                            <td><?= htmlspecialchars($fila['usuario']) ?></td>
                                            <td><?= $moneda($fila['total']) ?></td>
                                        </tr><?php endforeach; ?>
                                    <?php if (empty($datos)): ?>
                                        <tr>
                                            <td colspan="4" class="empty-state">No hay ventas registradas en el período
                                                seleccionado.</td>
                                        </tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    <?php elseif ($tipo === 'historial_compras'): ?>
                        <div class="table-responsive">
                            <table class="reports-table detail-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Operación</th>
                                        <th>Proveedor</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($datos as $fila): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i', strtotime($fila['fecha_compra'])) ?></td>
                                            <td>Compra #<?= (int) $fila['id_compra'] ?></td>
                                            <td><?= htmlspecialchars($fila['proveedor']) ?></td>
                                            <td><?= $moneda($fila['total']) ?></td>
                                        </tr><?php endforeach; ?>
                                    <?php if (empty($datos)): ?>
                                        <tr>
                                            <td colspan="4" class="empty-state">No hay compras registradas en el período
                                                seleccionado.</td>
                                        </tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    <?php elseif ($tipo === 'control_inventario'): ?>
                        <?php
                        // Resumen dinámico calculado a partir de los productos
                        // que ya devuelve la consulta de control de inventario.
                        $totalProductosInventario = count($datos);
                        $totalUnidadesInventario = 0;

                        foreach ($datos as $productoInventario) {
                            $totalUnidadesInventario += (int) ($productoInventario['existencia'] ?? 0);
                        }
                        ?>
                        <div class="metric-grid two-metrics inventory-metrics">
                            <article class="metric-card">
                                <span>Productos activos</span>
                                <strong><?= $entero($totalProductosInventario) ?></strong>
                            </article>
                            <article class="metric-card metric-highlight">
                                <span>Unidades disponibles en inventario</span>
                                <strong><?= $entero($totalUnidadesInventario) ?></strong>
                            </article>
                        </div>
                        <div class="table-responsive">
                            <table class="reports-table detail-table">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Producto</th>
                                        <th>Existencia</th>
                                        <th>Disponibilidad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($datos as $fila): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($fila['codigo']) ?></td>
                                            <td><?= htmlspecialchars($fila['producto']) ?></td>
                                            <td><?= $entero($fila['existencia']) ?></td>
                                            <td><span
                                                    class="status-pill <?= $fila['disponibilidad'] === 'Agotado' ? 'status-low' : '' ?>"><?= htmlspecialchars($fila['disponibilidad']) ?></span>
                                            </td>
                                        </tr><?php endforeach; ?>
                                    <?php if (empty($datos)): ?>
                                        <tr>
                                            <td colspan="4" class="empty-state">No hay productos activos registrados.</td>
                                        </tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    <?php elseif ($tipo === 'baja_existencia'): ?>
                        <div class="metric-grid one-metric">
                            <article class="metric-card metric-highlight"><span>Productos con baja
                                    existencia</span><strong><?= $entero(count($datos)) ?></strong></article>
                        </div>
                        <div class="table-responsive">
                            <table class="reports-table detail-table">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Existencia actual</th>
                                        <th>Mínimo establecido</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($datos as $fila): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($fila['producto']) ?></td>
                                            <td class="text-warning"><?= $entero($fila['existencia']) ?></td>
                                            <td><?= $entero($fila['stock_minimo']) ?></td>
                                        </tr><?php endforeach; ?>
                                    <?php if (empty($datos)): ?>
                                        <tr>
                                            <td colspan="3" class="empty-state">No hay productos con baja existencia.</td>
                                        </tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>
            </section>
        </main>
    </div>
</body>

</html>