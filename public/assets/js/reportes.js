document.addEventListener('DOMContentLoaded', () => {
    const buscador = document.getElementById('buscarReporte');
    const filas = Array.from(document.querySelectorAll('.report-row'));
    const mensajeVacio = document.getElementById('sinReportes');
    const formularioFiltros = document.getElementById('filtrosReportes');
    const selectorPeriodo = document.getElementById('periodo');
    const campoDesde = document.getElementById('desde');
    const campoHasta = document.getElementById('hasta');


    if (selectorPeriodo && campoDesde && campoHasta) {
        const actualizarFechas = () => {
            const personalizado = selectorPeriodo.value === 'personalizado';
            campoDesde.disabled = !personalizado;
            campoHasta.disabled = !personalizado;
            campoDesde.required = personalizado;
            campoHasta.required = personalizado;
        };

        selectorPeriodo.addEventListener('change', () => {
            actualizarFechas();
            if (selectorPeriodo.value !== 'personalizado' && formularioFiltros) {
                formularioFiltros.submit();
            }
        });
        actualizarFechas();
    }

    if (!buscador) return;

    buscador.addEventListener('input', () => {
        const termino = buscador.value.trim().toLocaleLowerCase('es');
        let visibles = 0;

        filas.forEach((fila) => {
            const contenido = (fila.dataset.search || '').toLocaleLowerCase('es');
            const coincide = contenido.includes(termino);
            fila.hidden = !coincide;
            if (coincide) visibles++;
        });

        if (mensajeVacio) mensajeVacio.hidden = visibles !== 0;
    });

    // Permite abrir el detalle haciendo clic en cualquier parte de la fila.
    filas.forEach((fila) => {
        fila.addEventListener('click', (evento) => {
            if (evento.target.closest('a, button')) return;
            const enlace = fila.querySelector('.report-row-link');
            if (enlace) window.location.href = enlace.href;
        });
    });
});
