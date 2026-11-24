
<?php
$nombreUsuario = $_SESSION['usuario']['nombre_completo'] ?? 'Administrador';
$correoUsuario = $_SESSION['usuario']['correo'] ?? '';

$moduloActual = $moduloActual ?? '';

$opcionesMenu = [
    ['nombre' => 'Resumen', 'ruta' => '/dashboard', 'modulo' => 'dashboard'],
    ['nombre' => 'Inventario', 'ruta' => '/productos', 'modulo' => 'inventario'],
    ['nombre' => 'Productos', 'ruta' => '/productos', 'modulo' => 'productos'],
    ['nombre' => 'Compras', 'ruta' => '/compras', 'modulo' => 'compras'],
    ['nombre' => 'Ventas', 'ruta' => '/ventas', 'modulo' => 'ventas'],
    ['nombre' => 'Reservas', 'ruta' => '/reservas', 'modulo' => 'reservas'],
    ['nombre' => 'Proveedores', 'ruta' => '/proveedores', 'modulo' => 'proveedores'],
    ['nombre' => 'Clientes', 'ruta' => '/clientes', 'modulo' => 'clientes'],
    ['nombre' => 'Usuarios', 'ruta' => '/usuarios', 'modulo' => 'usuarios'],
    ['nombre' => 'Reportes', 'ruta' => '/reportes', 'modulo' => 'reportes']
];
?>

<aside class="sidebar">

    <!-- ENCABEZADO DEL MENÚ -->
    <div class="sidebar-header">

        <a class="sidebar-brand" href="/el_papelito/public/dashboard">
            <img src="/el_papelito/public/assets/img/logo_el_papelito.jpeg" alt="Logo El Papelito">

            <span>
                <small>MINI LIBRERÍA</small>
                <strong>EL PAPELITO</strong>
            </span>
        </a>

        <!-- BOTÓN HAMBURGUESA -->
        <input type="checkbox" id="sidebar-toggle" class="sidebar-toggle-input">

        <label for="sidebar-toggle" class="sidebar-toggle-button" aria-label="Abrir o cerrar menú">
            <img
        src="/el_papelito/public/assets/img/logo_el_papelito.jpeg"
        alt="Logo El Papelito"
    >
        </label>

    </div>

    <!-- CONTENIDO DESPLEGABLE -->
    <div class="sidebar-content">

        <div class="sidebar-section-label">MENÚ PRINCIPAL</div>

        <nav class="sidebar-nav" aria-label="Menú principal">

            <?php foreach ($opcionesMenu as $opcion): ?>

                <a
                    href="/el_papelito/public<?= htmlspecialchars($opcion['ruta']) ?>"
                    class="<?= $moduloActual === $opcion['modulo'] ? 'active' : '' ?>"
                >
                    <?= htmlspecialchars($opcion['nombre']) ?>
                </a>

            <?php endforeach; ?>

        </nav>

        <!-- INFORMACIÓN DE SESIÓN -->
        <div class="sidebar-session">

            <span class="sidebar-section-label">SESIÓN INICIADA</span>

            <div class="session-user">

                <span class="avatar-letter">
                    <?= htmlspecialchars(strtoupper(substr($nombreUsuario, 0, 1))) ?>
                </span>

                <span>
                    <strong><?= htmlspecialchars($nombreUsuario) ?></strong>
                    <small><?= htmlspecialchars($correoUsuario) ?></small>
                </span>

            </div>

        </div>

    </div>

</aside>
