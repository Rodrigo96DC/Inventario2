document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-filtros');
    const tabla = document.getElementById('contenedor-tabla');
    const btnExportar = document.getElementById('btn-exportar');
    const baseExportar = btnExportar?.dataset.url;
    let timeout = null;

    function filtrar() {
        const params = new URLSearchParams(new FormData(form));

        // Actualiza URL del botón exportar con los filtros actuales
        if (btnExportar) {
            btnExportar.href = `${baseExportar}?${params}`;
        }

        fetch(`${form.action}?${params}&ajax=1`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.text())
        .then(html => {
            tabla.innerHTML = html;
            const total = tabla.querySelector('[data-total]');
            if (total) {
                document.getElementById('badge-total').textContent = total.dataset.total;
            }
        });
    }

    // Buscador con debounce
    document.getElementById('buscar')?.addEventListener('input', function () {
        clearTimeout(timeout);
        timeout = setTimeout(filtrar, 400);
    });

    // Selects y fechas: filtrar inmediato
    form?.querySelectorAll('select, input[type="date"]').forEach(el => {
        el.addEventListener('change', filtrar);
    });
});