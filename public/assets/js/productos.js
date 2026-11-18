
// Elementos del modal
const modalProducto = document.getElementById('modalProducto');

const btnNuevoProducto = document.getElementById('btnNuevoProducto');
const btnCerrarModal = document.getElementById('btnCerrarModal');
const btnCancelarModal = document.getElementById('btnCancelarModal');

// Abrir modal
btnNuevoProducto.addEventListener('click', function () {

    // Limpiar el formulario
    formProducto.reset();

    // Limpiar el identificador
    document.getElementById('id_producto').value = '';

    // Configurar para registrar
    formProducto.action = '/el_papelito/public/productos/crear';

    // Abrir modal
    modalProducto.classList.add('active');

});

// Cerrar modal con la X
btnCerrarModal.addEventListener('click', function () {
    modalProducto.classList.remove('active');
});

// Cerrar modal con Cancelar
btnCancelarModal.addEventListener('click', function () {
    modalProducto.classList.remove('active');
});

// Cerrar al hacer clic fuera de la ventana
modalProducto.addEventListener('click', function (evento) {
    if (evento.target === modalProducto) {
        modalProducto.classList.remove('active');
    }
});

// Cerrar al presionar Escape
document.addEventListener('keydown', function (evento) {
    if (evento.key === 'Escape') {
        modalProducto.classList.remove('active');
    }
});


// Elementos del modal de categorías
const modalCategorias = document.getElementById('modalCategorias');

const btnGestionarCategorias = document.getElementById('btnGestionarCategorias');
const btnCerrarCategorias = document.getElementById('btnCerrarCategorias');

// Abrir modal de categorías
btnGestionarCategorias.addEventListener('click', function () {
    modalCategorias.classList.add('active');
});

// Cerrar modal de categorías
btnCerrarCategorias.addEventListener('click', function () {
    modalCategorias.classList.remove('active');
});

// Cerrar al hacer clic fuera de la ventana
modalCategorias.addEventListener('click', function (evento) {
    if (evento.target === modalCategorias) {
        modalCategorias.classList.remove('active');
    }
});


// Contenedor donde aparecerán las categorías
const listaCategorias = document.getElementById('listaCategorias');

// Consultar categorías desde PHP

async function cargarCategorias() {

    try {

        const respuesta = await fetch('/el_papelito/public/categorias/listar');
        const categorias = await respuesta.json();

        listaCategorias.innerHTML = '';

        if (categorias.length === 0) {
            listaCategorias.textContent = 'No hay categorías registradas.';
            return;
        }

        categorias.forEach(categoria => {

            const elemento = document.createElement('div');
            elemento.classList.add('categoria-item');

            const informacion = document.createElement('div');

            const nombre = document.createElement('strong');
            nombre.textContent = categoria.nombre;

            const descripcion = document.createElement('p');
            descripcion.textContent = categoria.descripcion || '';

            informacion.appendChild(nombre);
            informacion.appendChild(descripcion);

            const botonEliminar = document.createElement('button');
            botonEliminar.type = 'button';
            botonEliminar.textContent = 'Eliminar';
            botonEliminar.classList.add('btn-eliminar-categoria');

            botonEliminar.addEventListener('click', async function () {

                const confirmar = confirm(
                    `¿Deseas eliminar la categoría "${categoria.nombre}"?`
                );

                if (!confirmar) return;

                const datos = new FormData();
                datos.append('id_categoria', categoria.id_categoria);

                try {

                    const respuesta = await fetch(
                        '/el_papelito/public/categorias/eliminar',
                        {
                            method: 'POST',
                            body: datos
                        }
                    );

                    const resultado = await respuesta.json();

                    alert(resultado.mensaje);

                    if (resultado.exito) {

                        await cargarCategorias();

                        const selector = document.getElementById('categoria');

                        const respuestaCategorias = await fetch(
                            '/el_papelito/public/categorias/listar'
                        );

                        const categoriasActualizadas = await respuestaCategorias.json();

                        selector.innerHTML = '<option value="">Selecciona una categoría</option>';

                        categoriasActualizadas.forEach(categoria => {

                            if (Number(categoria.activa) === 1) {

                                const opcion = document.createElement('option');

                                opcion.value = categoria.id_categoria;
                                opcion.textContent = categoria.nombre;

                                selector.appendChild(opcion);

                            }

                        });

                    }

                } catch (error) {
                    console.error(error);
                    alert('No se pudo eliminar la categoría.');
                }

            });

            elemento.appendChild(informacion);
            elemento.appendChild(botonEliminar);

            listaCategorias.appendChild(elemento);

        });

    } catch (error) {

        console.error('Error al cargar categorías:', error);

        listaCategorias.textContent =
            'No se pudieron cargar las categorías.';
    }
}


