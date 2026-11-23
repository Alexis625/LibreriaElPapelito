
<?php
// Protección de acceso
if (!isset($_SESSION['usuario'])) {
    header('Location: /el_papelito/public/login');
    exit;
}

// Valores para filtros
$busqueda = $busqueda ?? '';
$estado = $estado ?? '';
$fechaInicio = $fechaInicio ?? '';
$fechaFin = $fechaFin ?? '';

$reservaciones = $reservaciones ?? [];
$clientes = $clientes ?? [];
$productos = $productos ?? [];

$pagina = $pagina ?? 1;
$totalPaginas = $totalPaginas ?? 1;
$totalReservaciones = $totalReservaciones ?? 0;

$csrfToken = $_SESSION['csrf_token'] ?? '';

function escaparReservacion($valor)
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function formatoFechaReservacion($fecha)
{
    if (empty($fecha)) {
        return '—';
    }

    return date('d/m/Y', strtotime($fecha));
}

function formatoFechaHoraReservacion($fecha)
{
    if (empty($fecha)) {
        return '—';
    }

    return date('d/m/Y h:i A', strtotime($fecha));
}
?>

<link rel="stylesheet" href="/el_papelito/public/assets/css/proveedores.css">
<link rel="stylesheet" href="/el_papelito/public/assets/css/reservaciones.css">

