document.addEventListener('DOMContentLoaded', function () {

    // ── Buscador AJAX (solo en index) ─────────────────────────────────────────
    var buscador = document.getElementById('buscar');
    if (buscador) {
        var timeout = null;
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
                    var parser   = new DOMParser();
                    var doc      = parser.parseFromString(html, 'text/html');
                    var tbody    = document.querySelector('table tbody');
                    var newTbody = doc.querySelector('table tbody');
                    if (tbody && newTbody) tbody.innerHTML = newTbody.innerHTML;
                    var pag    = document.getElementById('paginacion');
                    var newPag = doc.getElementById('paginacion');
                    if (pag && newPag) pag.innerHTML = newPag.innerHTML;
                    
                });
            }, 350);
        });
    }

       if (buscador) {
        var timeout = null;
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
                    var parser   = new DOMParser();
                    var doc      = parser.parseFromString(html, 'text/html');
                    var tbody    = document.querySelector('table tbody');
                    var newTbody = doc.querySelector('table tbody');
                    if (tbody && newTbody) tbody.innerHTML = newTbody.innerHTML;
                    var pag    = document.getElementById('paginacion');
                    var newPag = doc.getElementById('paginacion');
                    if (pag && newPag) pag.innerHTML = newPag.innerHTML;
                    // Submit automático en selects
document.querySelectorAll('#form-filtros select').forEach(function(sel) {
    sel.addEventListener('change', function() {
        document.getElementById('form-filtros').submit();
    });
});
                });
            }, 350);
        });
    }

    // ── Cascada División → Departamento → Unidad ──────────────────────────────
    var divSelect = document.getElementById('division_id');
    var depSelect = document.getElementById('departamento_id');
    var uniSelect = document.getElementById('unidad_id');

    if (divSelect) {
        divSelect.addEventListener('change', function () {
            depSelect.innerHTML = '<option value="">Cargando...</option>';
            uniSelect.innerHTML = '<option value="">Seleccione departamento primero</option>';

            if (!this.value) {
                depSelect.innerHTML = '<option value="">Seleccione división primero</option>';
                return;
            }

            fetch('/interno/departamentos-por-division?division_id=' + this.value)
                .then(function(r) { return r.json(); })
                .then(function(deps) {
                    depSelect.innerHTML = '<option value="">Seleccione</option>';
                    deps.forEach(function(d) {
                        var opt = document.createElement('option');
                        opt.value = d.id;
                        opt.textContent = d.nombre;
                        depSelect.appendChild(opt);
                    });
                });
        });
    }

    if (depSelect) {
        depSelect.addEventListener('change', function () {
            uniSelect.innerHTML = '<option value="">Cargando...</option>';

            if (!this.value) {
                uniSelect.innerHTML = '<option value="">Seleccione departamento primero</option>';
                return;
            }

            fetch('/interno/unidades-por-departamento?departamento_id=' + this.value)
                .then(function(r) { return r.json(); })
                .then(function(unis) {
                    uniSelect.innerHTML = '<option value="">Seleccione</option>';
                    unis.forEach(function(u) {
                        var opt = document.createElement('option');
                        opt.value = u.id;
                        opt.textContent = u.nombre;
                        uniSelect.appendChild(opt);
                    });
                });
        });
    }

    // ── Cargar departamentos y unidades al editar ─────────────────────────────
var divSeleccionada = divSelect ? divSelect.value : null;
var depSeleccionado = depSelect ? depSelect.value : null;

if (divSeleccionada) {
    fetch('/interno/departamentos-por-division?division_id=' + divSeleccionada)
        .then(function(r) { return r.json(); })
        .then(function(deps) {
            depSelect.innerHTML = '<option value="">Seleccione</option>';
            deps.forEach(function(d) {
                var opt = document.createElement('option');
                opt.value = d.id;
                opt.textContent = d.nombre;
                if (d.id == depSeleccionado) opt.selected = true;
                depSelect.appendChild(opt);
            });

            // Cargar unidades si hay departamento seleccionado
            if (depSeleccionado) {
                fetch('/interno/unidades-por-departamento?departamento_id=' + depSeleccionado)
                    .then(function(r) { return r.json(); })
                    .then(function(unis) {
                        var uniSeleccionada = uniSelect ? uniSelect.value : null;
                        uniSelect.innerHTML = '<option value="">Seleccione</option>';
                        unis.forEach(function(u) {
                            var opt = document.createElement('option');
                            opt.value = u.id;
                            opt.textContent = u.nombre;
                            if (u.id == uniSeleccionada) opt.selected = true;
                            uniSelect.appendChild(opt);
                        });
                    });
            }
        });
}


// ── Selección múltiple ────────────────────────────────────────────────────
var checkTodos   = document.getElementById('check-todos');
var barraMasiva  = document.getElementById('barra-masiva');
var contadorSel  = document.getElementById('contador-sel');
var inputsMasivo = document.getElementById('inputs-masivo');
var btnLimpiar   = document.getElementById('btn-limpiar-sel');

function actualizarBarra() {
    var checks = document.querySelectorAll('.check-funcionario:checked');
    var n = checks.length;
    contadorSel.textContent = n;

    if (n > 0) {
        barraMasiva.classList.remove('d-none');
        // Deshabilitar botones de acción individuales
        document.querySelectorAll('.btn-accion-individual').forEach(function(btn) {
            btn.classList.add('disabled');
            btn.setAttribute('tabindex', '-1');
        });
        // Generar inputs hidden
        inputsMasivo.innerHTML = '';
        checks.forEach(function(c) {
            var input = document.createElement('input');
            input.type  = 'hidden';
            input.name  = 'ids[]';
            input.value = c.value;
            inputsMasivo.appendChild(input);
        });
    } else {
        barraMasiva.classList.add('d-none');
        document.querySelectorAll('.btn-accion-individual').forEach(function(btn) {
            btn.classList.remove('disabled');
            btn.removeAttribute('tabindex');
        });
    }
}

checkTodos?.addEventListener('change', function() {
    document.querySelectorAll('.check-funcionario').forEach(function(c) {
        c.checked = checkTodos.checked;
    });
    actualizarBarra();
});

document.querySelectorAll('.check-funcionario').forEach(function(c) {
    c.addEventListener('change', actualizarBarra);
});

btnLimpiar?.addEventListener('click', function() {
    document.querySelectorAll('.check-funcionario').forEach(function(c) {
        c.checked = false;
    });
    if (checkTodos) checkTodos.checked = false;
    actualizarBarra();
});

});//---------> fin del DOM no eliminar