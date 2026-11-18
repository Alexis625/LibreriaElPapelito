
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Iniciar sesión | El Papelito</title>

    <link rel="stylesheet" href="/el_papelito/public/assets/css/login.css">
</head>

<body>

    <main class="login-container">

        <!-- Panel de marca -->
        <section class="login-brand">

            <div class="brand-icon">
    <img src="/el_papelito/public/assets/img/logo_el_papelito.jpeg"
         alt="Logo de Mini Librería El Papelito">
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
                Ingresa tus credenciales para acceder al sistema.
            </p>

            <?php if (isset($error)): ?>
                <div class="login-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form
                class="login-form"
                action="/el_papelito/public/login"
                method="POST"
            >

                <label for="nombre_usuario">
                    Nombre de usuario
                </label>

                <input
                    type="text"
                    id="nombre_usuario"
                    name="nombre_usuario"
                    placeholder="Ingresa tu usuario"
                    required
                >

                <label for="password">
                    Contraseña
                </label>

                
<div class="password-container">

    <input
        type="password"
        id="password"
        name="password"
        placeholder="Ingresa tu contraseña"
        required
    >

    <button
        type="button"
        class="toggle-password"
        id="togglePassword"
        aria-label="Mostrar contraseña"
    >
        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg"
             width="20" height="20" viewBox="0 0 24 24"
             fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round"
             stroke-linejoin="round">
            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/>
            <circle cx="12" cy="12" r="3"/>
        </svg>
    </button>

</div>


                <button type="submit">
                    Iniciar sesión
                </button>

            </form>

        </section>

    </main>
    <script src="/el_papelito/public/assets/js/login.js"></script>

</body>
</html>
