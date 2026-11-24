
<?php
$nombre = $_SESSION['usuario']['nombre_completo'] ?? 'Administrador';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard | Mini Librería El Papelito</title>

    <link rel="stylesheet" href="/el_papelito/public/assets/css/layouts.css">

    <link rel="stylesheet" href="/el_papelito/public/assets/css/dashboard.css">
</head>

<body>

    <div class="app-layout">

        <?php
        $moduloActual = 'dashboard';
        require __DIR__ . '/../layouts/sidebar.php';
        ?>

        <main class="main-area">

            <?php require __DIR__ . '/../layouts/topbar.php'; ?>

            <!-- Contenido principal -->
            <section class="dashboard-content">

                <!-- Bienvenida -->
                <div class="welcome-section">
                    <h1>¡Hola! Bienvenido a tu librería</h1>
                    <p>Un vistazo rápido a la actividad de El Papelito.</p>
                </div>

                <!-- Indicadores -->
                <section class="dashboard-stats">

                    <article class="stat-card">
                        <div class="stat-icon pink"></div>
                        <h2>
                            <?= number_format($resumen['total_productos']) ?>
                        </h2>
                        <p>Productos registrados</p>
                    </article>

                    <article class="stat-card">
                        <div class="stat-icon red"></div>
                        <h2>
                            <?= number_format($resumen['stock_bajo']) ?>
                        </h2>
                        <p>Stock bajo</p>
                    </article>

                    <article class="stat-card">
                        <div class="stat-icon green"></div>
                        <h2>
                            $<?= number_format($resumen['ventas_dia'], 2, ',', '.') ?>
                        </h2>
                        <p>Ventas del día</p>
                    </article>

                    <article class="stat-card">
                        <div class="stat-icon orange"></div>
                        <h2>
                            <?= number_format($resumen['reservas_pendientes']) ?>
                        </h2>
                        <p>Reservas pendientes</p>
                    </article>

                </section>

                <!-- Gráfico e inventario -->
                <section class="dashboard-middle">

                    <article class="dashboard-card sales-card">

                        <h3>Ventas de la semana</h3>

                        <div class="sales-chart">

                            <?php
                            $dias = [
                                'Lun',
                                'Mar',
                                'Mié',
                                'Jue',
                                'Vie',
                                'Sáb',
                                'Dom'
                            ];

                            $maxVenta = !empty($ventasSemana)
                                ? max($ventasSemana)
                                : 0;
                            ?>

                            <?php foreach ($dias as $indice => $dia): ?>

                                <?php
                                $venta = $ventasSemana[$indice] ?? 0;

                                $altura = $maxVenta > 0
                                    ? ($venta / $maxVenta) * 100
                                    : 0;
                                ?>

                                <div class="chart-column">

                                    <div
                                        class="chart-bar <?= $indice === 5 ? 'highlight' : '' ?>"
                                        style="height: <?= $altura ?>%;"
                                        title="$<?= number_format($venta, 2, ',', '.') ?>"
                                    ></div>

                                    <span><?= $dia ?></span>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    </article>

                    <article class="dashboard-card inventory-card">

                        <h3>Atención de inventario</h3>

                        <div class="inventory-list">

                            <?php if (!empty($productosStockBajo)): ?>

                                <?php foreach ($productosStockBajo as $producto): ?>

                                    <div class="inventory-item">

                                        <div>
                                            <strong>
                                                <?= htmlspecialchars($producto['nombre']) ?>
                                            </strong>

                                            <span>
                                                <?= (int) $producto['existencia'] ?> unidades
                                                (Mínimo: <?= (int) $producto['stock_minimo'] ?>)
                                            </span>
                                        </div>

                                        <span class="stock-badge">
                                            Stock bajo
                                        </span>

                                    </div>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <div class="inventory-empty">
                                    <p>No hay productos con stock bajo.</p>
                                </div>

                            <?php endif; ?>

                        </div>

                    </article>

                </section>

                <!-- Accesos rápidos -->
                <section class="dashboard-card quick-access">

                    <h3>Accesos rápidos</h3>

                    <div class="quick-access-buttons">
                        <a href="#">Registrar venta</a>
                        <a href="/el_papelito/public/productos">Agregar producto</a>
                        <a href="#">Nueva compra</a>
                        <a href="#">Ver reservas</a>
                    </div>

                </section>

            </section>

        </main>

    </div>

</body>
</html>
