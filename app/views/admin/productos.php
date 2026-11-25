<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos | Mini Librería El Papelito</title>
    <link rel="stylesheet" href="/el_papelito/public/assets/css/layouts.css">
<link rel="stylesheet" href="/el_papelito/public/assets/css/productos.css">
</head>

<body>

    <div class="app-layout">

        <?php
        $moduloActual = 'productos';
        require __DIR__ . '/../layouts/sidebar.php';
        ?>

        <div class="main-area">

            <?php require __DIR__ . '/../layouts/topbar.php'; ?>

            <main class="productos-container">

        <!-- Encabezado -->
        <header class="productos-header">
            <div>
                <span>GESTIÓN DE INVENTARIO</span>
                <h1>Productos</h1>
                <p>Administra los productos de tu librería.</p>
            </div>

            <button type="button" id="btnNuevoProducto">
                + Nuevo producto
            </button>
        </header>



        <!-- Búsqueda y filtros de productos -->
        <div class="productos-filtros">

            <form method="GET" action="/el_papelito/public/productos">

                <div class="busqueda-productos">

                    <input type="text" name="busqueda" placeholder="Buscar por código o nombre..."
                        value="<?= htmlspecialchars($busqueda) ?>">

                    <button type="submit">
                        Buscar
                    </button>

                </div>

                <div class="filtro-estado">

                    <label for="filtroEstado">Mostrar:</label>

                    <select name="estado" id="filtroEstado" onchange="this.form.submit()">
                        <option value="todos" <?= $estadoFiltro === 'todos' ? 'selected' : '' ?>>
                            Todos los productos
                        </option>

                        <option value="activos" <?= $estadoFiltro === 'activos' ? 'selected' : '' ?>>
                            Productos activos
                        </option>

                        <option value="inactivos" <?= $estadoFiltro === 'inactivos' ? 'selected' : '' ?>>
                            Productos inactivos
                        </option>

                    </select>

                </div>

            </form>

        </div>



        <!-- Mensaje de registro -->
        <?php if (isset($_GET['registro'])): ?>

            <?php if ($_GET['registro'] === 'exitoso'): ?>

                <div class="mensaje mensaje-exito">
                    Producto registrado correctamente.
                </div>

            <?php elseif ($_GET['registro'] === 'error'): ?>

                <div class="mensaje mensaje-error">
                    No se pudo registrar el producto. Verifica los datos ingresados.
                </div>

            <?php endif; ?>

        <?php endif; ?>


        <!-- Mensaje de actualización -->
        <?php if (isset($_GET['actualizacion'])): ?>

            <?php if ($_GET['actualizacion'] === 'exitosa'): ?>

                <div class="mensaje mensaje-exito">
                    Producto actualizado correctamente.
                </div>

            <?php elseif ($_GET['actualizacion'] === 'error'): ?>

                <div class="mensaje mensaje-error">
                    No se pudo actualizar el producto. Verifica los datos ingresados.
                </div>

            <?php endif; ?>

        <?php endif; ?>


        <!-- Mensaje de cambio de estado -->
        <?php if (isset($_GET['estado'])): ?>

            <?php if ($_GET['estado'] === 'exitoso'): ?>

                <div class="mensaje mensaje-exito">
                    El estado del producto se cambió correctamente.
                </div>

            <?php elseif ($_GET['estado'] === 'error'): ?>

                <div class="mensaje mensaje-error">
                    No se pudo cambiar el estado del producto.
                </div>

            <?php endif; ?>

        <?php endif; ?>

        <!-- Mensaje de actualización de inventario -->
        <?php if (isset($_GET['inventario'])): ?>

            <?php if ($_GET['inventario'] === 'exitoso'): ?>
                <div class="mensaje mensaje-exito">
                    El inventario se actualizó correctamente.
                </div>
            <?php else: ?>
                <div class="mensaje mensaje-error">
                    No se pudo actualizar el inventario. Verifica las existencias e intenta nuevamente.
                </div>
            <?php endif; ?>

        <?php endif; ?>


        <!-- Listado de productos -->
        <section class="productos-card">

            <div class="productos-card-header">
                <h2>Listado de productos</h2>

                <span>
                    <?= count($productos) ?> productos registrados
                </span>
            </div>

            <div class="tabla-contenedor">

                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Precio venta</th>
                            <th>Existencias</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($productos)): ?>

                            <?php foreach ($productos as $producto): ?>
                                <tr>
                                    <td><?= htmlspecialchars($producto['codigo']) ?></td>

                                    <td><?= htmlspecialchars($producto['nombre']) ?></td>

                                    <td><?= htmlspecialchars($producto['categoria'] ?? 'Sin categoría') ?></td>

                                    <td>$<?= number_format($producto['precio_venta'], 2, ',', '.') ?></td>

                                    <td><?= (int) $producto['existencia'] ?></td>


                                    <td>
                                        <?php if ($producto['activo']): ?>

                                            <span class="estado-producto activo">
                                                Activo
                                            </span>

                                        <?php else: ?>

                                            <span class="estado-producto inactivo">
                                                Inactivo
                                            </span>

                                        <?php endif; ?>
                                    </td>


                                    <td>
                                        <button type="button" class="btn-editar-producto"
                                            data-id="<?= (int) $producto['id_producto'] ?>">
                                            Editar
                                        </button>
                                        <form action="/el_papelito/public/productos/estado" method="POST">

                                            <input type="hidden" name="id_producto"
                                                value="<?= (int) $producto['id_producto'] ?>">

                                            <input type="hidden" name="estado" value="<?= $producto['activo'] ? 0 : 1 ?>">

                                            <button type="submit">
                                                <?= $producto['activo'] ? 'Desactivar' : 'Activar' ?>
                                            </button>

                                        </form>
                                        <button type="button" class="btn-ajustar-inventario"
                                            data-id="<?= $producto['id_producto'] ?>"
                                            data-nombre="<?= htmlspecialchars($producto['nombre']) ?>">
                                            Ajustar inventario
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="7">No hay productos registrados.</td>
                            </tr>

                        <?php endif; ?>
                    </tbody>
                </table>

            </div>

        </section>

                </main>

        </div>

    </div>


    <!-- Modal: nuevo producto -->


    <!-- Modal: nuevo producto -->
    <div class="modal-overlay" id="modalProducto">

        <div class="modal-container">

            <div class="modal-header">
                <div>
                    <span>GESTIÓN DE INVENTARIO</span>
                    <h2>Nuevo producto</h2>
                    <p>Completa la información del producto.</p>
                </div>

                <button type="button" id="btnCerrarModal" aria-label="Cerrar">
                    &times;
                </button>
            </div>


            <form id="formProducto" action="/el_papelito/public/productos/crear" method="POST">
                <input type="hidden" name="id_producto" id="id_producto">

                <div class="form-grid">

                    <div class="form-group">
                        <label for="codigo">Código</label>
                        <input type="text" id="codigo" name="codigo" required>
                    </div>

                    <div class="form-group">
                        <label for="nombre">Nombre del producto</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>


                    <div class="form-group">
                        <label for="categoria">Categoría</label>

                        <div class="categoria-selector">

                            <select id="categoria" name="id_categoria" required>
                                <option value="">Selecciona una categoría</option>

                                <?php foreach ($categorias as $categoria): ?>

                                    <?php if ($categoria['activa']): ?>

                                        <option value="<?= $categoria['id_categoria'] ?>">
                                            <?= htmlspecialchars($categoria['nombre']) ?>
                                        </option>

                                    <?php endif; ?>

                                <?php endforeach; ?>

                            </select>

                            <button type="button" id="btnGestionarCategorias">
                                + Gestionar
                            </button>

                        </div>
                    </div>


                    <div class="form-group">
                        <label for="precio_compra">Precio de compra</label>
                        <input type="number" id="precio_compra" name="precio_compra" min="0" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label for="precio_venta">Precio de venta</label>
                        <input type="number" id="precio_venta" name="precio_venta" min="0" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label for="existencia">Existencia inicial</label>
                        <input type="number" id="existencia" name="existencia" min="0" step="1" value="0" required>
                    </div>

                    <div class="form-group">
                        <label for="stock_minimo">Stock mínimo</label>
                        <input type="number" id="stock_minimo" name="stock_minimo" min="0" step="1" value="0" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="3"></textarea>
                    </div>

                </div>

                <div class="modal-actions">
                    <button type="button" id="btnCancelarModal">
                        Cancelar
                    </button>

                    <button type="submit">
                        Agregar producto
                    </button>
                </div>

            </form>

        </div>

    </div>

    <!-- Modal para gestionar categorías -->
    <div class="modal-overlay" id="modalCategorias">

        <div class="modal-container">

            <div class="modal-header">
                <h2>Gestionar categorías</h2>

                <button type="button" id="btnCerrarCategorias">
                    &times;
                </button>
            </div>

            <!-- Formulario para agregar categoría -->
            <form id="formCategoria">

                <div class="form-group">
                    <label for="nombre_categoria">Nombre de la categoría</label>
                    <input type="text" id="nombre_categoria" name="nombre_categoria" placeholder="Ej. Cuadernos"
                        required>
                </div>

                <div class="form-group">
                    <label for="descripcion_categoria">Descripción</label>
                    <textarea id="descripcion_categoria" name="descripcion_categoria"
                        placeholder="Descripción de la categoría"></textarea>
                </div>

                <button type="submit">
                    Agregar categoría
                </button>

            </form>

            <div class="categorias-lista">
                <h3>Categorías registradas</h3>

                <div id="listaCategorias">
                    <!-- Aquí mostraremos las categorías -->
                </div>
            </div>

        </div>

    </div>



    <!-- Modal de ajustes de inventario -->
    <div class="modal-overlay" id="modalInventario">
        <div class="modal-container">

            <div class="modal-header">
                <div>
                    <span>CONTROL DE INVENTARIO</span>
                    <h2>Ajustar inventario</h2>
                    <p>Registra entradas y salidas de existencias.</p>
                </div>

                <button type="button" id="btnCerrarInventario">
                    &times;
                </button>
            </div>

            <form action="/el_papelito/public/productos/ajustar-inventario" method="POST" id="formInventario">

                <div class="form-grid">

                    <input type="hidden" name="id_producto" id="inventarioIdProducto">

                    <div class="form-group full-width">
                        <label for="inventarioNombreProducto">Producto</label>
                        <input type="text" id="inventarioNombreProducto" readonly>
                    </div>

                    <div class="form-group full-width">
                        <label for="tipoInventario">Tipo de movimiento</label>
                        <select name="tipo" id="tipoInventario" required>
                            <option value="AJUSTE_ENTRADA">
                                Entrada de inventario
                            </option>
                            <option value="AJUSTE_SALIDA">
                                Salida de inventario
                            </option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label for="cantidadInventario">Cantidad</label>
                        <input type="number" name="cantidad" id="cantidadInventario" min="1" step="1" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="observacionesInventario">Observaciones</label>
                        <textarea name="observaciones" id="observacionesInventario" rows="3"
                            placeholder="Motivo del ajuste (opcional)"></textarea>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" id="btnCancelarInventario">
                        Cancelar
                    </button>

                    <button type="submit">
                        Guardar movimiento
                    </button>
                </div>

            </form>
        </div>
    </div>



    <script src="/el_papelito/public/assets/js/productos.js"></script>

</body>

</html>