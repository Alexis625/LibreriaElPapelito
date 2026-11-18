<?php
$titulo = "Compras";

// Generar token de seguridad para el formulario
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?> | El Papelito</title>

    <link rel="stylesheet" href="/el_papelito/public/assets/css/proveedores.css">
    <link rel="stylesheet" href="/el_papelito/public/assets/css/compras.css">
</head>

<body>

    <main class="contenido-proveedores">

        <header class="encabezado">
            <div>
                <h1>Compras</h1>
                <p>Administra las compras realizadas a tus proveedores.</p>
            </div>

            <button type="button" id="btnNuevaCompra">
                + Nueva compra
            </button>
        </header>


<!-- Notificaciones del sistema -->

<?php if (isset($_GET['registro'])): ?>

    <?php if ($_GET['registro'] === 'exitoso'): ?>

        <div class="mensaje mensaje-exito">
            <strong>¡Compra registrada!</strong>
            La compra se guardó correctamente y el inventario fue actualizado.
        </div>

    <?php else: ?>

        <div class="mensaje mensaje-error">
            <strong>Error al registrar la compra.</strong>
            No fue posible completar el registro. Verifica los datos e inténtalo nuevamente.
        </div>

    <?php endif; ?>

<?php endif; ?>
<!-- Notificaciones de anulación -->

<?php if (isset($_GET['anulacion'])): ?>

    <?php if ($_GET['anulacion'] === 'exitosa'): ?>

        <div class="mensaje mensaje-exito">
            <strong>¡Compra anulada!</strong>
            La compra fue anulada y las existencias del inventario fueron actualizadas.
        </div>

    <?php else: ?>

        <div class="mensaje mensaje-error">
            <strong>No fue posible anular la compra.</strong>
            <?= htmlspecialchars(
                $_SESSION['error_anulacion'] ?? 'Verifica las existencias e inténtalo nuevamente.'
            ) ?>
        </div>

    <?php endif; ?>

    <?php unset($_SESSION['error_anulacion']); ?>

