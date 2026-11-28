
<?php include __DIR__ . '/../layouts/cliente_topbar.php'; ?>

<section class="welcome-section">
    <h1>Mis Reservas 🛒</h1>
    <p>Revisa y modifica los productos que deseas reservar.</p>
</section>

<section class="dashboard-card">

    <?php if (!empty($mensaje)): ?>
        <p class="mensaje-cliente">
            <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($items)): ?>

        <form action="/el_papelito/public/cliente/carrito/actualizar"
              method="POST">

            <div style="overflow-x:auto;">
                <table class="tabla-cliente" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unit.</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <input
                                        type="number"
                                        name="cantidades[<?= (int)$item['id_producto'] ?>]"
                                        value="<?= (int)$item['cantidad'] ?>"
                                        min="0"
                                        max="<?= (int)$item['existencia'] ?>"
                                        required
                                        style="width:85px; padding:10px; border:1px solid #d88acb; border-radius:8px; text-align:center; background:white; color:#222;"
                                    >
                                </td>

                                <td>
                                    $<?= number_format((float)$item['precio_venta'], 2) ?>
                                </td>

                                <td>
                                    $<?= number_format((float)$item['subtotal'], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <p style="font-size:13px; color:#777; margin-top:12px;">
                Puedes cambiar las cantidades. Escribe 0 para eliminar un producto.
            </p>

            <button type="submit" class="btn-reserve">
                Actualizar carrito
            </button>

        </form>

        <div class="total-reserva" style="margin-top:25px;">

            <h3>Total: $<?= number_format($total, 2) ?></h3>

            <form action="/el_papelito/public/cliente/reservas/confirmar"
                  method="POST">

                <label>Fecha límite de retiro (opcional)</label>

                <input
                    type="date"
                    name="fecha_limite_retiro"
                    min="<?= date('Y-m-d') ?>"
                    style="display:block; width:100%; padding:12px; margin:10px 0 15px; border:1px solid #ddd; border-radius:8px;"
                >

                <label>Observaciones (opcional)</label>

                <textarea
                    name="observaciones"
                    maxlength="300"
                    placeholder="Alguna indicación para tu reservación"
                    style="display:block; width:100%; min-height:90px; padding:12px; margin:10px 0 15px; border:1px solid #ddd; border-radius:8px;"
                ></textarea>

                <button type="submit" class="btn-confirmar">
                    Confirmar Reserva ✅
                </button>

            </form>

        </div>

    <?php else: ?>

        <p>Tu carrito está vacío. Agrega productos desde el catálogo.</p>

        <a href="/el_papelito/public/cliente/productos"
           class="btn-reserve">
            Ver productos
        </a>

    <?php endif; ?>

</section>

</main>
</div>
</body>
</html>