<div class="modulo-reservaciones">

    <!-- ENCABEZADO -->
    <div class="reservaciones-header">
        <div>
            <span class="reservaciones-etiqueta">GESTIÓN DE INVENTARIO Y CLIENTES</span>
            <h1>Reservaciones</h1>
            <p>Administra las reservaciones y consulta su estado.</p>
        </div>

        <button type="button" class="btn-reservacion-principal" id="btnAbrirCrear">
            <span>+</span>
            Nueva reservación
        </button>
    </div>

    <!-- MENSAJES -->
    <?php if (!empty($_SESSION['error_reservacion'])): ?>
        <div class="alerta-reservacion error">
            <?= escaparReservacion($_SESSION['error_reservacion']) ?>
        </div>
        <?php unset($_SESSION['error_reservacion']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error_estado_reservacion'])): ?>
        <div class="alerta-reservacion error">
            <?= escaparReservacion($_SESSION['error_estado_reservacion']) ?>
        </div>
        <?php unset($_SESSION['error_estado_reservacion']); ?>
    <?php endif; ?>

    <?php if (($_GET['registro'] ?? '') === 'exitoso'): ?>
        <div class="alerta-reservacion exito">
            La reservación se registró correctamente.
        </div>
    <?php endif; ?>

    <?php if (($_GET['estado'] ?? '') === 'exitoso'): ?>
        <div class="alerta-reservacion exito">
            El estado de la reservación se actualizó correctamente.
        </div>
    <?php endif; ?>

    <!-- RESUMEN -->
    <div class="reservaciones-resumen">
        <div class="resumen-card">
            <div class="resumen-icono rosa">▤</div>
            <div>
                <span>Total de reservaciones</span>
                <strong><?= number_format($totalReservaciones) ?></strong>
            </div>
        </div>

        <div class="resumen-card">
            <div class="resumen-icono amarillo">◷</div>
            <div>
                <span>Resultados actuales</span>
                <strong><?= count($reservaciones) ?></strong>
            </div>
        </div>
    </div>

    <!-- FILTROS -->
    <form method="GET"
          action="/el_papelito/public/reservaciones"
          class="reservaciones-filtros">

        <div class="filtro-busqueda">
            <label for="busqueda">Buscar reservación</label>
            <input
                type="text"
                id="busqueda"
                name="busqueda"
                value="<?= escaparReservacion($busqueda) ?>"
                placeholder="Cliente, teléfono o número..."
            >
        </div>

        <div>
            <label for="filtroEstado">Estado</label>
            <select name="estado" id="filtroEstado">
                <option value="">Todos los estados</option>
                <option value="PENDIENTE" <?= $estado === 'PENDIENTE' ? 'selected' : '' ?>>Pendiente</option>
                <option value="CONFIRMADA" <?= $estado === 'CONFIRMADA' ? 'selected' : '' ?>>Confirmada</option>
                <option value="ENTREGADA" <?= $estado === 'ENTREGADA' ? 'selected' : '' ?>>Entregada</option>
                <option value="CANCELADA" <?= $estado === 'CANCELADA' ? 'selected' : '' ?>>Cancelada</option>
            </select>
        </div>

        <div>
            <label for="fecha_inicio">Desde</label>
            <input
                type="date"
                id="fecha_inicio"
                name="fecha_inicio"
                value="<?= escaparReservacion($fechaInicio) ?>"
            >
        </div>

        <div>
            <label for="fecha_fin">Hasta</label>
            <input
                type="date"
                id="fecha_fin"
                name="fecha_fin"
                value="<?= escaparReservacion($fechaFin) ?>"
            >
        </div>

        <div class="filtros-acciones">
            <button type="submit" class="btn-filtrar">Filtrar</button>
            <a href="/el_papelito/public/reservaciones" class="btn-limpiar">Limpiar</a>
        </div>
    </form>

    <!-- TABLA -->
    <div class="reservaciones-tabla-contenedor">

        <div class="tabla-encabezado">
            <div>
                <h2>Listado de reservaciones</h2>
                <p>Consulta y administra las reservaciones registradas.</p>
            </div>

            <span class="tabla-total">
                <?= number_format($totalReservaciones) ?> registros
            </span>
        </div>

        <div class="tabla-scroll">
            <table class="tabla-reservaciones">
                <thead>
                    <tr>
                        <th>N.º</th>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Fecha de reserva</th>
                        <th>Fecha límite</th>
                        <th>Registrado por</th>
                        <th>Productos</th>
                        <th>Unidades</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($reservaciones)): ?>

                        <tr>
                            <td colspan="10" class="tabla-vacia">
                                <div class="vacio-icono">▤</div>
                                <strong>No hay reservaciones</strong>
                                <p>No se encontraron reservaciones con los filtros seleccionados.</p>
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($reservaciones as $reserva): ?>
                            <?php
                            $claseEstado = strtolower($reserva['estado'] ?? '');
                            ?>

                            <tr>
                                <td class="numero-reserva">
                                    #<?= escaparReservacion($reserva['id_reserva']) ?>
                                </td>

                                <td>
                                    <strong><?= escaparReservacion($reserva['nombre_completo']) ?></strong>
                                </td>

                                <td><?= escaparReservacion($reserva['telefono'] ?: '—') ?></td>

                                <td><?= formatoFechaHoraReservacion($reserva['fecha_reserva']) ?></td>

                                <td><?= formatoFechaReservacion($reserva['fecha_limite_retiro']) ?></td>

                                <td><?= escaparReservacion($reserva['registrado_por'] ?? 'Cliente sin usuario') ?></td>

                                <td><?= number_format((int) $reserva['cantidad_productos']) ?></td>

                                <td><?= number_format((int) $reserva['unidades']) ?></td>

                                <td>
                                    <span class="estado-reserva <?= escaparReservacion($claseEstado) ?>">
                                        <?= escaparReservacion(ucfirst(strtolower($reserva['estado']))) ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="acciones-reserva">

                                        <button
                                            type="button"
                                            class="btn-accion ver-detalle"
                                            data-id="<?= escaparReservacion($reserva['id_reserva']) ?>"
                                            title="Ver detalles"
                                        >
                                            Ver
                                        </button>

                                        <button
                                            type="button"
                                            class="btn-accion cambiar-estado"
                                            data-id="<?= escaparReservacion($reserva['id_reserva']) ?>"
                                            data-estado="<?= escaparReservacion($reserva['estado']) ?>"
                                            title="Cambiar estado"
                                        >
                                            Estado
                                        </button>

                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN -->
        <?php if ($totalPaginas > 1): ?>
            <div class="reservaciones-paginacion">

                <span>
                    Página <?= (int) $pagina ?> de <?= (int) $totalPaginas ?>
                </span>

                <div class="paginacion-botones">

                    <?php
                    $parametros = $_GET;
                    ?>

                    <?php if ($pagina > 1): ?>
                        <?php
                        $parametros['pagina'] = $pagina - 1;
                        ?>
                        <a href="?<?= escaparReservacion(http_build_query($parametros)) ?>">
                            Anterior
                        </a>
                    <?php endif; ?>

                    <?php if ($pagina < $totalPaginas): ?>
                        <?php
                        $parametros['pagina'] = $pagina + 1;
                        ?>
                        <a href="?<?= escaparReservacion(http_build_query($parametros)) ?>">
                            Siguiente
                        </a>
                    <?php endif; ?>

                </div>
            </div>
        <?php endif; ?>

    </div>

</div>

<!-- =====================================
     MODAL: CREAR RESERVACIÓN
===================================== -->

