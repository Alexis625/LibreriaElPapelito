<?php include __DIR__ . '/../layouts/cliente_topbar.php'; ?>

<section class="welcome-section">
    <h1>Mis Reservas 🛒</h1>
    <p>Revisa los productos que vas a reservar.</p>
</section>

<section class="dashboard-card">
    <table class="tabla-cliente">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unit.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Cuaderno Profesional</td>
                <td>2</td>
                <td>$15.00</td>
                <td>$30.00</td>
            </tr>
            <tr>
                <td>El Principito</td>
                <td>1</td>
                <td>$12.50</td>
                <td>$12.50</td>
            </tr>
            <tr>
                <td>Set de Plumas</td>
                <td>1</td>
                <td>$25.00</td>
                <td>$25.00</td>
            </tr>
        </tbody>
    </table>

    <div class="total-reserva">
        <h3>Total: $67.50</h3>
        <button class="btn-confirmar">Confirmar Reserva ✅</button>
    </div>
</section>

    </main>
</div>

</body>
</html>