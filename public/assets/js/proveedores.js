
document.addEventListener('DOMContentLoaded', () => {

    const modal = document.getElementById('modalProveedor');
    const form = document.getElementById('formProveedor');

    const btnNuevo = document.getElementById('btnNuevoProveedor');
    const btnCerrar = document.getElementById('cerrarModalProveedor');
    const btnCancelar = document.getElementById('cancelarModalProveedor');

    const titulo = document.getElementById('tituloModalProveedor');
    const btnGuardar = document.getElementById('btnGuardarProveedor');

    // Abrir formulario para registrar
    btnNuevo.addEventListener('click', () => {

        form.reset();

        document.getElementById('id_proveedor').value = '';

        form.action = '/el_papelito/public/proveedores/crear';

        titulo.textContent = 'Nuevo proveedor';
        btnGuardar.textContent = 'Guardar proveedor';

        modal.classList.add('active');
    });

    // Cerrar modal
    function cerrarModal() {
        modal.classList.remove('active');
    }

    btnCerrar.addEventListener('click', cerrarModal);
    btnCancelar.addEventListener('click', cerrarModal);

    // Cerrar al hacer clic fuera del formulario
    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            cerrarModal();
        }
    });

});


document.addEventListener('DOMContentLoaded', () => {

    const modal = document.getElementById('modalProveedor');
    const form = document.getElementById('formProveedor');

    const btnNuevo = document.getElementById('btnNuevoProveedor');
    const btnCerrar = document.getElementById('cerrarModalProveedor');
    const btnCancelar = document.getElementById('cancelarModalProveedor');

    const titulo = document.getElementById('tituloModalProveedor');
    const btnGuardar = document.getElementById('btnGuardarProveedor');

    // Abrir formulario para registrar
    btnNuevo.addEventListener('click', () => {

        form.reset();

        document.getElementById('id_proveedor').value = '';

        form.action = '/el_papelito/public/proveedores/crear';

        titulo.textContent = 'Nuevo proveedor';
        btnGuardar.textContent = 'Guardar proveedor';

        modal.classList.add('active');
    });

    // Abrir formulario para editar
    document.querySelectorAll('.btn-editar-proveedor').forEach(boton => {

        boton.addEventListener('click', async () => {

            const id = boton.dataset.id;

            try {
                const respuesta = await fetch(
                    `/el_papelito/public/proveedores/editar?id=${id}`
                );

                if (!respuesta.ok) {
                    throw new Error('No se pudo consultar el proveedor.');
                }

                const proveedor = await respuesta.json();

                if (!proveedor || !proveedor.id_proveedor) {
                    throw new Error('Proveedor no encontrado.');
                }

                form.reset();

                document.getElementById('id_proveedor').value = proveedor.id_proveedor;
                document.getElementById('nombre_empresa').value = proveedor.nombre_empresa ?? '';
                document.getElementById('nombre_contacto').value = proveedor.nombre_contacto ?? '';
                document.getElementById('telefono').value = proveedor.telefono ?? '';
                document.getElementById('correo').value = proveedor.correo ?? '';
                document.getElementById('direccion').value = proveedor.direccion ?? '';

                form.action = '/el_papelito/public/proveedores/actualizar';

                titulo.textContent = 'Editar proveedor';
                btnGuardar.textContent = 'Guardar cambios';

                modal.classList.add('active');

            } catch (error) {
                alert(error.message);
            }

        });

    });

    // Cerrar modal
    function cerrarModal() {
        modal.classList.remove('active');
    }

    btnCerrar.addEventListener('click', cerrarModal);
    btnCancelar.addEventListener('click', cerrarModal);


    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            cerrarModal();
        }
    });

    // Desaparecer notificaciones automáticamente
    const mensajes = document.querySelectorAll('.mensaje');

    mensajes.forEach(mensaje => {

        setTimeout(() => {

            mensaje.style.transition = 'opacity 0.5s ease';
            mensaje.style.opacity = '0';

            setTimeout(() => {
                mensaje.remove();
            }, 500);

        }, 4000);

    });

});