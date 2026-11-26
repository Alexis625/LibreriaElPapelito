<?php include __DIR__ . '/../layouts/cliente_topbar.php'; ?>

<section class="welcome-section">
    <h1>¡Bienvenido a El Papelito!</h1>
    <p>Todo lo que necesitas para escribir, crear y hacer realidad tus ideas.</p>
</section>

<section class="hero-banner">
    <div class="hero-text">
        <span class="hero-tag">Nueva Colección 2026</span>
        <h2>Papelería creativa y libros seleccionados para ti</h2>
        <p>Reserva tus artículos en línea y recógelos en tienda sin hacer fila.</p>
    </div>
    <a href="/LibreriaElPapelito/app/views/clientes/productos.php" class="hero-cta">Explorar catálogo</a>
</section>

<section class="dashboard-card">
    <h3>Explorar por categoría</h3>
    <div class="quick-access-buttons">
        <a href="/LibreriaElPapelito/app/views/clientes/productos.php">📒 Cuadernos y Libretas</a>
        <a href="/LibreriaElPapelito/app/views/clientes/productos.php">🖊️ Plumas y Marcadores</a>
        <a href="/LibreriaElPapelito/app/views/clientes/productos.php">📚 Literatura y Libros</a>
        <a href="/LibreriaElPapelito/app/views/clientes/productos.php">🎨 Arte y Manualidades</a>
    </div>
</section>

<section class="dashboard-card">
    <div class="section-header">
        <h3>Productos Destacados</h3>
        <a href="/LibreriaElPapelito/app/views/clientes/productos.php">Ver todos →</a>
    </div>

    <div class="products-grid">

        <div class="product-card">
            <div class="product-thumb">📒</div>
            <span class="product-cat">Papelería</span>
            <div class="product-info">
                <h4>Cuaderno Premium</h4>
            </div>
            <div class="product-footer">
                <span class="product-price">$15.00</span>
                <a href="/LibreriaElPapelito/app/views/clientes/reservas.php" class="btn-reserve">Reservar</a>
            </div>
        </div>

        <div class="product-card">
            <div class="product-thumb">📖</div>
            <span class="product-cat">Libros</span>
            <div class="product-info">
                <h4>El Principito</h4>
            </div>
            <div class="product-footer">
                <span class="product-price">$12.50</span>
                <a href="/LibreriaElPapelito/app/views/clientes/reservas.php" class="btn-reserve">Reservar</a>
            </div>
        </div>

        <div class="product-card">
            <div class="product-thumb">🖊️</div>
            <span class="product-cat">Escritura</span>
            <div class="product-info">
                <h4>Set de Plumas</h4>
            </div>
            <div class="product-footer">
                <span class="product-price">$25.00</span>
                <a href="/LibreriaElPapelito/app/views/clientes/reservas.php" class="btn-reserve">Reservar</a>
            </div>
        </div>

        <div class="product-card">
            <div class="product-thumb">📚</div>
            <span class="product-cat">Colección</span>
            <div class="product-info">
                <h4>Cien Años de Soledad</h4>
            </div>
            <div class="product-footer">
                <span class="product-price">$20.00</span>
                <a href="/LibreriaElPapelito/app/views/clientes/reservas.php" class="btn-reserve">Reservar</a>
            </div>
        </div>

    </div>
</section>

    </main>
</div>

</body>
</html>