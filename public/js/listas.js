document.addEventListener('DOMContentLoaded', function () {

    // ── Editar lista ──────────────────────────────────────────────────────────
    document.querySelectorAll('.btn-editar-lista').forEach(function(btn) {
        btn.addEventListener('click', function () {
            document.getElementById('lista-nombre').value      = this.dataset.nombre;
            document.getElementById('lista-estado').value      = this.dataset.estado;
            document.getElementById('lista-descripcion').value = this.dataset.descripcion ?? '';
            document.getElementById('lista-publica').checked   = this.dataset.publica === '1';
            document.getElementById('lista-tipo').disabled     = true;
            document.getElementById('lista-method').value      = 'PUT';
            document.getElementById('form-lista').action       = '/listas/' + this.dataset.id;
            document.getElementById('titulo-modal-lista').innerHTML =
                '<i class="fas fa-pen me-2"></i>Editar Lista';
            new bootstrap.Modal(document.getElementById('modal-nueva-lista')).show();
        });
    });

    // Reset modal al abrir nuevo
    document.querySelector('[data-bs-target="#modal-nueva-lista"]')?.addEventListener('click', function () {
        document.getElementById('lista-nombre').value      = '';
        document.getElementById('lista-estado').value      = 'en_proceso';
        document.getElementById('lista-descripcion').value = '';
        document.getElementById('lista-publica').checked   = false;
        document.getElementById('lista-tipo').disabled     = false;
        document.getElementById('lista-method').value      = 'POST';
        document.getElementById('form-lista').action       = '/listas';
        document.getElementById('titulo-modal-lista').innerHTML =
            '<i class="fas fa-plus me-2"></i>Nueva Lista';
    });


    // Mostrar/ocultar sección baja según tipo
document.getElementById('lista-tipo')?.addEventListener('change', function () {
    var divBaja = document.getElementById('div-auto-baja');
    if (this.value === 'equipos') {
        divBaja.classList.remove('d-none');
    } else {
        divBaja.classList.add('d-none');
        document.getElementById('lista-auto-baja').checked = false;
        document.getElementById('div-excel-baja').classList.add('d-none');
    }
});

// Mostrar/ocultar Excel según si es baja
document.getElementById('lista-auto-baja')?.addEventListener('change', function () {
    var divExcel = document.getElementById('div-excel-baja');
    if (this.checked) {
        divExcel.classList.remove('d-none');
    } else {
        divExcel.classList.add('d-none');
    }
});

// ── Tabs index ────────────────────────────────────────────────────────────
document.querySelectorAll('[data-tab]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('[data-tab]').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        ['activas', 'completadas', 'eliminadas'].forEach(function(t) {
            document.getElementById('tab-' + t)?.classList.toggle('d-none', t !== btn.dataset.tab);
        });
        var url = new URL(window.location.href);
        url.searchParams.set('tab', btn.dataset.tab);
        history.replaceState(null, '', url.toString());
    });
});

var tabActivo = new URL(window.location.href).searchParams.get('tab') ?? 'activas';
var btnActivo = document.querySelector('[data-tab="' + tabActivo + '"]');
if (btnActivo) btnActivo.click();

const hash = window.location.hash.replace('#', '');
if (hash) document.querySelector(`[data-tab="${hash}"]`)?.click();


});