<?php
$nombreUsuario = $_SESSION['usuario']['nombre_completo'] ?? 'Administrador';
$correoUsuario = $_SESSION['usuario']['correo'] ?? 'admin@papelito.com';
$periodoDesde = $periodoDesde ?? date('Y-m-01');
$periodoHasta = $periodoHasta ?? date('Y-m-d');
$meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
$periodoSeleccionado = $periodoSeleccionado ?? 'mes_actual';
$moneda = static fn($valor) => '$' . number_format((float)$valor, 2, ',', '.');
$entero = static fn($valor) => number_format((int)$valor, 0, ',', '.');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes | Mini Librería El Papelito</title>
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
            <a href="/el_papelito/public/ventas" title="Módulo de ventas">Ventas</a>
            <a href="/el_papelito/public/reservaciones" title="Módulo de reservas">Reservas</a>
            <a href="/el_papelito/public/proveedores">Proveedores</a>
            <a href="/el_papelito/public/clientes">Clientes</a>
            <a href="/el_papelito/public/usuarios" title="Módulo de usuarios">Usuarios</a>
            <a class="active" href="/el_papelito/public/reportes">Reportes</a>
        </nav>

        <div class="sidebar-session">
            <span class="sidebar-section-label">SESIÓN INICIADA</span>
            <div class="session-user">
                <span class="avatar-letter"><?= htmlspecialchars(strtoupper(substr($nombreUsuario, 0, 1))) ?></span>
                <span><strong><?= htmlspecialchars($nombreUsuario) ?></strong><small><?= htmlspecialchars($correoUsuario) ?></small></span>
            </div>
        </div>
    </aside>

    <main class="main-area">
        <header class="topbar">
            <div class="topbar-brand"><strong>Mini Librería El Papelito</strong><span>Panel de administración</span></div>
            <div class="topbar-user"><span class="notification-dot"></span><div><strong><?= htmlspecialchars($nombreUsuario) ?></strong><span>Mi cuenta · <a href="/el_papelito/public/logout">Salir</a></span></div></div>
        </header>

        <section class="page-content">
            <div class="page-heading">
                <h1>Reportes</h1>
                <p>Consulta ventas, compras e inventario por período.</p>
            </div>

            <section class="report-list-card">
                <div class="card-heading">
                    <div><h2>Listado y administración</h2><p>Consulta los resultados generales y accede al detalle de cada reporte.</p></div>
                </div>

                <form class="report-filters" method="GET" action="/el_papelito/public/reportes" id="filtrosReportes">
                    <div class="filter-group filter-period">
                        <label for="periodo">Período de consulta</label>
                        <select name="periodo" id="periodo">
                            <option value="mes_actual" <?= $periodoSeleccionado === 'mes_actual' ? 'selected' : '' ?>>Este mes</option>
                            <option value="mes_anterior" <?= $periodoSeleccionado === 'mes_anterior' ? 'selected' : '' ?>>Mes anterior</option>
                            <option value="ultimos_3_meses" <?= $periodoSeleccionado === 'ultimos_3_meses' ? 'selected' : '' ?>>Últimos 3 meses</option>
                            <option value="anio_actual" <?= $periodoSeleccionado === 'anio_actual' ? 'selected' : '' ?>>Año actual</option>
                            <option value="personalizado" <?= $periodoSeleccionado === 'personalizado' ? 'selected' : '' ?>>Personalizar fechas</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="desde">Fecha desde</label>
                        <input type="date" name="desde" id="desde" value="<?= htmlspecialchars($periodoDesde) ?>">
                    </div>
                    <div class="filter-group">
                        <label for="hasta">Fecha hasta</label>
                        <input type="date" name="hasta" id="hasta" value="<?= htmlspecialchars($periodoHasta) ?>">
                    </div>
                    <button type="submit" class="btn-primary filter-submit">Consultar</button>
                </form>

                <div class="report-toolbar">
                    <label class="search-report">
                        <span class="sr-only">Buscar reporte</span>
                        <input type="search" id="buscarReporte" placeholder="Buscar tipo de reporte..." autocomplete="off">
                    </label>
                    <a class="btn-primary" href="/el_papelito/public/reportes/exportar?reporte=resumen&amp;desde=<?= urlencode($periodoDesde) ?>&amp;hasta=<?= urlencode($periodoHasta) ?>">
                        Exportar reporte
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="reports-table" id="tablaReportes">
                        <thead><tr><th>Reporte</th><th>Período</th><th>Operaciones / productos</th><th>Resultado</th><th>Estado</th></tr></thead>
                        <tbody>
                        <?php foreach ($reportes as $item): ?>
                            <tr class="report-row" data-search="<?= htmlspecialchars(strtolower($item['reporte'] . ' ' . $item['codigo'])) ?>">
                                <td><a class="report-row-link" href="<?= htmlspecialchars($item['url']) ?>"><?= htmlspecialchars($item['reporte']) ?></a></td>
                                <td><?= htmlspecialchars($item['periodo']) ?></td>
                                <td><?= $entero($item['registros']) ?> <?= $item['codigo'] === 'INV' ? 'productos' : 'operaciones' ?></td>
                                <td><?= $item['formato'] === 'moneda' ? $moneda($item['resultado']) : $entero($item['resultado']) . ' unidades' ?></td>
                                <td><span class="status-pill"><?= htmlspecialchars($item['estado']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr id="sinReportes" class="empty-row" hidden><td colspan="5">No se encontraron reportes con ese nombre o código.</td></tr>
                        </tbody>
                    </table>
                </div>
                <p class="table-footnote">Los resultados se calculan con la información registrada en la base de datos.</p>
            </section>
        </section>
    </main>
</div>
<script src="/el_papelito/public/assets/js/reportes.js"></script>
</body>
</html>