<?php endif; ?>


        
<section class="filtros">

    <form method="GET" action="/el_papelito/public/compras">

        <div class="form-group">
            <label for="busqueda">Buscar</label>
            <input
                type="text"
                id="busqueda"
                name="busqueda"
                placeholder="Proveedor o documento..."
                value="<?= htmlspecialchars($busqueda) ?>">
        </div>

        <div class="form-group">
            <label for="id_proveedor">Proveedor</label>
            <select name="id_proveedor" id="id_proveedor">
                <option value="">Todos los proveedores</option>

                <?php foreach ($proveedores as $proveedor): ?>
                    <option
                        value="<?= $proveedor['id_proveedor'] ?>"
                        <?= (string)$idProveedor === (string)$proveedor['id_proveedor'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($proveedor['nombre_empresa']) ?>
                    </option>
                <?php endforeach; ?>

            </select>
        </div>

        <div class="form-group">
            <label for="estado">Estado</label>
            <select name="estado" id="estado">
                <option value="">Todos los estados</option>
                <option value="RECIBIDA" <?= $estado === 'RECIBIDA' ? 'selected' : '' ?>>
                    Recibida
                </option>
                <option value="ANULADA" <?= $estado === 'ANULADA' ? 'selected' : '' ?>>
                    Anulada
                </option>
            </select>
        </div>

        <div class="form-group">
            <label for="fecha_inicio">Desde</label>
            <input
                type="date"
                id="fecha_inicio"
                name="fecha_inicio"
                value="<?= htmlspecialchars($fechaInicio) ?>">
        </div>

        <div class="form-group">
            <label for="fecha_fin">Hasta</label>
            <input
                type="date"
                id="fecha_fin"
                name="fecha_fin"
                value="<?= htmlspecialchars($fechaFin) ?>">
        </div>

        <div class="acciones-filtros">
            <button type="submit">Filtrar</button>

            <a href="/el_papelito/public/compras">
                Limpiar filtros
            </a>
        </div>

    </form>

</section>


        <section class="panel-listado-compras">

    <div class="tabla-contenedor">

            <table>
                <thead>
                    <tr>
                        <th>N.º</th>
                        <th>Fecha</th>
                        <th>Proveedor</th>
                        <th>Documento</th>
                        <th>Registrado por</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (empty($compras)): ?>

                        <tr>
                            <td colspan="8" class="sin-resultados">
                                No se encontraron compras.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($compras as $compra): ?>

                            <tr class="<?= $compra['estado'] === 'ANULADA' ? 'fila-compra-anulada' : '' ?>">
                                <td>#<?= $compra['id_compra'] ?></td>

                                <td>
                                    <?= date('d/m/Y H:i', strtotime($compra['fecha_compra'])) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($compra['nombre_empresa']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($compra['numero_documento'] ?? '—') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($compra['nombre_completo']) ?>
                                </td>

                                <td>
                                    $<?= number_format((float) $compra['total'], 2) ?>
                                </td>

                                <td>
    <?php if ($compra['estado'] === 'RECIBIDA'): ?>

        <span class="estado-compra estado-recibida">
            Recibida
        </span>

    <?php else: ?>

        <span class="estado-compra estado-anulada">
            Anulada
        </span>

    <?php endif; ?>
</td>

<td>
    <div class="acciones-compra">

        <button
            type="button"
            class="btn-ver-compra"
            data-id="<?= $compra['id_compra'] ?>"
            title="Consultar detalles">

            <span aria-hidden="true">◉</span>
            Ver

        </button>

        <?php if ($compra['estado'] === 'RECIBIDA'): ?>

            <button
                type="button"
                class="btn-anular-compra"
                data-id="<?= $compra['id_compra'] ?>"
                data-csrf="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
                title="Anular compra">

                <span aria-hidden="true">×</span>
                Anular

            </button>

        <?php endif; ?>

    </div>
</td>
                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>
            </table>

        </div>

<!-- Paginación de compras -->

<?php
$desde = $totalCompras > 0
    ? (($pagina - 1) * $limite) + 1
    : 0;

$hasta = min($pagina * $limite, $totalCompras);

// Conservar los filtros al cambiar de página
$parametrosPaginacion = $_GET;
?>

<div class="paginacion-compras">

    <div class="paginacion-info">
        Mostrando <?= $desde ?> - <?= $hasta ?>
        de <?= $totalCompras ?> compras
    </div>

    <div class="paginacion-controles">

        <?php if ($pagina > 1): ?>
            <?php
            $parametrosPaginacion['pagina'] = $pagina - 1;
            ?>

            <a href="?<?= htmlspecialchars(http_build_query($parametrosPaginacion)) ?>"
               class="btn-paginacion">
                Anterior
            </a>
        <?php else: ?>
            <span class="btn-paginacion deshabilitado">
                Anterior
            </span>
        <?php endif; ?>

        <span class="pagina-actual">
            Página <?= $pagina ?> de <?= $totalPaginas ?>
        </span>

        <?php if ($pagina < $totalPaginas): ?>
            <?php
            $parametrosPaginacion['pagina'] = $pagina + 1;
            ?>

            <a href="?<?= htmlspecialchars(http_build_query($parametrosPaginacion)) ?>"
               class="btn-paginacion">
                Siguiente
            </a>
        <?php else: ?>
            <span class="btn-paginacion deshabilitado">
                Siguiente
            </span>
        <?php endif; ?>

    </div>

</div>

    </main>
    </section>

    <!-- Modal para registrar compras -->
    <div class="modal-overlay" id="modalCompra">

        <div class="modal-container modal-compra">

            <div class="modal-header">
                <div>
                    <h2>Nueva compra</h2>
                    <p>Registra los productos adquiridos a un proveedor.</p>
                </div>

                <button type="button" id="cerrarModalCompra" class="btn-cerrar">
                    &times;
                </button>
            </div>

            <form id="formCompra" method="POST" action="/el_papelito/public/compras/crear">
                <input
    type="hidden"
    name="csrf_token"
    value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
>

                <!-- Información general -->
                <h3 class="titulo-seccion-compra">Información de la compra</h3>

                <div class="form-grid">

                    <div class="form-group">
                        <label for="id_proveedor">Proveedor *</label>

                        <select name="id_proveedor" id="id_proveedor" required>
                            <option value="">Seleccionar proveedor</option>

                            <?php foreach ($proveedores as $proveedor): ?>
                                <option value="<?= $proveedor['id_proveedor'] ?>">
                                    <?= htmlspecialchars($proveedor['nombre_empresa']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>
                    </div>

                    <div class="form-group">
                        <label for="numero_documento">Número de documento</label>

                        <input type="text" name="numero_documento" id="numero_documento" maxlength="60"
                            placeholder="Ej. FAC-001">
                    </div>

                    <div class="form-group full-width">
                        <label for="observaciones">Observaciones</label>

                        <textarea name="observaciones" id="observaciones" rows="3" maxlength="500"
                            placeholder="Información adicional de la compra..."></textarea>
                    </div>

                </div>

                <!-- Productos -->
                <div class="productos-compra-header">
                    <div>
                        <h3 class="titulo-seccion-compra">Productos</h3>
                        <p>Agrega los productos que forman parte de esta compra.</p>
                    </div>

                    <button type="button" id="btnAgregarProducto">
                        + Agregar producto
                    </button>
                </div>

                <div class="tabla-contenedor tabla-productos-compra">

                    <table>
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Costo unitario</th>
                                <th>Subtotal</th>
                                <th>Acción</th>
                            </tr>
                        </thead>

                        <tbody id="listaProductosCompra">
    <tr id="filaSinProductos">
        <td colspan="5" class="sin-resultados">
            Agrega los productos de esta compra.
        </td>
    </tr>
</tbody>
                    </table>
                    <template id="plantillaProductoCompra">
    <tr class="fila-producto-compra">

        <td>
            <select name="productos[]" class="select-producto" required>
                <option value="">Seleccionar producto</option>

                <?php foreach ($productos as $producto): ?>
                    <option
                        value="<?= $producto['id_producto'] ?>"
                        data-costo="<?= $producto['precio_compra'] ?>"
                    >
                        <?= htmlspecialchars($producto['nombre']) ?>
                        (<?= htmlspecialchars($producto['codigo']) ?>)
                    </option>
                <?php endforeach; ?>

            </select>
        </td>

        <td>
            <input
                type="number"
                name="cantidades[]"
                class="cantidad-producto"
                min="1"
                step="1"
                value="1"
                required
            >
        </td>

        <td>
            <input
                type="number"
                name="costos[]"
                class="costo-producto"
                min="0"
                step="0.01"
                placeholder="0,00"
                required
            >
        </td>

        <td class="subtotal-producto">$0,00</td>

        <td>
            <button type="button" class="btn-eliminar-producto">
                Eliminar
            </button>
        </td>

    </tr>
</template>

                </div>

                <!-- Total -->
                <div class="total-compra-modal">
                    <span>Total de la compra</span>
                    <h2 id="totalCompra">$0,00</h2>
                </div>

                <!-- Botones -->
                <div class="modal-actions">

                    <button type="button" id="cancelarModalCompra">
                        Cancelar
                    </button>

                    <button type="submit" id="btnGuardarCompra">
                        Guardar compra
                    </button>

                </div>

            </form>

        </div>

    </div>

<!-- Modal para consultar detalles de compra -->
<div class="modal-overlay" id="modalDetalleCompra">

    <div class="modal-container modal-detalle-compra">

        <div class="modal-header">
            <div>
                <h2>Detalle de compra</h2>
                <p id="detalleNumeroCompra">Información de la compra</p>
            </div>

            <button
                type="button"
                id="cerrarDetalleCompra"
                class="btn-cerrar">
                &times;
            </button>
        </div>

        <div id="contenidoDetalleCompra">
            <p>Cargando información...</p>
        </div>

    </div>

</div>

<script src="/el_papelito/public/assets/js/compras.js"></script>

</body>

</html>