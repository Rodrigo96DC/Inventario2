document.addEventListener('DOMContentLoaded', function () {
    const tipoSelect  = document.getElementById('tipo_equipo_id');
    const equipoSelect = document.getElementById('equipo_id');
    const buscar = document.getElementById('buscar');
    const btnExportar = document.getElementById('btn-exportar');
    const form = document.getElementById('form-filtros');

    // Carga equipos al cambiar tipo
tipoSelect?.addEventListener('change', function () {
    const tipoId = this.value;
    equipoSelect.innerHTML = '<option value="">Cargando...</option>';

    if (! tipoId) {
        equipoSelect.innerHTML = '<option value="">Seleccione modelo</option>';
        return;
    }

    fetch(`${routeEquiposPorTipo}?tipo_id=${tipoId}`)
        .then(res => {
            if (! res.ok) throw new Error(`Error ${res.status}`);
            return res.json();
        })
        .then(equipos => {
            equipoSelect.innerHTML = '<option value="">Seleccione modelo</option>';
            if (equipos.length === 0) {
                equipoSelect.innerHTML = '<option value="">Sin equipos para este tipo</option>';
                return;
            }
            equipos.forEach(e => {
                const opt = document.createElement('option');
                opt.value = e.id;
                opt.textContent = `${e.marca} ${e.modelo}`;
                equipoSelect.appendChild(opt);
            });
        })
        .catch(err => {
            equipoSelect.innerHTML = '<option value="">Error al cargar</option>';
            console.error('Error cargando equipos:', err);
        });
});

    /// Buscador con debounce AJAX
    if (buscar && form) {
        let timeout = null;
        buscar.addEventListener('input', function () {
             console.log('input inventario:', buscar.value);
            clearTimeout(timeout);
            var q = buscar.value;
            timeout = setTimeout(function () {
                actualizarExportar();

                var url = new URL(window.location.href);
                url.searchParams.set('buscar', q);

                fetch(url.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(r) { return r.text(); })
  .then(function(html) {
    var parser  = new DOMParser();
    var doc     = parser.parseFromString(html, 'text/html');
    var cont    = document.getElementById('contenedor-inventario');
    var newCont = doc.getElementById('contenedor-inventario');
    var pag     = document.getElementById('paginacion');
    var newPag  = doc.getElementById('paginacion');

   if (cont && newCont) {
    cont.innerHTML = newCont.innerHTML;
    iniciarZoom(); // ← agrega esto
}
    if (pag && newPag)   pag.innerHTML  = newPag.innerHTML;
});
            }, 350);
        });
    }

    // Selects filtro: submit inmediato
    form?.querySelectorAll('select').forEach(el => {
        el.addEventListener('change', () => {
            actualizarExportar();
            form.submit();
        });
    });

    function actualizarExportar() {
        if (! btnExportar || ! form) return;
        const params = new URLSearchParams(new FormData(form));
        btnExportar.href = `${btnExportar.dataset.url}?${params}`;
    }


// Si hay tipo preseleccionado (edición), cargar modelos automáticamente
if (typeof tipoSeleccionado !== 'undefined' && tipoSeleccionado && tipoSelect) {
    tipoSelect.value = tipoSeleccionado;

    fetch(`${routeEquiposPorTipo}?tipo_id=${tipoSeleccionado}`)
        .then(res => res.json())
        .then(equipos => {
            equipoSelect.innerHTML = '<option value="">Seleccione modelo</option>';
            equipos.forEach(e => {
                const opt = document.createElement('option');
                opt.value = e.id;
                opt.textContent = `${e.marca} ${e.modelo}`;
                if (e.id === equipoSeleccionado) opt.selected = true;
                equipoSelect.appendChild(opt);
            });
        });
}

function iniciarZoom() {
    document.querySelectorAll('.card-img-wrapper').forEach(function(wrapper) {
        if (wrapper.dataset.zoomInit) return;
        wrapper.dataset.zoomInit = '1';

        var timerZoom   = null;
        var timerSalida = null;

        wrapper.addEventListener('mouseenter', function () {
            clearTimeout(timerSalida);
            timerZoom = setTimeout(function() {
                wrapper.classList.add('zoom-activo');
            }, 1200);
        });

        wrapper.addEventListener('mouseleave', function () {
            clearTimeout(timerZoom);
            timerSalida = setTimeout(function() {
                wrapper.classList.remove('zoom-activo');
            }, 200);
        });
    });
}

// Llamar al inicio y después de cada búsqueda AJAX
iniciarZoom();
});