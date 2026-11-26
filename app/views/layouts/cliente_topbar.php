<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librería El Papelito</title>
    <link rel="stylesheet" href="/LibreriaElPapelito/public/assets/css/cliente-publico.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="client-container">

    <nav class="client-navbar">
        <div class="navbar-inner">
            <a href="/LibreriaElPapelito/app/views/clientes/inicio.php" class="brand">
    <img src="/LibreriaElPapelito/public/assets/img/logo_el_papelito.jpeg" alt="Mini Librería El Papelito" class="brand-logo">
    <span>El Papelito</span>
</a>
            <div class="nav-links">
                <?php
                $paginaActual = basename($_SERVER['PHP_SELF']);
                ?>
                <a href="/LibreriaElPapelito/app/views/clientes/inicio.php" 
                   class="<?= $paginaActual === 'inicio.php' ? 'active' : '' ?>">Inicio</a>
                <a href="/LibreriaElPapelito/app/views/clientes/productos.php" 
                   class="<?= $paginaActual === 'productos.php' ? 'active' : '' ?>">Productos</a>
                <a href="/LibreriaElPapelito/app/views/clientes/historial.php" 
                   class="<?= $paginaActual === 'historial.php' ? 'active' : '' ?>">Historial</a>
                <a href="/LibreriaElPapelito/app/views/clientes/reservas.php" 
                   class="nav-btn-reserva <?= $paginaActual === 'reservas.php' ? 'active' : '' ?>">
                    Reservas <span class="badge-count">2</span>
                </a>
            </div>
        </div>
    </nav>

    <main class="client-content">