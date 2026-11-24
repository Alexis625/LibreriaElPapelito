
<?php
$nombreUsuario = $_SESSION['usuario']['nombre_completo'] ?? 'Administrador';
$inicialUsuario = strtoupper(substr($nombreUsuario, 0, 1));
?>

<header class="topbar">

    <!-- IDENTIDAD DEL SISTEMA -->
    <div class="topbar-brand">
        <strong>Mini Librería El Papelito</strong>
        <span>Panel de administración</span>
    </div>

    <!-- INFORMACIÓN DEL USUARIO -->
    <div class="topbar-user">

        <span class="notification-dot"></span>

        <div class="topbar-user-info">
            <strong>
                <?= htmlspecialchars($nombreUsuario) ?>
            </strong>

            <span>
                Mi cuenta ·
                <a href="/el_papelito/public/logout">Salir</a>
            </span>
        </div>

        <!-- MENÚ DE USUARIO PARA TELÉFONOS -->
        <details class="topbar-account">

            <summary class="topbar-avatar">
                <?= htmlspecialchars($inicialUsuario) ?>
            </summary>

            <div class="topbar-account-menu">
                <strong><?= htmlspecialchars($nombreUsuario) ?></strong>
                <a href="/el_papelito/public/logout">Cerrar sesión</a>
            </div>

        </details>

    </div>

</header>
