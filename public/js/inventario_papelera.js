document.addEventListener('DOMContentLoaded', function () {

    var modalActa      = new bootstrap.Modal(document.getElementById('modal-acta-baja'));
    var equipoIdActual = null;

    // ── Botones acta baja ─────────────────────────────────────────────────────
    function iniciarBotonesActa() {
        document.querySelectorAll('.btn-acta-baja').forEach(function(btn) {
            var nuevo = btn.cloneNode(true);
            btn.parentNode.replaceChild(nuevo, btn);
    nuevo.addEventListener('click', function() {
    equipoIdActual = this.dataset.id;
    document.getElementById('modal-baja-inventario').textContent = this.dataset.inventario;
    document.getElementById('modal-baja-detalle').value = this.dataset.detalle ?? '';
    modalActa.show();
});
        });
    }

    iniciarBotonesActa();

    document.getElementById('btn-descargar-acta')?.addEventListener('click', function() {
        if (!equipoIdActual) return;
        var detalle = document.getElementById('modal-baja-detalle').value;
        var url = '/equipos-asignados/' + equipoIdActual + '/baja?detalle=' + encodeURIComponent(detalle);
        window.open(url, '_blank');
        modalActa.hide();
    });

    // ── Buscador AJAX ─────────────────────────────────────────────────────────
    var buscarPapelera = document.getElementById('buscar-papelera');
    if (buscarPapelera) {
        var timeout = null;
        buscarPapelera.addEventListener('input', function () {
            clearTimeout(timeout);
            var q = this.value;
            timeout = setTimeout(function () {
                var url = new URL(window.location.href);
                url.searchParams.set('buscar', q);
                fetch(url.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(r) { return r.text(); })
                .then(function(html) {
                    var parser   = new DOMParser();
                    var doc      = parser.parseFromString(html, 'text/html');
                    var tbody    = document.querySelector('table tbody');
                    var newTbody = doc.querySelector('table tbody');
                    var pag      = document.getElementById('paginacion');
                    var newPag   = doc.getElementById('paginacion');
                    if (tbody && newTbody) tbody.innerHTML = newTbody.innerHTML;
                    if (pag && newPag)     pag.innerHTML   = newPag.innerHTML;
                    iniciarBotonesActa();
                });
            }, 350);
        });
    }
});