
document.addEventListener('DOMContentLoaded', () => {

    // =========================================
    // ELEMENTOS PRINCIPALES
    // =========================================

    const modalVenta = document.getElementById('modalVenta');
    const modalDetalle = document.getElementById('modalDetalleVenta');

    const btnNuevaVenta = document.getElementById('btnNuevaVenta');
    const btnCerrarVenta = document.getElementById('cerrarModalVenta');
    const btnCancelarVenta = document.getElementById('cancelarVenta');

    const btnCerrarDetalle = document.getElementById('cerrarDetalleVenta');
    const btnAgregarProducto = document.getElementById('btnAgregarProducto');

    const formularioVenta = document.getElementById('formVenta');
    const contenedorProductos = document.getElementById('contenedorProductosVenta');

    const totalVenta = document.getElementById('totalVenta');
    const btnGuardarVenta = document.getElementById('btnGuardarVenta');

    // =========================================
    // UTILIDADES
    // =========================================

    const formatoDinero = (cantidad) => {
        return '$' + Number(cantidad || 0).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };


let temporizadorAvisoStock;

function mostrarAvisoStock(mensaje) {

    let aviso = document.getElementById('avisoStockVenta');

    if (!aviso) {
        aviso = document.createElement('div');
        aviso.id = 'avisoStockVenta';

        aviso.style.position = 'fixed';
        aviso.style.top = '20px';
        aviso.style.right = '20px';
        aviso.style.zIndex = '2000';
        aviso.style.padding = '14px 20px';
        aviso.style.borderRadius = '10px';
        aviso.style.background = '#ffe8e8';
        aviso.style.color = '#c62828';
        aviso.style.border = '1px solid #ffcaca';
        aviso.style.boxShadow = '0 4px 15px rgba(0,0,0,0.12)';
        aviso.style.fontSize = '13px';
        aviso.style.fontWeight = '600';

        document.body.appendChild(aviso);
    }

    aviso.textContent = '⚠ ' + mensaje;
    aviso.style.display = 'block';

    clearTimeout(temporizadorAvisoStock);

    temporizadorAvisoStock = setTimeout(() => {
        aviso.style.display = 'none';
    }, 3000);
}

    const escaparHTML = (valor) => {
        return String(valor ?? '').replace(/[&<>"']/g, caracter => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[caracter]);
    };

    const abrirModal = (modal) => {
        if (modal) {
            modal.classList.add('activo');
        }
    };

    const cerrarModal = (modal) => {
        if (modal) {
            modal.classList.remove('activo');
        }
    };

    // =========================================
    // ABRIR Y CERRAR MODAL DE VENTA
    // =========================================

    btnNuevaVenta?.addEventListener('click', () => {
        formularioVenta.reset();

        const filas = contenedorProductos.querySelectorAll('.fila-producto-venta');

        filas.forEach((fila, indice) => {
            if (indice > 0) {
                fila.remove();
            }
        });

        actualizarVenta();
        abrirModal(modalVenta);
    });

    btnCerrarVenta?.addEventListener('click', () => {
        cerrarModal(modalVenta);
    });

    btnCancelarVenta?.addEventListener('click', () => {
        cerrarModal(modalVenta);
    });

    btnCerrarDetalle?.addEventListener('click', () => {
        cerrarModal(modalDetalle);
    });

    // Cerrar al hacer clic fuera del contenido

    [modalVenta, modalDetalle].forEach(modal => {
        modal?.addEventListener('click', (evento) => {
            if (evento.target === modal) {
                cerrarModal(modal);
            }
        });
    });

    // Cerrar con Escape

    document.addEventListener('keydown', (evento) => {
        if (evento.key === 'Escape') {
            cerrarModal(modalVenta);
            cerrarModal(modalDetalle);
        }
    });

    // =========================================
    // AGREGAR PRODUCTOS
    // =========================================

    btnAgregarProducto?.addEventListener('click', () => {

        const primeraFila = contenedorProductos.querySelector('.fila-producto-venta');

        if (!primeraFila) {
            return;
        }

        const nuevaFila = primeraFila.cloneNode(true);

        const select = nuevaFila.querySelector('.select-producto-venta');
        const cantidad = nuevaFila.querySelector('.cantidad-producto-venta');

        select.value = '';
        cantidad.value = 1;

        nuevaFila.querySelector('.precio-producto-venta').textContent = formatoDinero(0);
        nuevaFila.querySelector('.subtotal-producto-venta').textContent = formatoDinero(0);

        contenedorProductos.appendChild(nuevaFila);

        actualizarVenta();
    });

    // =========================================
    // CALCULAR SUBTOTALES Y TOTAL
    // =========================================


    function actualizarVenta() {

        let total = 0;

        const filas = contenedorProductos.querySelectorAll('.fila-producto-venta');

        filas.forEach(fila => {

            const select = fila.querySelector('.select-producto-venta');
            const cantidadInput = fila.querySelector('.cantidad-producto-venta');

            const opcion = select.options[select.selectedIndex];

            const precio = Number(opcion?.dataset.precio || 0);
            const cantidad = Number(cantidadInput.value || 0);

            const subtotal = precio * cantidad;

            fila.querySelector('.precio-producto-venta').textContent =
                formatoDinero(precio);

            fila.querySelector('.subtotal-producto-venta').textContent =
                formatoDinero(subtotal);

            cantidadInput.max = Number(opcion?.dataset.stock || 0);

            if (!select.value) {

                fila.querySelector('.precio-producto-venta').textContent =
                    formatoDinero(0);

                fila.querySelector('.subtotal-producto-venta').textContent =
                    formatoDinero(0);

                return;
            }

            total += subtotal;
        });

        totalVenta.textContent = formatoDinero(total);
    }


    // Detectar cambios en productos y cantidades

    
contenedorProductos.addEventListener('change', (evento) => {

    if (evento.target.classList.contains('select-producto-venta')) {

        const fila = evento.target.closest('.fila-producto-venta');
        const cantidadInput = fila.querySelector('.cantidad-producto-venta');

        cantidadInput.value = 1;
        cantidadInput.dataset.ultimaCantidadValida = '1';

        actualizarVenta();
    }

    if (evento.target.classList.contains('cantidad-producto-venta')) {
        actualizarVenta();
    }

});


    
contenedorProductos.addEventListener('input', (evento) => {

    if (!evento.target.classList.contains('cantidad-producto-venta')) {
        return;
    }

    const cantidadInput = evento.target;
    const fila = cantidadInput.closest('.fila-producto-venta');

    const select = fila.querySelector('.select-producto-venta');
    const opcion = select.options[select.selectedIndex];

    if (!select.value) {
        return;
    }

    const stock = Number(opcion.dataset.stock || 0);
    const cantidad = Number(cantidadInput.value);

    const ultimaCantidadValida = cantidadInput.dataset.ultimaCantidadValida || '1';

    if (cantidad > stock) {

        cantidadInput.value = ultimaCantidadValida;

        mostrarAvisoStock(
            `No hay stock suficiente. Disponible: ${stock} unidades.`
        );

        actualizarVenta();
        return;
    }

    if (cantidad >= 1 && Number.isInteger(cantidad)) {
        cantidadInput.dataset.ultimaCantidadValida = cantidad;
    }

    actualizarVenta();

});


    // =========================================
    // ELIMINAR PRODUCTOS
    // =========================================

    contenedorProductos.addEventListener('click', (evento) => {

        const boton = evento.target.closest('.btn-eliminar-producto');

        if (!boton) {
            return;
        }

        const filas = contenedorProductos.querySelectorAll('.fila-producto-venta');

        if (filas.length === 1) {
            const fila = filas[0];

            fila.querySelector('.select-producto-venta').value = '';
            fila.querySelector('.cantidad-producto-venta').value = 1;

        } else {
            boton.closest('.fila-producto-venta').remove();
        }

        actualizarVenta();
    });

    // =========================================
    // VALIDAR FORMULARIO DE VENTA
    // =========================================


    formularioVenta?.addEventListener('submit', (evento) => {

        const filas = contenedorProductos.querySelectorAll('.fila-producto-venta');

        const productosSeleccionados = [];
        let valido = true;

        for (const fila of filas) {

            const select = fila.querySelector('.select-producto-venta');
            const idProducto = select.value;

            if (!idProducto) {
                valido = false;
                alert('Debes seleccionar un producto en cada fila.');
                break;
            }

            if (productosSeleccionados.includes(idProducto)) {
                valido = false;
                alert('No puedes agregar el mismo producto más de una vez. Modifica su cantidad en una sola fila.');
                break;
            }
 
            productosSeleccionados.push(idProducto);
        }

        if (!valido) {
            evento.preventDefault();
            return;
        }

        if (!confirm('¿Deseas registrar esta venta?')) {
            evento.preventDefault();
            return;
        }

        btnGuardarVenta.disabled = true;
        btnGuardarVenta.textContent = 'Registrando...';

    });


    // =========================================
    // CONFIRMAR ANULACIÓN
    // =========================================

    document.querySelectorAll('.form-anular-venta').forEach(formulario => {

        formulario.addEventListener('submit', (evento) => {

            const confirmar = confirm(
                '¿Estás seguro de anular esta venta? Se devolverán los productos al inventario.'
            );

            if (!confirmar) {
                evento.preventDefault();
            }

        });

    });

    // =========================================
    // CONSULTAR DETALLE DE VENTA
    // =========================================

    document.querySelectorAll('.btn-ver-venta').forEach(boton => {

        boton.addEventListener('click', async () => {

            const idVenta = boton.dataset.id;
            const contenido = document.getElementById('contenidoDetalleVenta');
            const numeroVenta = document.getElementById('detalleNumeroVenta');

            numeroVenta.textContent = `Venta #${idVenta}`;
            contenido.innerHTML = '<p class="cargando-detalle">Cargando información...</p>';

            abrirModal(modalDetalle);

            try {

                const respuesta = await fetch(
                    `/el_papelito/public/ventas/obtener-detalle?id=${encodeURIComponent(idVenta)}`,
                    {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }
                );

                if (!respuesta.ok) {
                    throw new Error('No fue posible consultar la venta.');
                }

                const datos = await respuesta.json();

                if (!datos.exito) {
                    throw new Error(datos.mensaje || 'No se encontró la venta.');
                }

                mostrarDetalleVenta(datos.venta, datos.detalles);

            } catch (error) {

                contenido.innerHTML = `
                    <p class="mensaje-error">
                        ${escaparHTML(error.message)}
                    </p>
                `;

            }

        });

    });

    // =========================================
    // MOSTRAR INFORMACIÓN DE LA VENTA
    // =========================================

    function mostrarDetalleVenta(venta, detalles) {

        const contenido = document.getElementById('contenidoDetalleVenta');

        const fecha = venta.fecha_venta
            ? new Date(venta.fecha_venta.replace(' ', 'T')).toLocaleString('es-SV')
            : 'No disponible';

        let html = `
            <div class="detalle-venta-informacion">

                <div class="detalle-venta-dato">
                    <span>Cliente</span>
                    <strong>${escaparHTML(venta.cliente || 'Consumidor final')}</strong>
                </div>

                <div class="detalle-venta-dato">
                    <span>Fecha</span>
                    <strong>${escaparHTML(fecha)}</strong>
                </div>

                <div class="detalle-venta-dato">
                    <span>Registrado por</span>
                    <strong>${escaparHTML(venta.usuario || '')}</strong>
                </div>

                <div class="detalle-venta-dato">
                    <span>Estado</span>
                    <strong>${escaparHTML(venta.estado || '')}</strong>
                </div>

                <div class="detalle-venta-dato">
                    <span>Teléfono del cliente</span>
                    <strong>${escaparHTML(venta.telefono || 'No registrado')}</strong>
                </div>

                <div class="detalle-venta-dato">
                    <span>Correo del cliente</span>
                    <strong>${escaparHTML(venta.correo || 'No registrado')}</strong>
                </div>

            </div>

            <h3>Productos vendidos</h3>

            <div class="tabla-contenedor">
                <table class="tabla-detalle-venta">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        if (detalles && detalles.length > 0) {

            detalles.forEach(detalle => {

                html += `
                    <tr>
                        <td>
                            ${escaparHTML(detalle.nombre)}
                            <br>
                            <small>${escaparHTML(detalle.codigo || '')}</small>
                        </td>

                        <td>${escaparHTML(detalle.cantidad)}</td>

                        <td>${formatoDinero(detalle.precio_unitario)}</td>

                        <td>${formatoDinero(detalle.subtotal)}</td>
                    </tr>
                `;

            });

        } else {

            html += `
                <tr>
                    <td colspan="4">No hay productos registrados en esta venta.</td>
                </tr>
            `;

        }

        html += `
                    </tbody>
                </table>
            </div>

            <div class="total-detalle-venta">
                Total de la venta:
                <strong>${formatoDinero(venta.total)}</strong>
            </div>
        `;

        if (venta.observaciones) {
            html += `
                <div class="detalle-venta-dato" style="margin-top: 15px;">
                    <span>Observaciones</span>
                    <strong>${escaparHTML(venta.observaciones)}</strong>
                </div>
            `;
        }

        contenido.innerHTML = html;
    }

    // =========================================
    // INICIALIZACIÓN
    // =========================================

    actualizarVenta();

});
