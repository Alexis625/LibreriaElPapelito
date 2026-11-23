
document.addEventListener('DOMContentLoaded', () => {

    // =====================================
    // ELEMENTOS PRINCIPALES
    // =====================================

    const modalCrear = document.getElementById('modalCrear');
    const modalDetalle = document.getElementById('modalDetalle');
    const modalEstado = document.getElementById('modalEstado');

    const btnAbrirCrear = document.getElementById('btnAbrirCrear');
    const btnAgregarProducto = document.getElementById('btnAgregarProducto');

    const contenedorProductos = document.getElementById('contenedorProductosReserva');
    const contenidoDetalle = document.getElementById('contenidoDetalleReserva');

    const formCrear = document.getElementById('formCrearReservacion');

    const config = window.reservacionesConfig || {};

    // =====================================
    // FUNCIONES GENERALES
    // =====================================

    function escaparHTML(valor) {
        return String(valor ?? '').replace(/[&<>"']/g, caracter => {
            const entidades = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };

            return entidades[caracter];
        });
    }

    function formatoFecha(fecha) {
        if (!fecha) {
            return '—';
        }

        const partes = String(fecha).split('-');

        if (partes.length !== 3) {
            return escaparHTML(fecha);
        }

        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    }

    function formatoFechaHora(fecha) {
        if (!fecha) {
            return '—';
        }

        const valor = new Date(String(fecha).replace(' ', 'T'));

        if (Number.isNaN(valor.getTime())) {
            return escaparHTML(fecha);
        }

        return valor.toLocaleString('es-SV', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    }

    function formatoDinero(valor) {
        return new Intl.NumberFormat('es-SV', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 2
        }).format(Number(valor) || 0);
    }

// =====================================
// NOTIFICACIONES PEQUEÑAS
// =====================================

let temporizadorNotificacion;

function mostrarNotificacion(mensaje) {
    let notificacion = document.getElementById('notificacionReservacion');

    if (!notificacion) {
        notificacion = document.createElement('div');
        notificacion.id = 'notificacionReservacion';

        Object.assign(notificacion.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            zIndex: '99999',
            width: 'min(340px, calc(100% - 40px))',
            padding: '15px 18px',
            background: '#fff',
            borderLeft: '4px solid #DC5555',
            borderRadius: '9px',
            boxShadow: '0 5px 20px rgba(0,0,0,0.15)',
            color: '#333',
            fontSize: '13px',
            opacity: '0',
            transform: 'translateX(20px)',
            transition: 'all 0.25s ease'
        });

        document.body.appendChild(notificacion);
    }

    clearTimeout(temporizadorNotificacion);

    notificacion.innerHTML = `
        <div style="display:flex; align-items:center; gap:9px; margin-bottom:5px;">
            <span style="color:#DC5555; font-size:17px;">&#9888;</span>
            <strong style="font-size:14px;">Stock insuficiente</strong>
        </div>
        <div style="color:#666; line-height:1.5;"></div>
    `;

    notificacion.querySelector('div:last-child').textContent = mensaje;

    requestAnimationFrame(() => {
        notificacion.style.opacity = '1';
        notificacion.style.transform = 'translateX(0)';
    });

    temporizadorNotificacion = setTimeout(() => {
        notificacion.style.opacity = '0';
        notificacion.style.transform = 'translateX(20px)';

        setTimeout(() => {
            notificacion.remove();
        }, 250);
    }, 3500);
}

    function abrirModal(modal) {
        if (modal) {
            modal.classList.add('activo');
            document.body.style.overflow = 'hidden';
        }
    }

    function cerrarModal(modal) {
        if (modal) {
            modal.classList.remove('activo');
        }

        if (!document.querySelector('.modal-reservacion.activo')) {
            document.body.style.overflow = '';
        }
    }

    // =====================================
    // ABRIR Y CERRAR MODALES
    // =====================================

    btnAbrirCrear?.addEventListener('click', () => {
        abrirModal(modalCrear);
    });

    document.querySelectorAll('[data-cerrar]').forEach(boton => {
        boton.addEventListener('click', () => {
            const idModal = boton.dataset.cerrar;
            cerrarModal(document.getElementById(idModal));
        });
    });

    document.querySelectorAll('.modal-reservacion').forEach(modal => {
        modal.addEventListener('click', evento => {
            if (evento.target === modal) {
                cerrarModal(modal);
            }
        });
    });

    document.addEventListener('keydown', evento => {
        if (evento.key === 'Escape') {
            document.querySelectorAll('.modal-reservacion.activo').forEach(modal => {
                cerrarModal(modal);
            });
        }
    });

    // =====================================
    // PRODUCTOS DINÁMICOS
    // =====================================

    function actualizarBotonesEliminar() {
        const filas = contenedorProductos.querySelectorAll('.producto-reserva-fila');

        filas.forEach(fila => {
            const boton = fila.querySelector('.btn-eliminar-producto');

            if (boton) {
                boton.disabled = filas.length === 1;
            }
        });
    }

    function actualizarProductosSeleccionados() {
        const filas = contenedorProductos.querySelectorAll('.producto-reserva-fila');

        const seleccionados = Array.from(filas)
            .map(fila => fila.querySelector('.select-producto-reserva')?.value)
            .filter(Boolean);

        filas.forEach(fila => {
            const select = fila.querySelector('.select-producto-reserva');
            const valorActual = select.value;

            Array.from(select.options).forEach(opcion => {
                if (!opcion.value) {
                    return;
                }

                opcion.disabled =
                    seleccionados.includes(opcion.value) &&
                    opcion.value !== valorActual;
            });
        });
    }

    // =====================================
    // VALIDAR EXISTENCIAS DISPONIBLES
    // =====================================

    function obtenerExistencia(opcion) {
        if (!opcion || !opcion.value) {
            return null;
        }

        // Preferir data-existencia si la vista lo proporciona.
        if (opcion.dataset.existencia !== undefined) {
            const existencia = Number(opcion.dataset.existencia);

            return Number.isInteger(existencia) && existencia >= 0
                ? existencia
                : null;
        }

        // Compatibilidad con las opciones actuales.
        // Ejemplo: "Cuaderno (5 disponibles)".
        const coincidencia = opcion.textContent.match(/(\d+)\s+disponibles?/i);

        return coincidencia ? Number(coincidencia[1]) : null;
    }

    function validarExistenciaFila(fila, mostrarAlerta = true) {
        const select = fila.querySelector('.select-producto-reserva');
        const inputCantidad = fila.querySelector('input[name="cantidades[]"]');

        if (!select || !inputCantidad || !select.value || inputCantidad.value === '') {
            if (inputCantidad) {
                inputCantidad.removeAttribute('max');
                inputCantidad.setCustomValidity('');
            }

            return true;
        }

        const opcion = select.options[select.selectedIndex];
        const existencia = obtenerExistencia(opcion);

        if (existencia === null) {
            return true;
        }

        inputCantidad.max = existencia;

        const cantidad = Number(inputCantidad.value);

        if (Number.isInteger(cantidad) && cantidad > existencia) {
            inputCantidad.value = '';

            inputCantidad.setCustomValidity(
                `La cantidad supera las existencias disponibles (${existencia}).`
            );

            if (mostrarAlerta) {
    mostrarNotificacion(
        `Solo hay ${existencia} unidades disponibles de este producto.`
    );
}

            inputCantidad.focus();

            return false;
        }

        inputCantidad.setCustomValidity('');

        return true;
    }

    function validarTodasLasExistencias(mostrarAlerta = true) {
        const filas = contenedorProductos.querySelectorAll('.producto-reserva-fila');

        let valido = true;

        filas.forEach(fila => {
            if (!validarExistenciaFila(fila, mostrarAlerta)) {
                valido = false;
            }
        });

        return valido;
    }

    function crearFilaProducto() {
        const filaOriginal = contenedorProductos.querySelector('.producto-reserva-fila');

        if (!filaOriginal) {
            return;
        }

        const nuevaFila = filaOriginal.cloneNode(true);

        const select = nuevaFila.querySelector('.select-producto-reserva');
        const cantidad = nuevaFila.querySelector('input[name="cantidades[]"]');

        select.value = '';
        cantidad.value = '';
        cantidad.removeAttribute('max');
        cantidad.setCustomValidity('');

        Array.from(select.options).forEach(opcion => {
            opcion.disabled = false;
        });

        contenedorProductos.appendChild(nuevaFila);

        actualizarBotonesEliminar();
        actualizarProductosSeleccionados();
    }

    btnAgregarProducto?.addEventListener('click', () => {
        crearFilaProducto();
    });

    contenedorProductos?.addEventListener('click', evento => {
        const boton = evento.target.closest('.btn-eliminar-producto');

        if (!boton) {
            return;
        }

        const filas = contenedorProductos.querySelectorAll('.producto-reserva-fila');

        if (filas.length <= 1) {
            return;
        }

        boton.closest('.producto-reserva-fila').remove();

        actualizarBotonesEliminar();
        actualizarProductosSeleccionados();
    });

    contenedorProductos?.addEventListener('change', evento => {
        if (evento.target.classList.contains('select-producto-reserva')) {
            const fila = evento.target.closest('.producto-reserva-fila');

            actualizarProductosSeleccionados();

            if (fila) {
                validarExistenciaFila(fila);
            }
        }

        if (evento.target.matches('input[name="cantidades[]"]')) {
            const fila = evento.target.closest('.producto-reserva-fila');

            if (fila) {
                validarExistenciaFila(fila);
            }
        }
    });

    contenedorProductos?.addEventListener('input', evento => {
    if (evento.target.matches('input[name="cantidades[]"]')) {
        const fila = evento.target.closest('.producto-reserva-fila');

        if (fila) {
            validarExistenciaFila(fila, true);
        }
    }
});

    // =====================================
    // VALIDAR FORMULARIO
    // =====================================

    formCrear?.addEventListener('submit', evento => {
        const filas = contenedorProductos.querySelectorAll('.producto-reserva-fila');

        if (filas.length === 0) {
            evento.preventDefault();
            alert('Debes agregar al menos un producto.');
            return;
        }

        const productosSeleccionados = [];

        let valido = true;

        filas.forEach(fila => {
            const producto = fila.querySelector('select[name="productos[]"]').value;

            const cantidad = Number(
                fila.querySelector('input[name="cantidades[]"]').value
            );

            if (!producto || !Number.isInteger(cantidad) || cantidad <= 0) {
                valido = false;
            }

            productosSeleccionados.push(producto);
        });

        if (!valido) {
            evento.preventDefault();

            alert(
                'Selecciona un producto e ingresa una cantidad válida en cada fila.'
            );

            return;
        }

        if (!validarTodasLasExistencias()) {
            evento.preventDefault();
            return;
        }

        if (new Set(productosSeleccionados).size !== productosSeleccionados.length) {
            evento.preventDefault();

            alert('No puedes agregar el mismo producto más de una vez.');

            return;
        }

        const botonGuardar = formCrear.querySelector('.btn-guardar-reserva');

        if (botonGuardar) {
            botonGuardar.disabled = true;
            botonGuardar.textContent = 'Registrando...';
        }
    });

    // =====================================
    // CONSULTAR DETALLES POR AJAX
    // =====================================

    async function consultarDetalle(idReserva) {
        contenidoDetalle.innerHTML = `
            <div class="detalle-cargando">
                Cargando información de la reservación...
            </div>
        `;

        abrirModal(modalDetalle);

        try {
            const url = new URL(config.urlDetalle, window.location.origin);

            url.searchParams.set('id', idReserva);

            const respuesta = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            const datos = await respuesta.json();

            if (!respuesta.ok || !datos.exito) {
                throw new Error(
                    datos.mensaje || 'No fue posible consultar la reservación.'
                );
            }

            mostrarDetalle(datos.reservacion, datos.detalles);

        } catch (error) {
            contenidoDetalle.innerHTML = `
                <div class="detalle-error">
                    ${escaparHTML(error.message)}
                </div>
            `;
        }
    }

    function mostrarDetalle(reserva, detalles) {
        if (!reserva) {
            contenidoDetalle.innerHTML = `
                <div class="detalle-error">
                    No se encontró la información de la reservación.
                </div>
            `;

            return;
        }

        const estados = {
            PENDIENTE: 'Pendiente',
            CONFIRMADA: 'Confirmada',
            ENTREGADA: 'Entregada',
            CANCELADA: 'Cancelada'
        };

        const estado = reserva.estado || '';
        const claseEstado = estado.toLowerCase();

        let total = 0;
        let filasProductos = '';

        if (Array.isArray(detalles) && detalles.length > 0) {
            filasProductos = detalles.map(producto => {
                const subtotal = Number(producto.subtotal) || 0;

                total += subtotal;

                return `
                    <tr>
                        <td>
                            <strong>${escaparHTML(producto.nombre)}</strong>
                            <br>
                            <small>${escaparHTML(producto.codigo)}</small>
                        </td>

                        <td>${escaparHTML(producto.cantidad)}</td>

                        <td>${formatoDinero(producto.precio_venta)}</td>

                        <td>${formatoDinero(subtotal)}</td>
                    </tr>
                `;
            }).join('');

        } else {
            filasProductos = `
                <tr>
                    <td colspan="4" class="detalle-vacio">
                        No hay productos registrados.
                    </td>
                </tr>
            `;
        }

        // Compatibilidad con el formato nuevo y los registros antiguos.
        const registradoPor = reserva.registrado_por || 'Cliente sin usuario';

        const observaciones =
            reserva.observaciones_texto ??
            reserva.observaciones ??
            '';

        contenidoDetalle.innerHTML = `
            <div class="detalle-reserva-datos">

                <div class="detalle-reserva-item">
                    <span>Número de reservación</span>
                    <strong>#${escaparHTML(reserva.id_reserva)}</strong>
                </div>

                <div class="detalle-reserva-item">
                    <span>Estado</span>
                    <strong>
                        <span class="estado-reserva ${escaparHTML(claseEstado)}">
                            ${escaparHTML(estados[estado] || estado)}
                        </span>
                    </strong>
                </div>

                <div class="detalle-reserva-item">
                    <span>Cliente</span>
                    <strong>${escaparHTML(reserva.nombre_completo)}</strong>
                </div>

                <div class="detalle-reserva-item">
                    <span>Teléfono</span>
                    <strong>${escaparHTML(reserva.telefono || '—')}</strong>
                </div>

                <div class="detalle-reserva-item">
                    <span>Correo</span>
                    <strong>${escaparHTML(reserva.correo || '—')}</strong>
                </div>

                <div class="detalle-reserva-item">
                    <span>Fecha de registro</span>
                    <strong>${formatoFechaHora(reserva.fecha_reserva)}</strong>
                </div>

                <div class="detalle-reserva-item">
                    <span>Fecha límite de retiro</span>
                    <strong>${formatoFecha(reserva.fecha_limite_retiro)}</strong>
                </div>

                <div class="detalle-reserva-item">
                    <span>Registrado por</span>
                    <strong>${escaparHTML(registradoPor)}</strong>
                </div>

                <div class="detalle-reserva-item">
                    <span>Dirección</span>
                    <strong>${escaparHTML(reserva.direccion || '—')}</strong>
                </div>

            </div>

            <div class="detalle-reserva-observaciones">
                <strong>Observaciones</strong>
                ${escaparHTML(observaciones || 'Sin observaciones.')}
            </div>

            <h3 style="font-size:14px; margin-bottom:12px;">
                Productos reservados
            </h3>

            <div class="tabla-scroll">
                <table class="detalle-tabla-productos">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>
                        ${filasProductos}
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="3" class="detalle-total">
                                Total referencial
                            </td>

                            <td class="detalle-total">
                                ${formatoDinero(total)}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `;
    }

    document.querySelectorAll('.ver-detalle').forEach(boton => {
        boton.addEventListener('click', () => {
            consultarDetalle(boton.dataset.id);
        });
    });

    // =====================================
    // CAMBIAR ESTADO
    // =====================================

    const inputIdReserva = document.getElementById('estado_id_reserva');
    const selectNuevoEstado = document.getElementById('nuevo_estado');

    document.querySelectorAll('.cambiar-estado').forEach(boton => {
        boton.addEventListener('click', () => {
            const idReserva = boton.dataset.id;
            const estadoActual = boton.dataset.estado;

            inputIdReserva.value = idReserva;
            selectNuevoEstado.value = estadoActual;

            abrirModal(modalEstado);
        });
    });

    // =====================================
    // INICIALIZAR
    // =====================================

    actualizarBotonesEliminar();
    actualizarProductosSeleccionados();

    contenedorProductos.querySelectorAll('.producto-reserva-fila').forEach(fila => {
        validarExistenciaFila(fila, false);
    });

});