// Cargar categorías al abrir el modal
btnGestionarCategorias.addEventListener('click', cargarCategorias);


// Formulario para registrar categorías
const formCategoria = document.getElementById('formCategoria');

// Registrar categoría sin recargar la página
formCategoria.addEventListener('submit', async function (evento) {

    evento.preventDefault();

    const datos = new FormData(formCategoria);

    try {

        const respuesta = await fetch('/el_papelito/public/categorias/crear', {
            method: 'POST',
            body: datos
        });

        const resultado = await respuesta.json();

        alert(resultado.mensaje);

        if (resultado.exito) {

            formCategoria.reset();

            await cargarCategorias();

            const respuestaCategorias = await fetch(
                '/el_papelito/public/categorias/listar'
            );

            const categorias = await respuestaCategorias.json();

            const selector = document.getElementById('categoria');

            selector.innerHTML = '<option value="">Selecciona una categoría</option>';

            categorias.forEach(categoria => {

                if (Number(categoria.activa) === 1) {

                    const opcion = document.createElement('option');

                    opcion.value = categoria.id_categoria;
                    opcion.textContent = categoria.nombre;

                    selector.appendChild(opcion);

                }

            });

        }

    } catch (error) {

        console.error('Error:', error);

        alert('Ocurrió un error al registrar la categoría.');
    }

});


// Formulario y modal de productos
const formProducto = document.getElementById('formProducto');

// Botones para editar productos
const botonesEditar = document.querySelectorAll('.btn-editar-producto');

botonesEditar.forEach(boton => {

    boton.addEventListener('click', async function () {

        const idProducto = this.dataset.id;

        try {

            const respuesta = await fetch(
                `/el_papelito/public/productos/editar?id=${idProducto}`
            );

            if (!respuesta.ok) {
                throw new Error('No se pudo obtener el producto.');
            }

            const producto = await respuesta.json();

            // Guardar el identificador del producto
            document.getElementById('id_producto').value = producto.id_producto;

            // Configurar el formulario para actualizar
            formProducto.action = '/el_papelito/public/productos/actualizar';

            // Cargar información en el formulario
            formProducto.elements['codigo'].value = producto.codigo;
            formProducto.elements['nombre'].value = producto.nombre;
            formProducto.elements['id_categoria'].value = producto.id_categoria ?? '';
            formProducto.elements['precio_compra'].value = producto.precio_compra;
            formProducto.elements['precio_venta'].value = producto.precio_venta;
            formProducto.elements['existencia'].value = producto.existencia;
            formProducto.elements['stock_minimo'].value = producto.stock_minimo;
            formProducto.elements['descripcion'].value = producto.descripcion ?? '';

            // Abrir el modal
            modalProducto.classList.add('active');

        } catch (error) {

            console.error(error);
            alert('No se pudieron cargar los datos del producto.');

        }

    });

});


// Confirmar activación o desactivación de productos
const formulariosEstado = document.querySelectorAll(
    'form[action="/el_papelito/public/productos/estado"]'
);

formulariosEstado.forEach(formulario => {

    formulario.addEventListener('submit', function (evento) {

        const estado = this.querySelector('[name="estado"]').value;

        const mensaje = estado === '0'
            ? '¿Estás seguro de que deseas desactivar este producto?'
            : '¿Deseas volver a activar este producto?';

        const confirmar = confirm(mensaje);

        if (!confirmar) {
            evento.preventDefault();
        }

    });

});



// Modal de ajustes de inventario
const modalInventario = document.getElementById('modalInventario');

const botonesAjustarInventario = document.querySelectorAll(
    '.btn-ajustar-inventario'
);

const inventarioIdProducto = document.getElementById('inventarioIdProducto');
const inventarioNombreProducto = document.getElementById('inventarioNombreProducto');

const btnCerrarInventario = document.getElementById('btnCerrarInventario');
const btnCancelarInventario = document.getElementById('btnCancelarInventario');
const formInventario = document.getElementById('formInventario');

// Abrir modal
botonesAjustarInventario.forEach(boton => {
    boton.addEventListener('click', () => {

        formInventario.reset();

        inventarioIdProducto.value = boton.dataset.id;
        inventarioNombreProducto.value = boton.dataset.nombre;

        modalInventario.classList.add('active');
    });
});

// Cerrar modal
function cerrarModalInventario() {
    modalInventario.classList.remove('active');
}

btnCerrarInventario.addEventListener('click', cerrarModalInventario);
btnCancelarInventario.addEventListener('click', cerrarModalInventario);

// Cerrar al hacer clic fuera del formulario
modalInventario.addEventListener('click', (evento) => {
    if (evento.target === modalInventario) {
        cerrarModalInventario();
    }
});