<div class="modal-reservacion" id="modalCrear">

    <div class="modal-reservacion-contenido modal-grande">

        <div class="modal-reservacion-header">
            <div>
                <h2>Nueva reservación</h2>
                <p>Completa los datos para registrar una reservación.</p>
            </div>

            <button type="button" class="cerrar-modal" data-cerrar="modalCrear">
                &times;
            </button>
        </div>

        <form method="POST"
              action="/el_papelito/public/reservaciones/crear"
              id="formCrearReservacion">

            <input type="hidden" name="csrf_token" value="<?= escaparReservacion($csrfToken) ?>">

            <div class="formulario-reservacion">

                <div class="campo-reserva campo-completo">
                    <label for="id_cliente">Cliente *</label>

                    <select name="id_cliente" id="id_cliente" required>
                        <option value="">Selecciona un cliente</option>

                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= escaparReservacion($cliente['id_cliente']) ?>">
                                <?= escaparReservacion($cliente['nombre_completo']) ?>
                                <?= !empty($cliente['telefono']) ? ' - ' . escaparReservacion($cliente['telefono']) : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo-reserva">
                    <label for="fecha_limite_retiro">Fecha límite de retiro</label>

                    <input
                        type="date"
                        name="fecha_limite_retiro"
                        id="fecha_limite_retiro"
                    >
                </div>

                <div class="campo-reserva campo-completo">
                    <label for="observaciones">Observaciones</label>

                    <textarea
                        name="observaciones"
                        id="observaciones"
                        rows="3"
                        maxlength="500"
                        placeholder="Información adicional de la reservación..."
                    ></textarea>
                </div>

            </div>

            <!-- PRODUCTOS -->
            <div class="productos-reserva-header">
                <div>
                    <h3>Productos reservados</h3>
                    <p>Agrega los productos y sus respectivas cantidades.</p>
                </div>

                <button type="button" id="btnAgregarProducto" class="btn-agregar-producto">
                    + Agregar producto
                </button>
            </div>

            <div id="contenedorProductosReserva">

                <div class="producto-reserva-fila">

                    <div class="campo-reserva producto-select">
                        <label>Producto *</label>

                        <select name="productos[]" required class="select-producto-reserva">
                            <option value="">Selecciona un producto</option>

                            <?php foreach ($productos as $producto): ?>
                                <option
                                    value="<?= escaparReservacion($producto['id_producto']) ?>"
                                    data-precio="<?= escaparReservacion($producto['precio_venta']) ?>"
                                >
                                    <?= escaparReservacion($producto['codigo']) ?>
                                    - <?= escaparReservacion($producto['nombre']) ?>
                                    (<?= number_format((int) $producto['existencia']) ?> disponibles)
                                </option>
                            <?php endforeach; ?>

                        </select>
                    </div>

                    <div class="campo-reserva producto-cantidad">
                        <label>Cantidad *</label>

                        <input
                            type="number"
                            name="cantidades[]"
                            min="1"
                            step="1"
                            required
                            placeholder="Cantidad"
                        >
                    </div>

                    <button
                        type="button"
                        class="btn-eliminar-producto"
                        title="Eliminar producto"
                        disabled
                    >
                        &times;
                    </button>

                </div>

            </div>

            <div class="nota-reserva">
                <strong>Importante:</strong>
                Registrar una reservación no descuenta automáticamente las existencias del inventario.
            </div>

            <div class="modal-reservacion-footer">
                <button type="button" class="btn-cancelar-modal" data-cerrar="modalCrear">
                    Cancelar
                </button>

                <button type="submit" class="btn-guardar-reserva">
                    Registrar reservación
                </button>
            </div>

        </form>

    </div>
</div>

<!-- =====================================
     MODAL: DETALLES
===================================== -->

<div class="modal-reservacion" id="modalDetalle">

    <div class="modal-reservacion-contenido">

        <div class="modal-reservacion-header">
            <div>
                <h2>Detalle de reservación</h2>
                <p>Información del cliente y productos reservados.</p>
            </div>

            <button type="button" class="cerrar-modal" data-cerrar="modalDetalle">
                &times;
            </button>
        </div>

        <div id="contenidoDetalleReserva">
            <div class="detalle-cargando">Cargando información...</div>
        </div>

    </div>
</div>

<!-- =====================================
     MODAL: CAMBIAR ESTADO
===================================== -->

<div class="modal-reservacion" id="modalEstado">

    <div class="modal-reservacion-contenido modal-estado-contenido">

        <div class="modal-reservacion-header">
            <div>
                <h2>Actualizar estado</h2>
                <p>Selecciona el nuevo estado de la reservación.</p>
            </div>

            <button type="button" class="cerrar-modal" data-cerrar="modalEstado">
                &times;
            </button>
        </div>

        <form method="POST"
              action="/el_papelito/public/reservaciones/actualizar-estado">

            <input type="hidden" name="csrf_token" value="<?= escaparReservacion($csrfToken) ?>">

            <input type="hidden" name="id_reserva" id="estado_id_reserva">

            <div class="campo-reserva">
                <label for="nuevo_estado">Estado</label>

                <select name="estado" id="nuevo_estado" required>
                    <option value="PENDIENTE">Pendiente</option>
                    <option value="CONFIRMADA">Confirmada</option>
                    <option value="ENTREGADA">Entregada</option>
                    <option value="CANCELADA">Cancelada</option>
                </select>
            </div>

            <div class="modal-reservacion-footer">
                <button type="button" class="btn-cancelar-modal" data-cerrar="modalEstado">
                    Cancelar
                </button>

                <button type="submit" class="btn-guardar-reserva">
                    Guardar cambios
                </button>
            </div>

        </form>

    </div>
</div>

<!-- DATOS PARA JAVASCRIPT -->
<script>
    window.reservacionesConfig = {
        urlDetalle: '/el_papelito/public/reservaciones/obtener-detalle',
        csrfToken: <?= json_encode($csrfToken) ?>
    };
</script>

<script src="/el_papelito/public/assets/js/reservaciones.js"></script>
