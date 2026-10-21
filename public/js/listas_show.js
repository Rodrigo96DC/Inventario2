document.addEventListener('DOMContentLoaded', function () {

    var barraAcciones = document.getElementById('barra-acciones');
    var contadorItems = document.getElementById('contador-items');
    var checkTodos    = document.getElementById('check-todos');
    var btnLimpiar    = document.getElementById('btn-limpiar-sel');
    var itemsSeleccionados = new Set();

    // ── Checkboxes ────────────────────────────────────────────────────────────
    function actualizarBarra() {
        var n = itemsSeleccionados.size;
        if (contadorItems) contadorItems.textContent = n;
        if (barraAcciones) {
            if (n > 0) barraAcciones.classList.remove('d-none');
            else       barraAcciones.classList.add('d-none');
        }
        // Actualizar inputs del form papelera
        var contenedor = document.getElementById('inputs-papelera');
        if (contenedor) {
            contenedor.innerHTML = '';
            itemsSeleccionados.forEach(function(id) {
                var input   = document.createElement('input');
                input.type  = 'hidden';
                input.name  = 'items[]';
                input.value = id;
                contenedor.appendChild(input);
            });
        }
    }

    document.querySelectorAll('.check-item').forEach(function(c) {
        c.addEventListener('change', function () {
            if (this.checked) itemsSeleccionados.add(this.value);
            else              itemsSeleccionados.delete(this.value);
            actualizarBarra();
            if (checkTodos) {
                checkTodos.checked = document.querySelectorAll('.check-item').length > 0 &&
                    Array.from(document.querySelectorAll('.check-item')).every(c => c.checked);
            }
        });
    });

    checkTodos?.addEventListener('change', function () {
        document.querySelectorAll('.check-item').forEach(function(c) {
            c.checked = checkTodos.checked;
            if (checkTodos.checked) itemsSeleccionados.add(c.value);
            else                    itemsSeleccionados.delete(c.value);
        });
        actualizarBarra();
    });

    btnLimpiar?.addEventListener('click', function () {
        document.querySelectorAll('.check-item').forEach(function(c) { c.checked = false; });
        if (checkTodos) checkTodos.checked = false;
        itemsSeleccionados.clear();
        actualizarBarra();
    });

    // ── Papelera masivo ───────────────────────────────────────────────────────
    document.getElementById('btn-papelera-masivo')?.addEventListener('click', function () {
        if (itemsSeleccionados.size === 0) return;
        if (confirm('¿Enviar ' + itemsSeleccionados.size + ' equipo(s) a la papelera?')) {
            document.getElementById('form-papelera').submit();
        }
    });

    // ── Actas ─────────────────────────────────────────────────────────────────
    document.getElementById('btn-acta-separadas')?.addEventListener('click', function () {
       
        if (itemsSeleccionados.size === 0) return;
        if (itemsSeleccionados.size > 20) {
            alert('Máximo 20 equipos por descarga.');
            return;
        }
        // Descargar acta por cada equipo seleccionado
        var checks = document.querySelectorAll('.check-item:checked');
        checks.forEach(function(c) {
            var equipoId = c.dataset.equipoId;
            console.log('abriendo:', '/equipos-asignados/' + c.dataset.equipoId + '/baja');
            window.open('/equipos-asignados/' + equipoId + '/baja', '_blank');
        });
    });



    // ── Editar item ───────────────────────────────────────────────────────────
    document.querySelectorAll('.btn-editar-item').forEach(function(btn) {
        btn.addEventListener('click', function () {
            document.getElementById('item-estado').value  = this.dataset.estado;
            document.getElementById('item-detalle').value = this.dataset.detalle ?? '';
            document.getElementById('form-editar-item').action = '/lista-items/' + this.dataset.id;
            new bootstrap.Modal(document.getElementById('modal-editar-item')).show();
        });
    });

    // ── Buscador agregar item ─────────────────────────────────────────────────
    var buscarItem    = document.getElementById('buscar-item');
    var resultadosItem = document.getElementById('resultados-item');
    var itemableId    = document.getElementById('itemable-id');
    var timeoutItem   = null;

 buscarItem?.addEventListener('input', function () {
    clearTimeout(timeoutItem);
    var q = this.value.trim();
    if (q.length < 2) {
        resultadosItem.classList.add('d-none');
        return;
    }

    // Detectar tipo de lista desde el DOM
    var tipoLista = document.querySelector('meta[name="lista-tipo"]')?.content ?? 'equipos';

    var url = tipoLista === 'equipos'
        ? '/interno/equipos/buscar?q=' + encodeURIComponent(q)
        : '/interno/funcionarios/buscar?q=' + encodeURIComponent(q);

    timeoutItem = setTimeout(function () {
        fetch(url)
            .then(function(r) { return r.json(); })
            .then(function(lista) {
                resultadosItem.innerHTML = '';
                lista.forEach(function(item) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'list-group-item list-group-item-action small';

                    if (tipoLista === 'equipos') {
                        btn.innerHTML = '<strong>' + (item.marca ?? '') + ' ' + (item.modelo ?? '') + '</strong>' +
                            '<br><small class="text-secondary">' + (item.inventario ?? '') + '</small>';
                        btn.addEventListener('click', function () {
                            itemableId.value = item.id;
                            buscarItem.value = item.inventario;
                            resultadosItem.classList.add('d-none');
                        });
                    } else {
                        btn.innerHTML = '<strong>' + item.nombre + '</strong>' +
                            '<br><small class="text-secondary">' + (item.rut ?? '') + '</small>';
                        btn.addEventListener('click', function () {
                            itemableId.value = item.id;
                            buscarItem.value = item.nombre;
                            resultadosItem.classList.add('d-none');
                        });
                    }
                    resultadosItem.appendChild(btn);
                });
                resultadosItem.classList.remove('d-none');
            });
    }, 300);
});

// ── Botón abrir modal baja ────────────────────────────────────────────────
document.getElementById('btn-abrir-baja')?.addEventListener('click', function () {
    if (itemsSeleccionados.size === 0) {
        alert('Selecciona al menos un equipo.');
        return;
    }
    if (itemsSeleccionados.size > 20) {
        alert('Máximo 20 equipos por baja.');
        return;
    }
    document.getElementById('baja-cantidad').textContent = itemsSeleccionados.size;
    document.getElementById('baja-detalle').value = '';
    new bootstrap.Modal(document.getElementById('modal-confirmar-baja')).show();
});

// ── Ejecutar baja ─────────────────────────────────────────────────────────
document.getElementById('btn-ejecutar-baja')?.addEventListener('click', function () {
    var opcion  = document.querySelector('input[name="opcion-baja"]:checked')?.value ?? 'sin_documento';
    var detalle = document.getElementById('baja-detalle')?.value.trim();

    
  // Si es separadas — abrir ventanas

if (opcion === 'separadas') {
    document.querySelectorAll('.check-item:checked').forEach(function(c) {
        window.open('/equipos-asignados/' + c.dataset.equipoId + '/baja?detalle=' + encodeURIComponent(detalle), '_blank');
    });
}

    // Enviar a papelera
    var formPapelera = document.getElementById('form-papelera');
    var contenedor   = document.getElementById('inputs-papelera');
    contenedor.innerHTML = '';

    var inputDetalle   = document.createElement('input');
    inputDetalle.type  = 'hidden';
    inputDetalle.name  = 'detalle';
    inputDetalle.value = detalle;
    contenedor.appendChild(inputDetalle);

    var inputOpcion   = document.createElement('input');
    inputOpcion.type  = 'hidden';
    inputOpcion.name  = 'opcion_documento';
    inputOpcion.value = opcion;
    contenedor.appendChild(inputOpcion);

    document.querySelectorAll('.check-item:checked').forEach(function(c) {
        var input   = document.createElement('input');
        input.type  = 'hidden';
        input.name  = 'items[]';
        input.value = c.value;
        contenedor.appendChild(input);
    });

    bootstrap.Modal.getInstance(document.getElementById('modal-confirmar-baja'))?.hide();
    setTimeout(function() {
        formPapelera.submit();
    }, opcion === 'separadas' ? 1000 : 200);
});


// ── Tabs pendientes/completados ───────────────────────────────────────────
document.querySelectorAll('[data-tab]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('[data-tab]').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('tab-pendientes').classList.toggle('d-none', this.dataset.tab !== 'pendientes');
        document.getElementById('tab-completados').classList.toggle('d-none', this.dataset.tab !== 'completados');
        
        // Guardar tab en URL como parámetro
        var url = new URL(window.location.href);
        url.searchParams.set('tab', this.dataset.tab);
        history.replaceState(null, '', url.toString());
    });
});

// Restaurar tab desde URL
var tabActivo = new URL(window.location.href).searchParams.get('tab') ?? 'pendientes';
var btnActivo = document.querySelector('[data-tab="' + tabActivo + '"]');
if (btnActivo) btnActivo.click();

const hash = window.location.hash.replace('#', '');
if (hash) document.querySelector(`[data-tab="${hash}"]`)?.click();

});