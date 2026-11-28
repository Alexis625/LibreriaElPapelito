
<?php include __DIR__ . '/../layouts/cliente_topbar.php'; ?>

<section class="welcome-section">
    <h1>Nuestros Productos 📦</h1>
    <p>Selecciona la cantidad y agrégalos a tu reserva.</p>
</section>

<section class="dashboard-card">

    <div class="products-grid">

        <?php if (!empty($productos)): ?>

            <?php foreach ($productos as $producto): ?>

                <div class="product-card">

                    <div class="product-thumb">📦</div>

                    <span class="product-cat">
                        <?= htmlspecialchars($producto['categoria'] ?? 'Sin categoría', ENT_QUOTES, 'UTF-8') ?>
                    </span>

                    <div class="product-info">

                        <h4>
                            <?= htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8') ?>
                        </h4>

                        <p>
                            Disponibles: <?= (int)$producto['existencia'] ?>
                        </p>

                    </div>

                    <div class="product-footer">
                        <span class="product-price">
                            $<?= number_format((float)$producto['precio_venta'], 2) ?>
                        </span>
                    </div>

                    <form
                        action="/el_papelito/public/cliente/carrito/agregar"
                        method="POST"
                    >

                        <input
                            type="hidden"
                            name="id_producto"
                            value="<?= (int)$producto['id_producto'] ?>"
                        >

                        <label style="display:block; margin:12px 0 5px;">
                            Cantidad
                        </label>

                        <input
                            type="number"
                            name="cantidad"
                            value="1"
                            min="1"
                            max="<?= (int)$producto['existencia'] ?>"
                            required
                            style="width:100%; padding:10px; border:1px solid #d88acb; border-radius:8px; box-sizing:border-box; margin-bottom:12px;"
                        >

                        <button
                            type="submit"
                            class="btn-reserve"
                            <?= (int)$producto['existencia'] <= 0 ? 'disabled' : '' ?>
                        >
                            Agregar a reserva
                        </button>

                    </form>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p>No hay productos disponibles actualmente.</p>

        <?php endif; ?>

    </div>

</section>

</main>
</div>
</body>
</html>
