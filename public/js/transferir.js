document.addEventListener('DOMContentLoaded', function () {

    // ── Variables ─────────────────────────────────────────────────────────────
    var form          = document.getElementById('form-transferir');
    var inputBuscar   = document.getElementById('buscar-destino');
    var resultadosDiv = document.getElementById('resultados-destino');
    var inputHidden   = document.getElementById('funcionario_destino_id');
    var destinoCard   = document.getElementById('destino-seleccionado');
    var destinoNombre = document.getElementById('destino-nombre');
    var btnCambiar    = document.getElementById('btn-cambiar-destino');
    var checkTodos    = document.getElementById('check-todos');
    var btnTodos      = document.getElementById('btn-todos');
    var btnAbrirModal = document.getElementById('btn-abrir-modal-hostnames');
    var modalEl       = document.getElementById('modal-hostnames');
    var modalBS       = modalEl ? new bootstrap.Modal(modalEl) : null;
    var btnConfirmar  = document.getElementById('btn-confirmar-transferir');
    var tiposConHostname = ['Computador', 'Notebook'];
    var timeout       = null;

    // Map en memoria — se pierde al recargar, correcto
    var equiposSeleccionados = new Map();

    // ── Helper ────────────────────────────────────────────────────────────────
    function esc(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function getDatosFila(checkbox) {
        var row    = checkbox.closest('tr');
        return {
            id:        checkbox.value,
            tipo:      row.dataset.tipo ?? '',
            marca:     row.querySelector('td:nth-child(3) div')?.textContent.trim() ?? '',
            modelo:    row.querySelector('td:nth-child(3) small')?.textContent.trim() ?? '',
            inventario: row.querySelector('td:nth-child(4)')?.textContent.trim() ?? '',
            hostname:  row.querySelector('td:nth-child(5)')?.textContent.trim() ?? '—',
        };
    }

    function sincronizarInputs() {
        var contenedor = document.getElementById('inputs-equipos');
        if (!contenedor) return;
        contenedor.innerHTML = '';
        equiposSeleccionados.forEach(function(d, id) {
            var input   = document.createElement('input');
            input.type  = 'hidden';
            input.name  = 'equipos[]';
            input.value = id;
            contenedor.appendChild(input);
        });
    }

    // ── AJAX helper ───────────────────────────────────────────────────────────
    function cargarPagina(url) {
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.text(); })
            .then(function(html) {
                var parser   = new DOMParser();
                var doc      = parser.parseFromString(html, 'text/html');
                var tbody    = document.querySelector('table tbody');
                var newTbody = doc.querySelector('table tbody');
                var pag      = document.getElementById('paginacion-transferir');
                var newPag   = doc.getElementById('paginacion-transferir');
                if (tbody && newTbody) tbody.innerHTML = newTbody.innerHTML;
                if (pag && newPag)     pag.innerHTML   = newPag.innerHTML;
                iniciarCheckboxes();
            });
    }

    // ── Checkboxes ────────────────────────────────────────────────────────────
    function iniciarCheckboxes() {
        // Clonar para eliminar listeners anteriores
        document.querySelectorAll('.check-equipo').forEach(function(c) {
            var nuevo = c.cloneNode(true);
            c.parentNode.replaceChild(nuevo, c);
        });

        // Obtener checks frescos y configurar
        document.querySelectorAll('.check-equipo').forEach(function(c) {
            c.checked = equiposSeleccionados.has(c.value);
            c.addEventListener('change', function () {
                if (this.checked) {
                    equiposSeleccionados.set(this.value, getDatosFila(this));
                } else {
                    equiposSeleccionados.delete(this.value);
                }
                sincronizarInputs();
                actualizarCheckTodos();
            });
        });

        actualizarCheckTodos();
        iniciarPaginacion();
    }

    function actualizarCheckTodos() {
        if (!checkTodos) return;
        var checks = document.querySelectorAll('.check-equipo');
        checkTodos.checked = checks.length > 0 && Array.from(checks).every(function(c) {
            return c.checked;
        });
    }

    function iniciarPaginacion() {
        document.querySelectorAll('#paginacion-transferir a').forEach(function(link) {
            var nuevo = link.cloneNode(true);
            link.parentNode.replaceChild(nuevo, link);
            nuevo.addEventListener('click', function(e) {
                e.preventDefault();
                cargarPagina(this.href);
            });
        });
    }

    // checkTodos — solo una vez
    if (checkTodos) {
        checkTodos.addEventListener('change', function () {
            document.querySelectorAll('.check-equipo').forEach(function(c) {
                c.checked = checkTodos.checked;
                if (checkTodos.checked) {
                    equiposSeleccionados.set(c.value, getDatosFila(c));
                } else {
                    equiposSeleccionados.delete(c.value);
                }
            });
            sincronizarInputs();
        });
    }

    // btnTodos — solo una vez
    if (btnTodos) {
        btnTodos.addEventListener('click', function () {
            var checks = document.querySelectorAll('.check-equipo');
            var todos  = Array.from(checks).every(function(c) { return c.checked; });
            checks.forEach(function(c) {
                c.checked = !todos;
                if (!todos) {
                    equiposSeleccionados.set(c.value, getDatosFila(c));
                } else {
                    equiposSeleccionados.delete(c.value);
                }
            });
            if (checkTodos) checkTodos.checked = !todos;
            btnTodos.textContent = todos ? 'Seleccionar todos' : 'Deseleccionar todos';
            sincronizarInputs();
        });
    }

    iniciarCheckboxes();

    // ── Buscador equipos AJAX ─────────────────────────────────────────────────
    var buscarEquipo = document.getElementById('buscar-equipo');
    if (buscarEquipo) {
        var timeoutBuscar = null;
        buscarEquipo.addEventListener('input', function () {
            clearTimeout(timeoutBuscar);
            var q   = this.value;
            var url = new URL(window.location.href);
            if (q === '') {
                url.searchParams.delete('buscar');
            } else {
                url.searchParams.set('buscar', q);
            }
            timeoutBuscar = setTimeout(function () {
                cargarPagina(url.toString());
            }, 350);
        });
    }

    // ── Buscador funcionario destino ──────────────────────────────────────────
    if (inputBuscar) {
        inputBuscar.addEventListener('input', function () {
            clearTimeout(timeout);
            var q = this.value.trim();
            if (q.length < 2) {
                resultadosDiv.classList.add('d-none');
                resultadosDiv.innerHTML = '';
                return;
            }
            timeout = setTimeout(function () {
                fetch(routeBuscarFuncionario + '?q=' + encodeURIComponent(q))
                    .then(function(r) { return r.json(); })
                    .then(function(lista) {
                        resultadosDiv.innerHTML = '';
                        if (lista.length === 0) {
                            resultadosDiv.innerHTML = '<div class="list-group-item text-secondary small">Sin resultados</div>';
                        } else {
                            lista.forEach(function(f) {
                                var item = document.createElement('button');
                                item.type = 'button';
                                item.className = 'list-group-item list-group-item-action';
                                item.innerHTML = '<div class="fw-medium">' + esc(f.nombre) + '</div>' +
                                    '<small class="text-secondary">' + esc(f.rut || '') + ' · ' + esc(f.cargo || '') + '</small>';
                                item.addEventListener('click', function() { seleccionarDestino(f); });
                                resultadosDiv.appendChild(item);
                            });
                        }
                        resultadosDiv.classList.remove('d-none');
                    });
            }, 300);
        });
    }

    function seleccionarDestino(f) {
        inputHidden.value         = f.id;
        destinoNombre.textContent = f.nombre;
        inputBuscar.value         = '';
        resultadosDiv.classList.add('d-none');
        destinoCard.classList.remove('d-none');
        inputBuscar.classList.add('d-none');
    }

    if (btnCambiar) {
        btnCambiar.addEventListener('click', function () {
            inputHidden.value = '';
            destinoCard.classList.add('d-none');
            inputBuscar.classList.remove('d-none');
            inputBuscar.value = '';
            inputBuscar.focus();
        });
    }

    document.addEventListener('click', function (e) {
        if (!inputBuscar?.contains(e.target) && !resultadosDiv?.contains(e.target)) {
            resultadosDiv?.classList.add('d-none');
        }
    });

    // ── Modal hostnames ───────────────────────────────────────────────────────
    if (btnAbrirModal) {
        btnAbrirModal.addEventListener('click', function () {
            if (!inputHidden.value) {
                alert('Debes seleccionar un funcionario de destino.');
                return;
            }
            if (equiposSeleccionados.size === 0) {
                alert('Debes seleccionar al menos un equipo para transferir.');
                return;
            }

            var tbody = document.getElementById('modal-hostnames-body');
            tbody.innerHTML = '';

            equiposSeleccionados.forEach(function(datos, id) {
                var hostnameCell = tiposConHostname.includes(datos.tipo)
                    ? '<input type="text" class="form-control form-control-sm hostname-input" data-equipo="' + id + '" value="' + (datos.hostname === '—' ? '' : esc(datos.hostname)) + '" placeholder="Sin hostname">'
                    : '<span class="text-secondary small">No aplica</span>';

                var tr = document.createElement('tr');
                tr.innerHTML =
                    '<td class="small">' + esc(datos.marca) + ' ' + esc(datos.modelo) +
                    ' <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle ms-1" style="font-size:.65rem;">' + esc(datos.tipo) + '</span></td>' +
                    '<td class="small fw-medium">' + esc(datos.inventario) + '</td>' +
                    '<td>' + hostnameCell + '</td>';
                tbody.appendChild(tr);
            });

            modalBS?.show();
        });
    }

    if (btnConfirmar) {
        btnConfirmar.addEventListener('click', function () {
            document.querySelectorAll('input[name^="hostnames"]').forEach(function(el) { el.remove(); });
            document.querySelectorAll('.hostname-input').forEach(function(input) {
                if (input.value.trim() === '') return;
                var hidden  = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'hostnames[' + input.dataset.equipo + ']';
                hidden.value = input.value.trim();
                form.appendChild(hidden);
            });

            if (modalEl) {
                modalEl.addEventListener('hidden.bs.modal', function () {
                    form.submit();
                }, { once: true });
                modalBS?.hide();
            } else {
                form.submit();
            }
        });
    }

}); //----->Fin del DOM no borrar