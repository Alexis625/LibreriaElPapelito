<?php

$titulo = "Usuarios";

$escapar = function ($valor) {
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
};

$mensajeUsuario = $_SESSION['mensaje_usuario'] ?? null;

unset($_SESSION['mensaje_usuario']);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $titulo ?> | Mini Librería El Papelito</title>

    <link rel="stylesheet" href="/el_papelito/public/assets/css/layouts.css">
<link rel="stylesheet" href="/el_papelito/public/assets/css/usuarios.css">
</head>

<body>

    <div class="app-layout">
        <?php
        $moduloActual = 'usuarios';
        require __DIR__ . '/../layouts/sidebar.php';
        ?>

        <div class="main-area">
            <?php require __DIR__ . '/../layouts/topbar.php'; ?>

            <main class="usuarios-container">

        <!-- ENCABEZADO -->
        <header class="usuarios-header">
            <div>
                <span class="usuarios-etiqueta">ADMINISTRACIÓN DEL SISTEMA</span>
                <h1>Usuarios</h1>
                <p>Administra las cuentas de acceso de tu librería.</p>
            </div>

            <button type="button" id="btnNuevoUsuario">
                + Nuevo usuario
            </button>
        </header>


        <!-- FILTROS -->
        <section class="usuarios-filtros">

            <form method="GET" action="/el_papelito/public/usuarios">

                <div class="busqueda-usuarios">

                    <input type="text" name="busqueda" placeholder="Buscar por nombre, usuario o correo..."
                        value="<?= $escapar($busqueda) ?>">

                    <button type="submit">
                        Buscar
                    </button>

                </div>

                <div class="filtro-estado">

                    <label for="filtroEstado">Mostrar:</label>

                    <select name="estado" id="filtroEstado" onchange="this.form.submit()">
                        <option value="todos" <?= $estadoFiltro === 'todos' ? 'selected' : '' ?>>
                            Todos los usuarios
                        </option>

                        <option value="activos" <?= $estadoFiltro === 'activos' ? 'selected' : '' ?>>
                            Usuarios activos
                        </option>

                        <option value="inactivos" <?= $estadoFiltro === 'inactivos' ? 'selected' : '' ?>>
                            Usuarios inactivos
                        </option>
                    </select>

                </div>

            </form>

        </section>


        <!-- MENSAJES -->
        <?php if (isset($_GET['registro'])): ?>

            <?php if ($_GET['registro'] === 'exitoso'): ?>

                <div class="mensaje mensaje-exito">
                    Usuario registrado correctamente.
                </div>

            <?php else: ?>

                <div class="mensaje mensaje-error">
    <?= $mensajeUsuario
        ? $escapar($mensajeUsuario)
        : 'No se pudo registrar el usuario. Verifica los datos ingresados.' ?>
</div>

            <?php endif; ?>

        <?php endif; ?>


        <?php if (isset($_GET['actualizacion'])): ?>

            <?php if ($_GET['actualizacion'] === 'exitosa'): ?>

                <div class="mensaje mensaje-exito">
                    Usuario actualizado correctamente.
                </div>

            <?php else: ?>

                <div class="mensaje mensaje-error">
    <?= $mensajeUsuario
        ? $escapar($mensajeUsuario)
        : 'No se pudo actualizar el usuario. Verifica los datos ingresados.' ?>
