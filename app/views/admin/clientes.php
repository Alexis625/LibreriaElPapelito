
<?php
$titulo = "Clientes";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?> | El Papelito</title>

    <link rel="stylesheet" href="/el_papelito/public/assets/css/proveedores.css">
    <link rel="stylesheet" href="/el_papelito/public/assets/css/clientes.css">
</head>
<body>

    <main class="contenido-proveedores">

        <header class="encabezado">
            <div>
                <h1>Clientes</h1>
                <p>Administra la información de tus clientes.</p>
            </div>

            <button type="button" id="btnNuevoCliente">
                + Nuevo cliente
            </button>
        </header>

        <!-- Notificaciones del sistema -->

        <?php if (isset($_GET['registro'])): ?>

            <?php if ($_GET['registro'] === 'exitoso'): ?>
                <div class="mensaje mensaje-exito">
                    <strong>¡Cliente registrado!</strong>
                    El cliente se agregó correctamente.
                </div>
            <?php else: ?>
                <div class="mensaje mensaje-error">
                    <strong>Error al registrar.</strong>
                    No fue posible registrar el cliente.
                </div>
            <?php endif; ?>

        <?php elseif (isset($_GET['actualizacion'])): ?>

            <?php if ($_GET['actualizacion'] === 'exitosa'): ?>
                <div class="mensaje mensaje-exito">
                    <strong>¡Cambios guardados!</strong>
                    Los datos del cliente se actualizaron correctamente.
                </div>
            <?php else: ?>
                <div class="mensaje mensaje-error">
                    <strong>Error al actualizar.</strong>
                    No fue posible modificar los datos del cliente.
                </div>
            <?php endif; ?>

        <?php elseif (isset($_GET['estado'])): ?>

            <?php if ($_GET['estado'] === 'exitoso'): ?>
                <div class="mensaje mensaje-exito">
                    <strong>¡Estado actualizado!</strong>
                    El estado del cliente cambió correctamente.
                </div>
            <?php else: ?>
                <div class="mensaje mensaje-error">
                    <strong>Error al cambiar el estado.</strong>
                    No fue posible actualizar el cliente.
                </div>
            <?php endif; ?>

        <?php endif; ?>


        <!-- Filtros de búsqueda -->

        <section class="filtros">

            <form method="GET" action="/el_papelito/public/clientes">

                <input
                    type="text"
                    name="busqueda"
                    placeholder="Buscar por nombre, teléfono o correo..."
                    value="<?= htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8') ?>"
                >

                <select name="estado">
                    <option value="todos" <?= $estadoFiltro === 'todos' ? 'selected' : '' ?>>
                        Todos
                    </option>

                    <option value="activos" <?= $estadoFiltro === 'activos' ? 'selected' : '' ?>>
                        Activos
                    </option>

                    <option value="inactivos" <?= $estadoFiltro === 'inactivos' ? 'selected' : '' ?>>
                        Inactivos
                    </option>
                </select>

                <button type="submit">Filtrar</button>

            </form>

        </section>


        <!-- Tabla de clientes -->

        <section class="tabla-contenedor">

            <table>
                <thead>
                    <tr>
                        <th>Nombre completo</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Dirección</th>
                        <th>Fecha de registro</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (empty($clientes)): ?>

                        <tr>
                            <td colspan="7" class="sin-resultados">
                                No se encontraron clientes.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($clientes as $cliente): ?>

                            <tr>
                                <td>
                                    <?= htmlspecialchars($cliente['nombre_completo'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($cliente['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($cliente['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($cliente['direccion'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?= date('d/m/Y', strtotime($cliente['fecha_registro'])) ?>
                                </td>

                                <td>
                                    <?= $cliente['activo'] ? 'Activo' : 'Inactivo' ?>
                                </td>

                                <td class="acciones-cliente">

                                    <button
                                        type="button"
                                        class="btn-editar-cliente"
                                        data-id="<?= (int)$cliente['id_cliente'] ?>">
                                        Editar
                                    </button>

                                    <form
                                        method="POST"
                                        action="/el_papelito/public/clientes/estado"
                                        onsubmit="return confirm('¿Deseas cambiar el estado de este cliente?');">

                                        <input
                                            type="hidden"
                                            name="id_cliente"
                                            value="<?= (int)$cliente['id_cliente'] ?>">

                                        <input
                                            type="hidden"
                                            name="estado"
                                            value="<?= $cliente['activo'] ? 0 : 1 ?>">

                                        <button
                                            type="submit"
                                            class="btn-estado-cliente <?= $cliente['activo'] ? 'desactivar' : 'activar' ?>">

                                            <?= $cliente['activo'] ? 'Desactivar' : 'Activar' ?>

                                        </button>

                                    </form>

                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>
            </table>


            <!-- Paginación -->

            <?php if ($totalPaginas > 1): ?>

                <div class="paginacion-clientes">

                    <?php
                    $parametrosPaginacion = $_GET;
                    ?>

                    <?php if ($paginaActual > 1): ?>

                        <?php
                        $parametrosPaginacion['pagina'] = $paginaActual - 1;
                        ?>

                        <a href="?<?= htmlspecialchars(http_build_query($parametrosPaginacion), ENT_QUOTES, 'UTF-8') ?>">
                            Anterior
                        </a>

                    <?php endif; ?>


                    <span>
                        Página <?= $paginaActual ?> de <?= $totalPaginas ?>
                    </span>


                    <?php if ($paginaActual < $totalPaginas): ?>

                        <?php
                        $parametrosPaginacion['pagina'] = $paginaActual + 1;
                        ?>

                        <a href="?<?= htmlspecialchars(http_build_query($parametrosPaginacion), ENT_QUOTES, 'UTF-8') ?>">
                            Siguiente
                        </a>

                    <?php endif; ?>

                </div>

            <?php endif; ?>

        </section>

    </main>


    <!-- Modal para registrar y editar clientes -->

    <div class="modal-overlay" id="modalCliente">

        <div class="modal-container">

            <div class="modal-header">
                <div>
                    <h2 id="tituloModalCliente">Nuevo cliente</h2>
                    <p>Completa la información del cliente.</p>
                </div>

                <button
                    type="button"
                    id="cerrarModalCliente"
                    class="btn-cerrar">
                    &times;
                </button>
            </div>

            <form
                id="formCliente"
                action="/el_papelito/public/clientes/crear"
                method="POST">

                <input
                    type="hidden"
                    name="id_cliente"
                    id="id_cliente">

                <div class="form-grid">

                    <div class="form-group full-width">
                        <label for="nombre_completo">
                            Nombre completo *
                        </label>

                        <input
                            type="text"
                            name="nombre_completo"
                            id="nombre_completo"
                            maxlength="120"
                            required>
                    </div>


                    <div class="form-group">
                        <label for="telefono">
                            Teléfono
                        </label>

                        <input
                            type="text"
                            name="telefono"
                            id="telefono"
                            maxlength="25">
                    </div>


                    <div class="form-group">
                        <label for="correo">
                            Correo electrónico
                        </label>

                        <input
                            type="email"
                            name="correo"
                            id="correo"
                            maxlength="120">
                    </div>


                    <div class="form-group full-width">
                        <label for="direccion">
                            Dirección
                        </label>

                        <textarea
                            name="direccion"
                            id="direccion"
                            rows="3"
                            maxlength="255"></textarea>
                    </div>

                </div>


                <div class="modal-actions">

                    <button
                        type="button"
                        id="cancelarModalCliente">
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        id="btnGuardarCliente">
                        Guardar cliente
                    </button>

                </div>

            </form>

        </div>

    </div>


    <script src="/el_papelito/public/assets/js/clientes.js"></script>

</body>
</html>
