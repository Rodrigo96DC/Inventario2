document.addEventListener('DOMContentLoaded', function () {

    // ── Buscador AJAX ─────────────────────────────────────────────────────────
    const buscador = document.getElementById('buscar');
    if (buscador) {
        let timeout = null;
        buscador.addEventListener('input', function () {
            clearTimeout(timeout);
            var q = buscador.value;
            timeout = setTimeout(function () {
                var url = new URL(window.location.href);
                url.searchParams.set('buscar', q);
                fetch(url.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(r) { return r.text(); })
                .then(function(html) {
                    var parser = new DOMParser();
                    var doc    = parser.parseFromString(html, 'text/html');

                    var grid    = document.getElementById('contenedor-grid');
                    var tabla   = document.getElementById('contenedor-tabla');
                    var newGrid = doc.getElementById('contenedor-grid');
                    var newTabla= doc.getElementById('contenedor-tabla');
                    var pag     = document.getElementById('paginacion');
                    var newPag  = doc.getElementById('paginacion');

                    if (grid && newGrid)   grid.innerHTML   = newGrid.innerHTML;
                    if (tabla && newTabla) tabla.innerHTML  = newTabla.innerHTML;
                    if (pag && newPag)     pag.innerHTML    = newPag.innerHTML;

                    aplicarTamano(localStorage.getItem('equipos-tamano') || 'md');
                    iniciarZoom();
                });
            }, 350);
        });
    }

    // ── Toggle vista grid/tabla ───────────────────────────────────────────────
    const btnGrid    = document.getElementById('vista-grid');
    const btnTabla   = document.getElementById('vista-tabla');
    const vistaGrid  = document.getElementById('contenedor-grid');
    const vistaTabla = document.getElementById('contenedor-tabla');
    const vistaGuardada = localStorage.getItem('equipos-vista') || 'grid';

    function aplicarVista(vista) {
        const esGrid = vista === 'grid';
        vistaGrid?.classList.toggle('d-none', !esGrid);
        vistaTabla?.classList.toggle('d-none', esGrid);
        btnGrid?.classList.toggle('active', esGrid);
        btnTabla?.classList.toggle('active', !esGrid);
        localStorage.setItem('equipos-vista', vista);
    }

    aplicarVista(vistaGuardada);
    btnGrid?.addEventListener('click',  () => aplicarVista('grid'));
    btnTabla?.addEventListener('click', () => aplicarVista('tabla'));

    // ── Tamaño de cards ───────────────────────────────────────────────────────
    const sizeButtons  = document.querySelectorAll('[data-card-size]');
    const gridEquipos  = document.getElementById('grid-equipos');
    const tamanoGuardado = localStorage.getItem('equipos-tamano') || 'md';

    function aplicarTamano(tamano) {
        const clases = {
            sm: 'col-6 col-sm-4 col-md-3 col-lg-2',
            md: 'col-12 col-sm-6 col-md-4 col-lg-3',
            lg: 'col-12 col-sm-6 col-md-4',
        };
        if (!gridEquipos) return;
        gridEquipos.querySelectorAll('.equipo-col').forEach(col => {
            col.className = 'equipo-col ' + (clases[tamano] || clases.md);
        });
        sizeButtons.forEach(btn =>
            btn.classList.toggle('active', btn.dataset.cardSize === tamano));
        localStorage.setItem('equipos-tamano', tamano);
    }

    aplicarTamano(tamanoGuardado);
    sizeButtons.forEach(btn => {
        btn.addEventListener('click', () => aplicarTamano(btn.dataset.cardSize));
    });

    // ── Zoom de imagen ────────────────────────────────────────────────────────
 function iniciarZoom() {
    document.querySelectorAll('.card-img-wrapper').forEach(wrapper => {
        if (wrapper.dataset.zoomInit) return;
        wrapper.dataset.zoomInit = '1';

        let timerZoom   = null;
        let timerSalida = null;

        wrapper.addEventListener('mouseenter', function () {
            clearTimeout(timerSalida);
            timerZoom = setTimeout(() => {
                wrapper.classList.add('zoom-activo');
            }, 1200);
        });

        wrapper.addEventListener('mouseleave', function () {
            clearTimeout(timerZoom);
            timerSalida = setTimeout(() => {
                wrapper.classList.remove('zoom-activo');
            }, 200);
        });
    });
}

    iniciarZoom();
});