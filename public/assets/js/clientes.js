
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modalCliente');
    const form = document.getElementById('formCliente');

    const btnNuevo = document.getElementById('btnNuevoCliente');
    const btnCerrar = document.getElementById('cerrarModalCliente');
    const btnCancelar = document.getElementById('cancelarModalCliente');
    const btnGuardar = document.getElementById('btnGuardarCliente');

    const titulo = document.getElementById('tituloModalCliente');

    const idCliente = document.getElementById('id_cliente');
    const nombre = document.getElementById('nombre_completo');
    const telefono = document.getElementById('telefono');
    const correo = document.getElementById('correo');
    const direccion = document.getElementById('direccion');

    const URL_BASE = '/el_papelito/public';

    function abrirModal() {
        modal.classList.add('active');
    }

    function cerrarModal() {
        modal.classList.remove('active');
        form.reset();
        idCliente.value = '';
        form.action = `${URL_BASE}/clientes/crear`;
        titulo.textContent = 'Registrar cliente';
        btnGuardar.textContent = 'Guardar cliente';
    }

    // Registrar cliente
    btnNuevo.addEventListener('click', () => {
        cerrarModal();
        abrirModal();
        nombre.focus();
    });

    // Cerrar modal
    btnCerrar.addEventListener('click', cerrarModal);
    btnCancelar.addEventListener('click', cerrarModal);

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            cerrarModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('active')) {
            cerrarModal();
        }
    });

    // Editar cliente
    document.querySelectorAll('.btn-editar-cliente').forEach((boton) => {
        boton.addEventListener('click', async () => {
            const id = boton.dataset.id;

            try {
                boton.disabled = true;

                const respuesta = await fetch(
                    `${URL_BASE}/clientes/editar?id=${encodeURIComponent(id)}`
                );

                if (!respuesta.ok) {
                    throw new Error('No se pudo obtener la información del cliente.');
                }

                const cliente = await respuesta.json();

                if (!cliente || !cliente.id_cliente) {
                    throw new Error('No se encontró el cliente solicitado.');
                }

                idCliente.value = cliente.id_cliente;
                nombre.value = cliente.nombre_completo ?? '';
                telefono.value = cliente.telefono ?? '';
                correo.value = cliente.correo ?? '';
                direccion.value = cliente.direccion ?? '';

                form.action = `${URL_BASE}/clientes/actualizar`;
                titulo.textContent = 'Editar cliente';
                btnGuardar.textContent = 'Guardar cambios';

                abrirModal();
                nombre.focus();

            } catch (error) {
                alert(error.message || 'Ocurrió un error al cargar el cliente.');
            } finally {
                boton.disabled = false;
            }
        });
    });
});
