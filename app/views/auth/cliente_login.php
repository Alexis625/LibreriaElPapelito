
<?php
$titulo = "Iniciar sesión";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $titulo ?> | El Papelito</title>

    <link rel="stylesheet" href="/el_papelito/public/assets/css/login.css">

    <style>
        .enlace-cuenta {
            margin-top: 22px;
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

        .mensaje-exito {
            padding: 12px;
            margin-bottom: 15px;
            background: #edfff3;
            color: #21834b;
            border-radius: 8px;
            font-size: 13px;
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
                Todo lo que necesitas para escribir,
                crear y hacer realidad tus ideas.
            </p>

        </section>

        <!-- Panel de inicio de sesión -->
        <section class="login-form-container">

            <h2>¡Bienvenido!</h2>

            <p class="login-description">
                Ingresa a tu cuenta de cliente.
            </p>

            <?php if (isset($_GET['registro']) && $_GET['registro'] === 'exitoso'): ?>
                <div class="mensaje-exito">
                    ¡Tu cuenta fue creada correctamente! Ya puedes iniciar sesión.
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="login-error">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form
                class="login-form"
                action="/el_papelito/public/cliente/login"
                method="POST"
            >

                <label for="correo">
                    Correo electrónico
                </label>

                <input
                    type="email"
                    id="correo"
                    name="correo"
                    placeholder="Ingresa tu correo"
                    required
                >

                <label for="password">
                    Contraseña
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Ingresa tu contraseña"
                    required
                >

                <button type="submit">
                    Iniciar sesión
                </button>

            </form>

            <p class="enlace-cuenta">
                ¿Todavía no tienes una cuenta?
                <a href="/el_papelito/public/cliente/registro">
                    Regístrate aquí
                </a>
            </p>

        </section>

    </main>

</body>
</html>
