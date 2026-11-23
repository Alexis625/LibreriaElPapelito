
<?php
$titulo = "Movimientos de inventario";

$escapar = function ($valor) {
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
};

$tiposMovimiento = [
    'ENTRADA_COMPRA' => 'Entrada por compra',
    'SALIDA_VENTA' => 'Salida por venta',
    'AJUSTE_ENTRADA' => 'Ajuste de entrada',
    'AJUSTE_SALIDA' => 'Ajuste de salida'
];

$clasesMovimiento = [
    'ENTRADA_COMPRA' => 'entrada',
    'SALIDA_VENTA' => 'salida',
    'AJUSTE_ENTRADA' => 'ajuste-entrada',
    'AJUSTE_SALIDA' => 'ajuste-salida'
];

$parametrosPaginacion = [
    'busqueda' => $busqueda,
    'tipo' => $tipo,
    'fecha_inicio' => $fechaInicio,
    'fecha_fin' => $fechaFin
];

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $titulo ?> | El Papelito</title>

    <link rel="stylesheet" href="/el_papelito/public/assets/css/movimientos.css">
</head>

<body>

    <main class="modulo-movimientos">

        <!-- ENCABEZADO -->
        <header class="movimientos-header">
            <div>
                <span class="movimientos-etiqueta">CONTROL DE INVENTARIO</span>
                <h1>Movimientos de inventario</h1>
                <p>Consulta el historial de entradas, salidas y ajustes de productos.</p>
            </div>
        </header>


        <!-- TARJETAS DE RESUMEN -->
        <section class="movimientos-resumen">

            <article class="resumen-movimiento">
                <div class="resumen-icono rosa">▤</div>
                <div>
                    <span>Total de movimientos</span>
                    <strong>
                        <?= number_format((int) ($resumen['total_movimientos'] ?? 0), 0, ',', '.') ?>
                    </strong>
                </div>
            </article>

            <article class="resumen-movimiento">
                <div class="resumen-icono verde">↓</div>
                <div>
                    <span>Unidades ingresadas por compras</span>
                    <strong>
                        <?= number_format((int) ($resumen['total_entradas'] ?? 0), 0, ',', '.') ?>
                    </strong>
                </div>
            </article>

            <article class="resumen-movimiento">
                <div class="resumen-icono coral">↑</div>
                <div>
                    <span>Unidades salidas por ventas</span>
                    <strong>
                        <?= number_format((int) ($resumen['total_salidas'] ?? 0), 0, ',', '.') ?>
                    </strong>
                </div>
            </article>

            <article class="resumen-movimiento">
                <div class="resumen-icono amarillo">↻</div>
                <div>
                    <span>Ajustes realizados</span>
                    <strong>
                        <?= number_format((int) ($resumen['total_ajustes'] ?? 0), 0, ',', '.') ?>
                    </strong>
                </div>
            </article>

        </section>


        <!-- FILTROS -->
        <section class="movimientos-filtros">

            <form method="GET" action="/el_papelito/public/movimientos">

                <div class="campo-filtro busqueda-filtro">
                    <label for="busqueda">Buscar movimiento</label>

                    <input
                        type="text"
                        id="busqueda"
                        name="busqueda"
                        placeholder="Producto, código, usuario o referencia..."
                        value="<?= $escapar($busqueda) ?>"
                    >
                </div>

                <div class="campo-filtro">
                    <label for="tipo">Tipo de movimiento</label>

                    <select name="tipo" id="tipo">

                        <option value="">Todos los movimientos</option>

                        <?php foreach ($tiposMovimiento as $clave => $nombre): ?>

                            <option
                                value="<?= $escapar($clave) ?>"
                                <?= $tipo === $clave ? 'selected' : '' ?>
                            >
                                <?= $escapar($nombre) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="campo-filtro">
                    <label for="fecha_inicio">Desde</label>

                    <input
                        type="date"
                        id="fecha_inicio"
                        name="fecha_inicio"
                        value="<?= $escapar($fechaInicio) ?>"
                    >
                </div>

                <div class="campo-filtro">
                    <label for="fecha_fin">Hasta</label>

                    <input
                        type="date"
                        id="fecha_fin"
                        name="fecha_fin"
                        value="<?= $escapar($fechaFin) ?>"
                    >
                </div>

                <div class="acciones-filtro">

                    <button type="submit" class="btn-filtrar">
                        Filtrar
                    </button>

                    <a
                        href="/el_papelito/public/movimientos"
                        class="btn-limpiar"
                    >
                        Limpiar
                    </a>

                </div>

            </form>

        </section>


        <!-- HISTORIAL DE MOVIMIENTOS -->
        <section class="movimientos-tabla-contenedor">

            <div class="tabla-encabezado">
                <div>
                    <h2>Historial de movimientos</h2>
                    <p>Registro de las operaciones realizadas en el inventario.</p>
                </div>

                <span class="tabla-total">
                    <?= number_format($totalMovimientos, 0, ',', '.') ?>
                    registros
                </span>
            </div>


            <div class="tabla-scroll">

                <table class="tabla-movimientos">

                    <thead>
                        <tr>
                            <th>Fecha y hora</th>
                            <th>Producto</th>
                            <th>Tipo</th>
                            <th>Cantidad</th>
                            <th>Existencia anterior</th>
                            <th>Existencia nueva</th>
                            <th>Usuario</th>
                            <th>Referencia</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (!empty($movimientos)): ?>

                            <?php foreach ($movimientos as $movimiento): ?>

                                <tr>

                                    <td>
                                        <?= date(
                                            'd/m/Y H:i',
                                            strtotime($movimiento['fecha_movimiento'])
                                        ) ?>
                                    </td>

                                    <td>
                                        <div class="producto-movimiento">
                                            <strong>
                                                <?= $escapar($movimiento['producto']) ?>
                                            </strong>

                                            <small>
                                                <?= $escapar($movimiento['codigo']) ?>
                                            </small>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="tipo-movimiento <?= $escapar(
                                            $clasesMovimiento[$movimiento['tipo']] ?? ''
                                        ) ?>">
                                            <?= $escapar(
                                                $tiposMovimiento[$movimiento['tipo']] ?? $movimiento['tipo']
                                            ) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <strong>
                                            <?= number_format(
                                                (int) $movimiento['cantidad'],
                                                0,
                                                ',',
                                                '.'
                                            ) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= number_format(
                                            (int) $movimiento['existencia_anterior'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= number_format(
                                            (int) $movimiento['existencia_nueva'],
                                            0,
                                            ',',
                                            '.'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= $escapar($movimiento['usuario']) ?>
                                    </td>

                                    <td>
                                        <?= $escapar($movimiento['referencia'] ?: '—') ?>
                                    </td>

                                    <td class="observaciones-celda">
                                        <?= $escapar($movimiento['observaciones'] ?: '—') ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="9" class="tabla-vacia">
                                    <strong>No se encontraron movimientos.</strong>
                                    <p>Prueba cambiando los filtros de búsqueda.</p>
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>


            <!-- PAGINACIÓN -->
            <div class="movimientos-paginacion">

                <span>
                    Página <?= $pagina ?> de <?= $totalPaginas ?>
                </span>

                <div class="paginacion-botones">

                    <?php if ($pagina > 1): ?>

                        <a href="?<?= $escapar(http_build_query(array_merge(
                            $parametrosPaginacion,
                            ['pagina' => $pagina - 1]
                        ))) ?>">
                            Anterior
                        </a>

                    <?php endif; ?>


                    <?php if ($pagina < $totalPaginas): ?>

                        <a href="?<?= $escapar(http_build_query(array_merge(
                            $parametrosPaginacion,
                            ['pagina' => $pagina + 1]
                        ))) ?>">
                            Siguiente
                        </a>

                    <?php endif; ?>

                </div>

            </div>

        </section>

    </main>

</body>

</html>
