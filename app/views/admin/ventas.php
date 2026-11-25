
<?php

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$titulo = "Ventas";

function escaparVenta($valor)
{
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

$mensajeRegistro = $_GET['registro'] ?? '';
$mensajeAnulacion = $_GET['anulacion'] ?? '';

?>

<link rel="stylesheet" href="/el_papelito/public/assets/css/layouts.css">
<link rel="stylesheet" href="/el_papelito/public/assets/css/proveedores.css">
<link rel="stylesheet" href="/el_papelito/public/assets/css/ventas.css">

<div class="app-layout">
    <?php
    $moduloActual = 'ventas';
    require __DIR__ . '/../layouts/sidebar.php';
    ?>

    <div class="main-area">
        <?php require __DIR__ . '/../layouts/topbar.php'; ?>

        <main class="contenido-proveedores contenido-ventas">

    <div class="encabezado">
        <div>
            <h1>Ventas</h1>
            <p>Administra y consulta las ventas registradas en la librería.</p>
        </div>

        <button type="button" class="btn-principal" id="btnNuevaVenta">
            + Nueva venta
        </button>
    </div>

    <?php if ($mensajeRegistro === 'exito'): ?>
        <div class="mensaje mensaje-exito">
            La venta se registró correctamente.
        </div>
    <?php elseif ($mensajeRegistro === 'error'): ?>
        <div class="mensaje mensaje-error">
            <?= escaparVenta($_SESSION['error_venta'] ?? 'No fue posible registrar la venta.') ?>
        </div>
        <?php unset($_SESSION['error_venta']); ?>
    <?php endif; ?>

    <?php if ($mensajeAnulacion === 'exito'): ?>
        <div class="mensaje mensaje-exito">
            La venta se anuló correctamente.
        </div>
    <?php elseif ($mensajeAnulacion === 'error'): ?>
        <div class="mensaje mensaje-error">
            <?= escaparVenta($_SESSION['error_anulacion'] ?? 'No fue posible anular la venta.') ?>
        </div>
        <?php unset($_SESSION['error_anulacion']); ?>
    <?php endif; ?>

    <form method="GET" action="/el_papelito/public/ventas" class="filtros">

        <div class="filtro-busqueda">
            <input
                type="text"
                name="busqueda"
                placeholder="Buscar por venta, cliente o usuario..."
                value="<?= escaparVenta($busqueda ?? '') ?>"
            >
        </div>

        <div class="filtro-campo">
            <select name="estado">
                <option value="todos" <?= ($estadoFiltro ?? 'todos') === 'todos' ? 'selected' : '' ?>>
                    Todos los estados
                </option>
                <option value="COMPLETADA" <?= ($estadoFiltro ?? '') === 'COMPLETADA' ? 'selected' : '' ?>>
                    Completadas
                </option>
                <option value="ANULADA" <?= ($estadoFiltro ?? '') === 'ANULADA' ? 'selected' : '' ?>>
                    Anuladas
                </option>
            </select>
        </div>

        <div class="filtro-campo">
            <label>Desde</label>
            <input
                type="date"
                name="fecha_inicio"
                value="<?= escaparVenta($fechaInicio ?? '') ?>"
            >
        </div>

        <div class="filtro-campo">
            <label>Hasta</label>
            <input
                type="date"
                name="fecha_fin"
                value="<?= escaparVenta($fechaFin ?? '') ?>"
            >
        </div>

        <button type="submit" class="btn-principal">
            Filtrar
        </button>

        <a href="/el_papelito/public/ventas" class="btn-secundario">
            Limpiar
        </a>

    </form>

    <div class="tabla-contenedor">

        <table class="tabla">
            <thead>
                <tr>
                    <th>Venta</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Registrado por</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>

                <?php if (!empty($ventas)): ?>

                    <?php foreach ($ventas as $venta): ?>

                        <tr>
                            <td>#<?= escaparVenta($venta['id_venta']) ?></td>

                            <td>
                                <?= escaparVenta(date('d/m/Y H:i', strtotime($venta['fecha_venta']))) ?>
                            </td>

                            <td>
                                <?= escaparVenta($venta['cliente']) ?>
                            </td>

                            <td>
                                <?= escaparVenta($venta['usuario']) ?>
                            </td>

                            <td class="total-venta">
                                $<?= number_format((float)$venta['total'], 2, '.', ',') ?>
                            </td>

                            <td>
                                <?php if ($venta['estado'] === 'COMPLETADA'): ?>
                                    <span class="estado-venta completada">Completada</span>
                                <?php else: ?>
                                    <span class="estado-venta anulada">Anulada</span>
                                <?php endif; ?>
                            </td>

                            <td class="acciones-venta">

                                <button
                                    type="button"
                                    class="btn-ver-venta"
                                    data-id="<?= escaparVenta($venta['id_venta']) ?>"
                                >
                                    Ver detalle
                                </button>

                                <?php if ($venta['estado'] === 'COMPLETADA'): ?>

                                    <form
                                        method="POST"
                                        action="/el_papelito/public/ventas/anular"
                                        class="form-anular-venta"
                                    >
                                        <input type="hidden" name="csrf_token" value="<?= escaparVenta($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="id_venta" value="<?= escaparVenta($venta['id_venta']) ?>">

                                        <button type="submit" class="btn-anular-venta">
                                            Anular
                                        </button>
                                    </form>

                                <?php endif; ?>

                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="7" class="sin-registros">
                            No se encontraron ventas registradas.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>
        </table>

    </div>

    <?php if (($totalPaginas ?? 1) > 1): ?>

        <div class="paginacion-ventas">

            <?php
            $parametros = $_GET;
            ?>

            <?php if ($pagina > 1): ?>
                <?php $parametros['pagina'] = $pagina - 1; ?>
                <a href="?<?= escaparVenta(http_build_query($parametros)) ?>">
                    Anterior
                </a>
            <?php endif; ?>

            <span>
                Página <?= (int)$pagina ?> de <?= (int)$totalPaginas ?>
            </span>

            <?php if ($pagina < $totalPaginas): ?>
                <?php $parametros['pagina'] = $pagina + 1; ?>
                <a href="?<?= escaparVenta(http_build_query($parametros)) ?>">
                    Siguiente
                </a>
            <?php endif; ?>

        </div>

    <?php endif; ?>

        </main>
    </div>
</div>

<!-- MODAL: REGISTRAR VENTA -->

<div class="modal" id="modalVenta">

    <div class="modal-contenido modal-venta">

        <div class="modal-encabezado">
            <div>
                <h2>Nueva venta</h2>
                <p>Agrega los productos que deseas vender.</p>
            </div>

            <button type="button" class="cerrar-modal" id="cerrarModalVenta">
                &times;
            </button>
        </div>

        <form
            method="POST"
            action="/el_papelito/public/ventas/crear"
            id="formVenta"
        >

            <input
                type="hidden"
                name="csrf_token"
                value="<?= escaparVenta($_SESSION['csrf_token']) ?>"
            >

            <div class="formulario-venta">

                <div class="campo-venta">
                    <label for="id_cliente">Cliente</label>

                    <select name="id_cliente" id="id_cliente">
                        <option value="">Consumidor final</option>

                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= escaparVenta($cliente['id_cliente']) ?>">
                                <?= escaparVenta($cliente['nombre_completo']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="campo-venta">
                    <label for="observaciones">Observaciones</label>

                    <textarea
                        name="observaciones"
                        id="observaciones"
                        maxlength="500"
                        placeholder="Observaciones de la venta (opcional)"
                    ></textarea>
                </div>

            </div>

            <div class="seccion-productos-venta">

                <div class="productos-venta-encabezado">
                    <h3>Productos</h3>

                    <button type="button" class="btn-agregar-producto" id="btnAgregarProducto">
                        + Agregar producto
                    </button>
                </div>

                <div id="contenedorProductosVenta">

                    <div class="fila-producto-venta">

                        <div class="campo-producto">
                            <label>Producto</label>

                            <select name="productos[]" class="select-producto-venta" required>
                                <option value="">Seleccionar producto</option>

                                <?php foreach ($productos as $producto): ?>
                                    <option
                                        value="<?= escaparVenta($producto['id_producto']) ?>"
                                        data-precio="<?= escaparVenta($producto['precio_venta']) ?>"
                                        data-stock="<?= escaparVenta($producto['existencia']) ?>"
                                    >
                                        <?= escaparVenta($producto['codigo'] . ' - ' . $producto['nombre']) ?>
                                        (Stock: <?= (int)$producto['existencia'] ?>)
                                    </option>
                                <?php endforeach; ?>

                            </select>
                        </div>

                        <div class="campo-cantidad">
                            <label>Cantidad</label>

                            <input
                                type="number"
                                name="cantidades[]"
                                class="cantidad-producto-venta"
                                min="1"
                                value="1"
                                required
                            >
                        </div>

                        <div class="campo-precio">
                            <label>Precio</label>
                            <span class="precio-producto-venta">$0,00</span>
                        </div>

                        <div class="campo-subtotal">
                            <label>Subtotal</label>
                            <span class="subtotal-producto-venta">$0,00</span>
                        </div>

                        <button
                            type="button"
                            class="btn-eliminar-producto"
                            title="Eliminar producto"
                        >
                            &times;
                        </button>

                    </div>

                </div>

            </div>

            <div class="resumen-venta">
                <span>Total de la venta</span>
                <strong id="totalVenta">$0,00</strong>
            </div>

            <div class="modal-acciones">
                <button type="button" class="btn-secundario" id="cancelarVenta">
                    Cancelar
                </button>

                <button type="submit" class="btn-principal" id="btnGuardarVenta">
                    Registrar venta
                </button>
            </div>

        </form>

    </div>

</div>


<!-- MODAL: DETALLE DE VENTA -->

<div class="modal" id="modalDetalleVenta">

    <div class="modal-contenido modal-detalle-venta">

        <div class="modal-encabezado">
            <div>
                <h2>Detalle de venta</h2>
                <p id="detalleNumeroVenta">Información de la venta</p>
            </div>

            <button type="button" class="cerrar-modal" id="cerrarDetalleVenta">
                &times;
            </button>
        </div>

        <div id="contenidoDetalleVenta">
            <p class="cargando-detalle">Cargando información...</p>
        </div>

    </div>

</div>

<script src="/el_papelito/public/assets/js/ventas.js"></script>
