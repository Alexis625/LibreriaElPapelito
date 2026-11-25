
<?php
$titulo = "Proveedores";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?> | El Papelito</title>

    <link rel="stylesheet" href="/el_papelito/public/assets/css/layouts.css">
<link rel="stylesheet" href="/el_papelito/public/assets/css/proveedores.css">
</head>
<body>

    <div class="app-layout">

        <?php
        $moduloActual = 'proveedores';
        require __DIR__ . '/../layouts/sidebar.php';
        ?>

        <div class="main-area">

            <?php require __DIR__ . '/../layouts/topbar.php'; ?>

            <main class="contenido-proveedores">

        <header class="encabezado">
            <div>
                <h1>Proveedores</h1>
                <p>Administra la información de tus proveedores.</p>
            </div>

            <button type="button" id="btnNuevoProveedor">
                + Nuevo proveedor
            </button>
        </header>


        <!-- Notificaciones del sistema -->

        <?php if (isset($_GET['registro'])): ?>

            <?php if ($_GET['registro'] === 'exitoso'): ?>
                <div class="mensaje mensaje-exito">
                    <strong>¡Proveedor registrado!</strong>
                    El proveedor se agregó correctamente.
                </div>
            <?php else: ?>
                <div class="mensaje mensaje-error">
                    <strong>Error al registrar.</strong>
                    No fue posible registrar el proveedor.
                </div>
            <?php endif; ?>

        <?php elseif (isset($_GET['actualizacion'])): ?>

            <?php if ($_GET['actualizacion'] === 'exitosa'): ?>
                <div class="mensaje mensaje-exito">
                    <strong>¡Cambios guardados!</strong>
                    Los datos del proveedor se actualizaron correctamente.
                </div>
            <?php else: ?>
                <div class="mensaje mensaje-error">
                    <strong>Error al actualizar.</strong>
                    No fue posible modificar los datos del proveedor.
                </div>
            <?php endif; ?>

        <?php elseif (isset($_GET['estado'])): ?>

            <?php if ($_GET['estado'] === 'exitoso'): ?>
                <div class="mensaje mensaje-exito">
                    <strong>¡Estado actualizado!</strong>
                    El estado del proveedor cambió correctamente.
                </div>
            <?php else: ?>
                <div class="mensaje mensaje-error">
                    <strong>Error al cambiar el estado.</strong>
                    No fue posible actualizar el proveedor.
                </div>
            <?php endif; ?>

        <?php elseif (isset($_GET['eliminacion'])): ?>

            <?php if ($_GET['eliminacion'] === 'exitosa'): ?>
                <div class="mensaje mensaje-exito">
                    <strong>¡Proveedor eliminado!</strong>
                    El proveedor se eliminó correctamente.
                </div>
            <?php else: ?>
                <div class="mensaje mensaje-error">
                    <strong>No se pudo eliminar.</strong>
                    Verifica que esté inactivo y que no tenga compras asociadas.
                </div>
            <?php endif; ?>

        <?php endif; ?>


        <section class="filtros">

            <form method="GET" action="/el_papelito/public/proveedores">

                <input
                    type="text"
                    name="busqueda"
                    placeholder="Buscar por empresa, contacto o teléfono..."
                    value="<?= htmlspecialchars($busqueda) ?>"
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

        <section class="tabla-contenedor">

            <table>
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <th>Contacto</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Dirección</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (empty($proveedores)): ?>

                        <tr>
                            <td colspan="7" class="sin-resultados">
                                No se encontraron proveedores.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($proveedores as $proveedor): ?>

                            <tr>
                                <td><?= htmlspecialchars($proveedor['nombre_empresa']) ?></td>

                                <td><?= htmlspecialchars($proveedor['nombre_contacto'] ?? '') ?></td>

                                <td><?= htmlspecialchars($proveedor['telefono'] ?? '') ?></td>

                                <td><?= htmlspecialchars($proveedor['correo'] ?? '') ?></td>

                                <td><?= htmlspecialchars($proveedor['direccion'] ?? '') ?></td>

                                <td>
                                    <?= $proveedor['activo'] ? 'Activo' : 'Inactivo' ?>
                                </td>

                                
<td class="acciones-proveedor">

    <button type="button" class="btn-editar-proveedor"
        data-id="<?= $proveedor['id_proveedor'] ?>">
        Editar
    </button>

    <form method="POST"
          action="/el_papelito/public/proveedores/estado"
          onsubmit="return confirm('¿Deseas cambiar el estado de este proveedor?');">

        <input type="hidden"
               name="id_proveedor"
               value="<?= $proveedor['id_proveedor'] ?>">

        <input type="hidden"
               name="estado"
               value="<?= $proveedor['activo'] ? 0 : 1 ?>">

        <button type="submit"
                class="btn-estado-proveedor <?= $proveedor['activo'] ? 'desactivar' : 'activar' ?>">

            <?= $proveedor['activo'] ? 'Desactivar' : 'Activar' ?>

        </button>
    </form>

    <?php if (!$proveedor['activo']): ?>

        <form method="POST"
              action="/el_papelito/public/proveedores/eliminar"
              onsubmit="return confirm('¿Estás seguro de eliminar definitivamente este proveedor? Esta acción no se puede deshacer.');">

            <input type="hidden"
                   name="id_proveedor"
                   value="<?= $proveedor['id_proveedor'] ?>">

            <button type="submit" class="btn-eliminar-proveedor">
                Eliminar
            </button>

        </form>

    <?php endif; ?>

</td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>
            </table>

        </section>

                </main>

        </div>

    </div>


    <!-- Modal para registrar y editar proveedores -->


<!-- Modal para registrar y editar proveedores -->
<div class="modal-overlay" id="modalProveedor">

    <div class="modal-container">

        <div class="modal-header">
            <div>
                <h2 id="tituloModalProveedor">Nuevo proveedor</h2>
                <p>Completa la información del proveedor.</p>
            </div>

            <button type="button" id="cerrarModalProveedor" class="btn-cerrar">
                &times;
            </button>
        </div>

        <form id="formProveedor"
              action="/el_papelito/public/proveedores/crear"
              method="POST">

            <input type="hidden" name="id_proveedor" id="id_proveedor">

            <div class="form-grid">

                <div class="form-group full-width">
                    <label for="nombre_empresa">Nombre de la empresa *</label>
                    <input type="text" name="nombre_empresa"
                           id="nombre_empresa" maxlength="150" required>
                </div>

                <div class="form-group">
                    <label for="nombre_contacto">Nombre del contacto</label>
                    <input type="text" name="nombre_contacto"
                           id="nombre_contacto" maxlength="120">
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="text" name="telefono"
                           id="telefono" maxlength="25">
                </div>

                <div class="form-group full-width">
                    <label for="correo">Correo electrónico</label>
                    <input type="email" name="correo"
                           id="correo" maxlength="120">
                </div>

                <div class="form-group full-width">
                    <label for="direccion">Dirección</label>
                    <textarea name="direccion"
                              id="direccion" rows="3"
                              maxlength="255"></textarea>
                </div>

            </div>

            <div class="modal-actions">
                <button type="button" id="cancelarModalProveedor">
                    Cancelar
                </button>

                <button type="submit" id="btnGuardarProveedor">
                    Guardar proveedor
                </button>
            </div>

        </form>

    </div>

</div>

<script src="/el_papelito/public/assets/js/proveedores.js"></script>
</body>
</html>
