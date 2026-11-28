
<?php
$titulo = "Crear cuenta";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $titulo ?> | El Papelito</title>

    <link rel="stylesheet" href="/el_papelito/public/assets/css/login.css">

    <style>
        .login-container {
            max-width: 1050px;
        }

        .login-form-container {
            padding: 35px 55px;
        }

        .login-form {
            gap: 7px;
        }

        .login-form label {
            margin-top: 5px;
        }

        .login-form input {
            padding: 11px 14px;
        }

        .login-form button[type="submit"] {
            margin-top: 15px;
        }

        .enlace-cuenta {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
        }

        .enlace-cuenta a {
            color: #F044A8;
            font-weight: 700;
            text-decoration: none;
        }

        .enlace-cuenta a:hover {
            text-decoration: underline;
        }

        @media (max-width: 700px) {
            .login-form-container {
                padding: 30px 25px;
            }
        }
    </style>
</head>

<body>

    <main class="login-container">

        <!-- Panel de marca -->
        <section class="login-brand">

            <div class="brand-icon">
                <img
                    src="/el_papelito/public/assets/img/logo_el_papelito.jpeg"
                    alt="Logo de Mini Librería El Papelito"
                >
            </div>

            <h1>El Papelito</h1>

            <p>
                Crea tu cuenta y descubre todo lo que tenemos
                preparado para ti.
            </p>

        </section>

        <!-- Formulario de registro -->
        <section class="login-form-container">

            <h2>Crear cuenta</h2>

            <p class="login-description">
                Completa tus datos para registrarte.
            </p>

            <?php if (!empty($error)): ?>
                <div class="login-error">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form
                class="login-form"
                action="/el_papelito/public/cliente/registro"
                method="POST"
            >

                <label for="nombre_completo">Nombre completo *</label>

                <input
                    type="text"
                    id="nombre_completo"
                    name="nombre_completo"
                    maxlength="120"
                    placeholder="Tu nombre completo"
                    required
                >

                <label for="telefono">Teléfono</label>

                <input
                    type="text"
                    id="telefono"
                    name="telefono"
                    maxlength="25"
                    placeholder="Tu número de teléfono"
                >

                <label for="correo">Correo electrónico *</label>

                <input
                    type="email"
                    id="correo"
                    name="correo"
                    maxlength="120"
                    placeholder="ejemplo@correo.com"
                    required
                >

                <label for="direccion">Dirección</label>

                <input
                    type="text"
                    id="direccion"
                    name="direccion"
                    maxlength="255"
                    placeholder="Tu dirección"
                >

                <label for="password">Contraseña *</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    minlength="8"
                    placeholder="Mínimo 8 caracteres"
                    required
                >

                <label for="confirmar_password">Confirmar contraseña *</label>

                <input
                    type="password"
                    id="confirmar_password"
                    name="confirmar_password"
                    minlength="8"
                    placeholder="Repite tu contraseña"
                    required
                >

                <button type="submit">
                    Crear mi cuenta
                </button>

            </form>

            <p class="enlace-cuenta">
                ¿Ya tienes una cuenta?
                <a href="/el_papelito/public/cliente/login">
                    Inicia sesión
                </a>
            </p>

        </section>

    </main>

</body>
</html>
