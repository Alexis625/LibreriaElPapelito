<?php include __DIR__ . '/../layouts/cliente_topbar.php'; ?>

<section class="welcome-section">
    <h1>Mi Historial de Reservas 📜</h1>
    <p>Aquí puedes ver las reservas que has realizado anteriormente.</p>
</section>

<section class="dashboard-card">
    <table class="tabla-cliente">
        <thead>
            <tr>
                <th>ID Reserva</th>
                <th>Fecha</th>
                <th>Productos</th>
                <th>Total</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>#1024</td>
                <td>25/11/2026</td>
                <td>2x Cuaderno, 1x Libro</td>
                <td>$42.50</td>
                <td><span class="estado estado-ok">Completada</span></td>
            </tr>
            <tr>
                <td>#1025</td>
                <td>27/11/2026</td>
                <td>1x Set de Plumas</td>
                <td>$25.00</td>
                <td><span class="estado estado-pendiente">Pendiente</span></td>
            </tr>
            <tr>
                <td>#1026</td>
                <td>26/11/2026</td>
                <td>3x El Principito</td>
                <td>$37.50</td>
                <td><span class="estado estado-ok">Completada</span></td>
            </tr>
            <tr>
                <td>#1027</td>
                <td>20/11/2026</td>
                <td>1x Agenda 2026</td>
                <td>$18.00</td>
                <td><span class="estado estado-cancelada">Cancelada</span></td>
            </tr>
        </tbody>
    </table>
</section>

    </main>
</div>

</body>
</html>