</div>

            <?php endif; ?>

        <?php endif; ?>


        <?php if (isset($_GET['estado'])): ?>

            <?php if ($_GET['estado'] === 'exitoso'): ?>

                <div class="mensaje mensaje-exito">
                    El estado del usuario se cambió correctamente.
                </div>

            <?php else: ?>

                <div class="mensaje mensaje-error">
                    No se pudo cambiar el estado del usuario.
                </div>

            <?php endif; ?>

        <?php endif; ?>


        <!-- LISTADO DE USUARIOS -->
        <section class="usuarios-card">

            <div class="usuarios-card-header">

                <div>
                    <h2>Listado de usuarios</h2>
                    <p>Consulta y administra las cuentas registradas.</p>
                </div>

                <span class="usuarios-total">
                    <?= count($usuarios) ?> usuarios registrados
                </span>

            </div>


            <div class="tabla-contenedor">

                <table>

                    <thead>
                        <tr>
                            <th>N.º</th>
                            <th>Nombre completo</th>
                            <th>Nombre de usuario</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Fecha de registro</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>


                    <tbody>

                        <?php if (!empty($usuarios)): ?>

                            <?php foreach ($usuarios as $usuario): ?>

                                <tr>

                                    <td>
                                        #<?= (int) $usuario['id_usuario'] ?>
                                    </td>

                                    <td>
                                        <?= $escapar($usuario['nombre_completo']) ?>
                                    </td>

                                    <td>
                                        <?= $escapar($usuario['nombre_usuario']) ?>
                                    </td>

                                    <td>
                                        <?= $escapar($usuario['correo'] ?: '—') ?>
                                    </td>

                                    <td>
                                        <span class="rol-usuario">
                                            <?= $escapar($usuario['rol']) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= date(
                                            'd/m/Y',
                                            strtotime($usuario['fecha_creacion'])
                                        ) ?>
                                    </td>

                                    <td>

                                        <?php if ($usuario['activo']): ?>

                                            <span class="estado-usuario activo">
                                                Activo
                                            </span>

                                        <?php else: ?>

                                            <span class="estado-usuario inactivo">
                                                Inactivo
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td class="acciones-usuario">

                                        <button type="button" class="btn-editar-usuario"
                                            data-id="<?= (int) $usuario['id_usuario'] ?>">
                                            Editar
                                        </button>

                                        <form action="/el_papelito/public/usuarios/estado" method="POST"
                                            onsubmit="return confirm('¿Deseas cambiar el estado de este usuario?');">

                                            <input type="hidden" name="id_usuario" value="<?= (int) $usuario['id_usuario'] ?>">

                                            <input type="hidden" name="estado" value="<?= $usuario['activo'] ? 0 : 1 ?>">

                                            <button type="submit"
                                                class="<?= $usuario['activo'] ? 'btn-desactivar' : 'btn-activar' ?>">
                                                <?= $usuario['activo'] ? 'Desactivar' : 'Activar' ?>
                                            </button>

                                        </form>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="8" class="tabla-vacia">
                                    No se encontraron usuarios registrados.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

                </section>

            </main>
        </div>
    </div>

    <!-- MODAL: REGISTRAR Y EDITAR USUARIO -->


    <!-- MODAL: REGISTRAR Y EDITAR USUARIO -->
    <div class="modal-overlay" id="modalUsuario">

        <div class="modal-container">

            <div class="modal-header">

                <div>
                    <span>ADMINISTRACIÓN DEL SISTEMA</span>

                    <h2 id="tituloModalUsuario">
                        Nuevo usuario
                    </h2>

                    <p id="descripcionModalUsuario">
                        Completa la información de la cuenta.
                    </p>
                </div>

                <button type="button" id="btnCerrarModal" aria-label="Cerrar">
                    &times;
                </button>

            </div>


            <form id="formUsuario" action="/el_papelito/public/usuarios/crear" method="POST">

                <input type="hidden" name="id_usuario" id="id_usuario">

                <div class="form-grid">

                    <div class="form-group full-width">

                        <label for="nombre_completo">
                            Nombre completo
                        </label>

                        <input type="text" id="nombre_completo" name="nombre_completo" maxlength="120"
                            pattern="[A-Za-zÁÉÍÓÚáéíóúÜüÑñ]+([ '-][A-Za-zÁÉÍÓÚáéíóúÜüÑñ]+)*"
                            title="Ingresa solamente letras, espacios, guiones o apóstrofes." required>

                    </div>


                    <div class="form-group">

                        <label for="nombre_usuario">
                            Nombre de usuario
                        </label>

                        <input type="text" id="nombre_usuario" name="nombre_usuario" maxlength="50" readonly required>

                    </div>


                    <div class="form-group">

                        <label for="correo">
                            Correo electrónico
                        </label>

                        <input type="email" id="correo" name="correo" maxlength="120" autocomplete="email">

                    </div>


                    <div class="form-group">

                        <label for="id_rol">
                            Rol del usuario
                        </label>

                        <select id="id_rol" name="id_rol" required>

                            <option value="">
                                Selecciona un rol
                            </option>

                            <?php foreach ($roles as $rol): ?>

                                <option value="<?= (int) $rol['id_rol'] ?>">
                                    <?= $escapar($rol['nombre']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="form-group">

                        <label for="password">
                            Contraseña
                        </label>

                        <div class="password-container">
                            <input type="password" id="password" name="password" minlength="8"
                                autocomplete="new-password">

                            <button type="button" id="btnMostrarPassword" aria-label="Mostrar contraseña"
                                title="Mostrar contraseña">
                                <span id="iconoPassword">Mostrar</span>
                            </button>
                        </div>

                        <small id="ayudaPassword">
                            Mínimo 8 caracteres.
                        </small>

                    </div>

                </div>


                <div class="modal-actions">

                    <button type="button" id="btnCancelarModal">
                        Cancelar
                    </button>

                    <button type="submit" id="btnGuardarUsuario">
                        Registrar usuario
                    </button>

                </div>

            </form>

        </div>

    </div>


    <!-- FUNCIONALIDAD DE LA VISTA -->
    <script>

        const modalUsuario = document.getElementById('modalUsuario');
        const formUsuario = document.getElementById('formUsuario');

        const btnNuevoUsuario = document.getElementById('btnNuevoUsuario');
        const btnCerrarModal = document.getElementById('btnCerrarModal');
        const btnCancelarModal = document.getElementById('btnCancelarModal');

        const tituloModal = document.getElementById('tituloModalUsuario');
        const descripcionModal = document.getElementById('descripcionModalUsuario');
        const btnGuardar = document.getElementById('btnGuardarUsuario');

        const campoPassword = document.getElementById('password');
        const ayudaPassword = document.getElementById('ayudaPassword');

        function abrirModalUsuario() {
            modalUsuario.classList.add('activo');
            document.body.style.overflow = 'hidden';
        }

        function cerrarModalUsuario() {
            modalUsuario.classList.remove('activo');
            document.body.style.overflow = '';
        }

        function limpiarFormularioUsuario() {

            formUsuario.reset();

            document.getElementById('id_usuario').value = '';

            formUsuario.action = '/el_papelito/public/usuarios/crear';

            tituloModal.textContent = 'Nuevo usuario';

            descripcionModal.textContent =
                'Completa la información de la cuenta.';

            btnGuardar.textContent = 'Registrar usuario';

            campoPassword.required = true;
            campoPassword.value = '';

            ayudaPassword.textContent =
                'Mínimo 8 caracteres.';
            campoNombreUsuario.value = '';
            campoPassword.type = 'password';
            iconoPassword.textContent = 'Mostrar';

        }


        // Abrir formulario de registro
        btnNuevoUsuario.addEventListener('click', () => {

            limpiarFormularioUsuario();

            abrirModalUsuario();

        });


        // Cerrar modal
        btnCerrarModal.addEventListener('click', cerrarModalUsuario);
        btnCancelarModal.addEventListener('click', cerrarModalUsuario);


        modalUsuario.addEventListener('click', (evento) => {

            if (evento.target === modalUsuario) {
                cerrarModalUsuario();
            }

        });


        document.addEventListener('keydown', (evento) => {

            if (evento.key === 'Escape') {
                cerrarModalUsuario();
            }

        });


        // Editar usuario
        document.querySelectorAll('.btn-editar-usuario').forEach(boton => {

            boton.addEventListener('click', async () => {

                const idUsuario = boton.dataset.id;

                limpiarFormularioUsuario();

                tituloModal.textContent = 'Editar usuario';

                descripcionModal.textContent =
                    'Modifica la información de la cuenta.';

                btnGuardar.textContent = 'Guardar cambios';

                campoPassword.required = false;

                ayudaPassword.textContent =
                    'Déjala vacía si no deseas cambiar la contraseña.';

                formUsuario.action =
                    '/el_papelito/public/usuarios/actualizar';

                try {

                    const respuesta = await fetch(
                        '/el_papelito/public/usuarios/editar?id=' +
                        encodeURIComponent(idUsuario)
                    );

                    if (!respuesta.ok) {
                        throw new Error('No se pudo consultar el usuario.');
                    }

                    const usuario = await respuesta.json();

                    document.getElementById('id_usuario').value =
                        usuario.id_usuario;

                    document.getElementById('nombre_completo').value =
                        usuario.nombre_completo;

                    document.getElementById('nombre_usuario').value =
                        usuario.nombre_usuario;

                    document.getElementById('correo').value =
                        usuario.correo ?? '';

                    document.getElementById('id_rol').value =
                        usuario.id_rol;

                    abrirModalUsuario();

                } catch (error) {

                    alert(error.message);

                }

            });

        });
        // ==========================================
        // GENERAR NOMBRE DE USUARIO AUTOMÁTICAMENTE
        // ==========================================

        const campoNombreCompleto = document.getElementById('nombre_completo');
        const campoNombreUsuario = document.getElementById('nombre_usuario');

        function generarNombreUsuario() {

            const palabras = campoNombreCompleto.value
                .trim()
                .toLocaleLowerCase('es')
                .split(/\s+/)
                .filter(Boolean);

            if (palabras.length < 2) {
                campoNombreUsuario.value = '';
                return;
            }

            const primerNombre = palabras[0];

            // Si hay dos palabras, la segunda se toma como apellido.
            // Si hay tres o más, la tercera corresponde al primer apellido.
            const primerApellido = palabras.length >= 3
                ? palabras[2]
                : palabras[1];

            campoNombreUsuario.value =
                primerApellido + '.' + primerNombre;
        }

        campoNombreCompleto.addEventListener(
            'input',
            generarNombreUsuario
        );


        // ==========================================
        // MOSTRAR Y OCULTAR CONTRASEÑA
        // ==========================================

        const btnMostrarPassword = document.getElementById('btnMostrarPassword');
        const iconoPassword = document.getElementById('iconoPassword');

        btnMostrarPassword.addEventListener('click', () => {

            const mostrar = campoPassword.type === 'password';

            campoPassword.type = mostrar ? 'text' : 'password';

            iconoPassword.textContent = mostrar ? 'Ocultar' : 'Mostrar';

            btnMostrarPassword.setAttribute(
                'aria-label',
                mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña'
            );

            btnMostrarPassword.title = mostrar
                ? 'Ocultar contraseña'
                : 'Mostrar contraseña';
        });


        // ==========================================
        // MENSAJES TEMPORALES
        // ==========================================

        document.querySelectorAll('.mensaje').forEach(mensaje => {

            setTimeout(() => {

                mensaje.style.transition = 'opacity 0.4s ease';
                mensaje.style.opacity = '0';

                setTimeout(() => {
                    mensaje.remove();
                }, 400);

            }, 4000);

        });

    </script>

</body>

</html>     