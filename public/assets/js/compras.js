
document.addEventListener('DOMContentLoaded', function () {

    // =====================================
    // ELEMENTOS DEL MODAL
    // =====================================

    const modal = document.getElementById('modalCompra');
    const btnNuevo = document.getElementById('btnNuevaCompra');
    const btnCerrar = document.getElementById('cerrarModalCompra');
    const btnCancelar = document.getElementById('cancelarModalCompra');

    // Abrir modal
    btnNuevo.addEventListener('click', function () {
        modal.classList.add('active');
    });

    // Cerrar modal
    btnCerrar.addEventListener('click', cerrarModal);
    btnCancelar.addEventListener('click', cerrarModal);

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            cerrarModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            cerrarModal();
        }
    });

    function cerrarModal() {
        modal.classList.remove('active');
    }


    // =====================================
    // ELEMENTOS DE PRODUCTOS
    // =====================================

    const btnAgregar = document.getElementById('btnAgregarProducto');
    const listaProductos = document.getElementById('listaProductosCompra');
    const plantilla = document.getElementById('plantillaProductoCompra');
    const totalCompra = document.getElementById('totalCompra');


    // Formato de moneda
    const formatoMoneda = new Intl.NumberFormat('es-SV', {
        style: 'currency',
        currency: 'USD'
    });


    // =====================================
    // AGREGAR PRODUCTO
    // =====================================

    btnAgregar.addEventListener('click', function () {

        const filaVacia = document.getElementById('filaSinProductos');

        if (filaVacia) {
            filaVacia.remove();
        }

        const nuevaFila = plantilla.content.cloneNode(true);

        listaProductos.appendChild(nuevaFila);

    });


    // =====================================
    // DETECTAR CAMBIOS EN LA TABLA
    // =====================================

    listaProductos.addEventListener('change', function (event) {

        const fila = event.target.closest('.fila-producto-compra');

        if (!fila) return;

        // Cuando se selecciona un producto
        if (event.target.classList.contains('select-producto')) {

            const opcion = event.target.selectedOptions[0];
            const costo = opcion.dataset.costo;

            if (costo !== undefined) {
                fila.querySelector('.costo-producto').value = costo;
            }

        }

        calcularTotales();

    });


    listaProductos.addEventListener('input', function (event) {

        if (
            event.target.classList.contains('cantidad-producto') ||
            event.target.classList.contains('costo-producto')
        ) {
            calcularTotales();
        }

    });


    // =====================================
    // ELIMINAR PRODUCTO
    // =====================================

    listaProductos.addEventListener('click', function (event) {

        if (event.target.classList.contains('btn-eliminar-producto')) {

            const fila = event.target.closest('.fila-producto-compra');

            fila.remove();

            calcularTotales();

            if (listaProductos.querySelectorAll('.fila-producto-compra').length === 0) {

                listaProductos.innerHTML = `
                    <tr id="filaSinProductos">
                        <td colspan="5" class="sin-resultados">
                            Agrega los productos de esta compra.
                        </td>
                    </tr>
                `;

            }

        }

    });


    // =====================================
    // CALCULAR SUBTOTALES Y TOTAL
    // =====================================

    function calcularTotales() {

        let total = 0;

        const filas = listaProductos.querySelectorAll('.fila-producto-compra');

        filas.forEach(function (fila) {

            const cantidad = parseInt(
                fila.querySelector('.cantidad-producto').value
            ) || 0;

            const costo = parseFloat(
                fila.querySelector('.costo-producto').value
            ) || 0;

            const subtotal = cantidad * costo;

            fila.querySelector('.subtotal-producto').textContent =
                formatoMoneda.format(subtotal);

            total += subtotal;

        });

        totalCompra.textContent = formatoMoneda.format(total);

    }

    //Evitar doble envio al formulario de compra
    const formCompra = document.getElementById('formCompra');
    const btnGuardar = document.getElementById('btnGuardarCompra');

    formCompra.addEventListener('submit', function () {

        // Evitar nuevos envíos
        btnGuardar.disabled = true;

        // Cambiar el texto del botón
        btnGuardar.textContent = 'Registrando compra...';

        // Cambiar apariencia
        btnGuardar.style.opacity = '0.7';
        btnGuardar.style.cursor = 'not-allowed';

    });


    // =====================================
    // CONSULTAR DETALLES DE UNA COMPRA
    // =====================================

    const modalDetalle = document.getElementById('modalDetalleCompra');
    const btnCerrarDetalle = document.getElementById('cerrarDetalleCompra');
    const contenidoDetalle = document.getElementById('contenidoDetalleCompra');
    const numeroDetalle = document.getElementById('detalleNumeroCompra');

    // Escapar valores para evitar insertar HTML no confiable
    function escaparHTML(valor) {
        return String(valor ?? '').replace(/[&<>"']/g, function (caracter) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[caracter];
        });
    }

    // Detectar clic en cualquier botón Ver
    document.addEventListener('click', async function (event) {

        const boton = event.target.closest('.btn-ver-compra');

        if (!boton) return;

        const idCompra = boton.dataset.id;

        modalDetalle.classList.add('active');
        numeroDetalle.textContent = 'Compra #' + idCompra;
        contenidoDetalle.innerHTML = '<p>Cargando información...</p>';

        try {
            const respuesta = await fetch(
                '/el_papelito/public/compras/obtener-detalle?id=' +
                encodeURIComponent(idCompra)
            );

            if (!respuesta.ok) {
                throw new Error('No fue posible consultar la compra.');
            }

            const datos = await respuesta.json();

            if (!datos.exito) {
                throw new Error(datos.mensaje || 'No se encontraron los datos.');
            }

            const compra = datos.compra;
            const detalles = datos.detalles;

            numeroDetalle.textContent = 'Compra #' + compra.id_compra;

            const filas = detalles.map(function (producto) {
                return `
                    <tr>
                        <td>${escaparHTML(producto.codigo)}</td>
                        <td>${escaparHTML(producto.nombre)}</td>
                        <td>${escaparHTML(producto.cantidad)}</td>
                        <td>${formatoMoneda.format(Number(producto.costo_unitario))}</td>
                        <td>${formatoMoneda.format(Number(producto.subtotal))}</td>
                    </tr>
                `;
            }).join('');

            const fecha = new Date(
                compra.fecha_compra.replace(' ', 'T')
            );

            contenidoDetalle.innerHTML = `
                <div class="detalle-compra-resumen">
                    <div>
                        <span>Proveedor</span>
                        <strong>${escaparHTML(compra.nombre_empresa)}</strong>
                    </div>

                    <div>
                        <span>Fecha</span>
                        <strong>${escaparHTML(fecha.toLocaleString('es-SV'))}</strong>
                    </div>

                    <div>
                        <span>Registrada por</span>
                        <strong>${escaparHTML(compra.nombre_completo)}</strong>
                    </div>

                    <div>
                        <span>Documento</span>
                        <strong>${escaparHTML(compra.numero_documento || 'No especificado')}</strong>
                    </div>

                    <div>
                        <span>Estado</span>
                        <strong>${escaparHTML(compra.estado)}</strong>
                    </div>
                </div>

                <h3>Productos de la compra</h3>

                <div class="tabla-contenedor">
                    <table>
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Costo unitario</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>

                        <tbody>
                            ${filas || `
                                <tr>
                                    <td colspan="5">No hay productos registrados.</td>
                                </tr>
                            `}
                        </tbody>
                    </table>
                </div>

                <div class="detalle-compra-total">
                    <span>Total de la compra</span>
                    <strong>${formatoMoneda.format(Number(compra.total))}</strong>
                </div>

                <div class="detalle-compra-observaciones">
                    <h3>Observaciones</h3>
                    <p>${escaparHTML(compra.observaciones || 'Sin observaciones.')}</p>
                </div>
            `;

        } catch (error) {
            contenidoDetalle.innerHTML = `
                <p class="mensaje-error">
                    ${escaparHTML(error.message)}
                </p>
            `;
        }
    });

    // Cerrar modal
    btnCerrarDetalle.addEventListener('click', function () {
        modalDetalle.classList.remove('active');
    });

    modalDetalle.addEventListener('click', function (event) {
        if (event.target === modalDetalle) {
            modalDetalle.classList.remove('active');
        }
    });

    document.addEventListener('click', function (event) {

        const boton = event.target.closest('.btn-anular-compra');

        if (!boton) return;

        const idCompra = boton.dataset.id;
        const csrfToken = boton.dataset.csrf;

        const confirmar = confirm(
            `¿Estás seguro de que deseas anular la compra #${idCompra}?\n\n` +
            'Esta acción descontará del inventario las cantidades ingresadas por esta compra.'
        );

        if (!confirmar) return;

        // Crear formulario para enviar la solicitud POST
        const formulario = document.createElement('form');

        formulario.method = 'POST';
        formulario.action = '/el_papelito/public/compras/anular';

        const campoId = document.createElement('input');
        campoId.type = 'hidden';
        campoId.name = 'id_compra';
        campoId.value = idCompra;

        const campoToken = document.createElement('input');
        campoToken.type = 'hidden';
        campoToken.name = 'csrf_token';
        campoToken.value = csrfToken;

        formulario.appendChild(campoId);
        formulario.appendChild(campoToken);

        document.body.appendChild(formulario);

        formulario.submit();
    });



});
