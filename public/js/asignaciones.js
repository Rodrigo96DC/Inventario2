document.addEventListener('DOMContentLoaded', function () {

    // ─── Estado ───────────────────────────────────────────────────────────────
    let funcionarioActivo = null;


    // ─── Elementos ────────────────────────────────────────────────────────────
    const inputBuscar = document.getElementById('buscar-funcionario');
    const resultadosDiv = document.getElementById('resultados-funcionario');
    const cardFuncionario = document.getElementById('card-funcionario');
    const barraAccion = document.getElementById('barra-accion');
    const contadorSel = document.getElementById('contador-seleccionados');
    const btnAbrirModal = document.getElementById('btn-abrir-modal-asignar');
    const btnLimpiar = document.getElementById('btn-limpiar');
    const btnConfirmar = document.getElementById('btn-confirmar-asignar');
    const modalAsignar = new bootstrap.Modal(document.getElementById('modal-asignar'));
    const modalHistorial = new bootstrap.Modal(document.getElementById('modal-historial'));
    const seleccionados = new Map(); // id -> {id, marca, modelo, inventario}

    // ─── Búsqueda de funcionario ──────────────────────────────────────────────
    let timeoutBuscar = null;
    inputBuscar?.addEventListener('input', function () {
        clearTimeout(timeoutBuscar);
        const q = this.value.trim();

        if (q.length < 2) {
            resultadosDiv.classList.add('d-none');
            resultadosDiv.innerHTML = '';
            return;
        }

        timeoutBuscar = setTimeout(() => {
            fetch(`${routes.buscarFuncionario}?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(lista => {
                    resultadosDiv.innerHTML = '';

                    if (lista.length === 0) {
                        resultadosDiv.innerHTML = '<div class="list-group-item text-secondary small">Sin resultados</div>';
                    } else {
                        lista.forEach(f => {
                            const item = document.createElement('button');
                            item.type = 'button';
                            item.className = 'list-group-item list-group-item-action';
                            item.innerHTML = `<div class="fw-medium">${f.nombre}</div><small class="text-secondary">${f.rut ?? ''} · ${f.cargo ?? ''}</small>`;
                            item.addEventListener('click', () => seleccionarFuncionario(f));
                            resultadosDiv.appendChild(item);
                        });
                    }

                    resultadosDiv.classList.remove('d-none');
                });
        }, 300);
    });

    // ─── Seleccionar funcionario ──────────────────────────────────────────────
    function seleccionarFuncionario(f) {
        funcionarioActivo = f;
        resultadosDiv.classList.add('d-none');
        inputBuscar.value = f.nombre;
        console.log('funcionario-nombre:', document.getElementById('funcionario-nombre'));
        console.log('funcionario-rut:', document.getElementById('funcionario-rut'));
        // Llenar perfil
        document.getElementById('funcionario-nombre').textContent = f.nombre;
        document.getElementById('funcionario-rut').textContent = f.rut ?? '—';
        document.getElementById('funcionario-region').textContent = f.region ?? '—';
        document.getElementById('funcionario-area').textContent = f.area ?? '—';
        document.getElementById('funcionario-cargo').textContent = f.cargo ?? '—';
        document.getElementById('funcionario-correo').textContent = f.correo ?? '—';
        document.getElementById('funcionario-rut').textContent = f.rut ?? '—';

        var btnEditar = document.getElementById('btn-editar-funcionario');
        console.log('btn-editar:', btnEditar, 'href:', '/funcionarios/' + f.id + '/edit');
        if (btnEditar) {
            btnEditar.href = '/funcionarios/' + f.id + '/edit';
            btnEditar.classList.remove('d-none');
        }

        // Avatar
        const wrapper = document.getElementById('funcionario-avatar-wrapper');
        wrapper.innerHTML = f.avatar
            ? `<img src="${f.avatar}" class="rounded-circle avatar-lg" alt="${f.nombre}">`
            : `<div class="rounded-circle bg-secondary-subtle d-flex align-items-center justify-content-center avatar-lg"><i class="fas fa-user fa-2x text-secondary"></i></div>`;

        cardFuncionario.classList.remove('d-none');
        cargarEquiposAsignados(f.id);
        actualizarBotonAsignar();
    }

    // ─── Cargar equipos asignados ─────────────────────────────────────────────
    function cargarEquiposAsignados(funcionarioId) {
        const lista = document.getElementById('lista-equipos-asignados');
        const badge = document.getElementById('badge-equipos-asignados');
        lista.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

        fetch(`${routes.equiposFuncionario}/${funcionarioId}/equipos`)
            .then(r => r.json())
            .then(equipos => {
                badge.textContent = equipos.length;

                if (equipos.length === 0) {
                    lista.innerHTML = '<p class="text-secondary text-center small py-3">Sin equipos asignados.</p>';
                    return;
                }

                lista.innerHTML = equipos.map(e => `
                    <div class="card mb-2 shadow-sm">
                        <div class="card-body py-2 px-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-medium small">${e.marca} ${e.modelo}</div>
                                    <div class="text-secondary" style="font-size:.75rem;">
                                        <i class="fas fa-barcode me-1"></i>${e.inventario}
                                        ${e.hostname ? `· <i class="fas fa-computer me-1"></i>${e.hostname}` : ''}
                                    </div>
                                    <span class="badge bg-${e.color}-subtle text-${e.color}-emphasis border border-${e.color}-subtle" style="font-size:.65rem;">${e.estado}</span>
                                </div>
                                <button class="btn btn-sm btn-outline-danger btn-desasignar ms-2"
                                        data-id="${e.detalle_id}"
                                        title="Desasignar">
                                    <i class="fas fa-unlink"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('');

                // Eventos desasignar
                lista.querySelectorAll('.btn-desasignar').forEach(btn => {
                    btn.addEventListener('click', function () {
                        if (!confirm('¿Desasignar este equipo?')) return;
                        desasignar(this.dataset.id);
                    });
                });
            });
    }

    // ─── Desasignar ───────────────────────────────────────────────────────────
    function desasignar(detalleId) {
        fetch(`${routes.desasignar}/${detalleId}/desasignar`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': routes.csrf,
                'Content-Type': 'application/json',
            },
        })
            .then(r => r.json())
            .then(res => {
                if (res.ok) {
                    mostrarToast(res.mensaje, 'success');
                    cargarEquiposAsignados(funcionarioActivo.id);
                    // Recargar página para que el equipo vuelva al grid
                    setTimeout(() => location.reload(), 1200);
                }
            });
    }

    // ─── Selección de equipos ─────────────────────────────────────────────────
    document.querySelectorAll('.equipo-card').forEach(card => {
        card.addEventListener('click', function (e) {
            if (e.target.closest('.btn-historial')) return;
            const id = this.dataset.id;
            if (seleccionados.has(id)) {
                seleccionados.delete(id);
                this.classList.remove('seleccionado');
            } else {
                seleccionados.set(id, {
                    id: id,
                    tipo:       this.dataset.tipo       ?? '',
                    marca: this.dataset.marca ?? '—',
                    modelo: this.dataset.modelo ?? '—',
                    inventario: this.dataset.inventario ?? id,
                });
                this.classList.add('seleccionado');
            }
            
            actualizarBarra();
        });
    });

    function actualizarBarra() {
        const n = seleccionados.size;
        contadorSel.textContent = n;

        if (n > 0) {
            barraAccion.classList.add('visible');
        } else {
            barraAccion.classList.remove('visible');
        }

        actualizarBotonAsignar();
        actualizarPanelSeleccionados();
    }

    function actualizarPanelSeleccionados() {
        const panel = document.getElementById('panel-seleccionados');
        const lista = document.getElementById('lista-seleccionados');
        const contador = document.getElementById('contador-panel');

        if (seleccionados.size === 0) {
            panel.classList.add('d-none');
            return;
        }

        panel.classList.remove('d-none');
        contador.textContent = seleccionados.size;

        lista.innerHTML = '';
        seleccionados.forEach(function (datos, id) {
            const card = document.querySelector(`.equipo-card[data-id="${id}"]`);

            var marca = card ? card.dataset.marca : '—';
            var modelo = card ? card.dataset.modelo : '—';
            var inventario = card ? card.dataset.inventario : id;

            const item = document.createElement('div');
            item.className = 'd-flex justify-content-between align-items-center p-1 border-bottom';
            item.innerHTML = `
        <div>
            <div class="small fw-medium">${datos.marca} ${datos.modelo}</div>
            <div class="small text-secondary">${datos.inventario}</div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-danger btn-quitar-sel" data-id="${id}">
            <i class="fas fa-xmark"></i>
        </button>
    `;
 
            lista.appendChild(item);
        });

        // Botones quitar
        lista.querySelectorAll('.btn-quitar-sel').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                seleccionados.delete(id);
                const card = document.querySelector(`.equipo-card[data-id="${id}"]`);
                if (card) card.classList.remove('seleccionado');
                actualizarBarra();
            });
        });
    }

    function actualizarBotonAsignar() {
        btnAbrirModal.disabled = !(funcionarioActivo && seleccionados.size > 0);
    }

    // Limpiar selección
    btnLimpiar?.addEventListener('click', function () {
        seleccionados.clear();
        document.querySelectorAll('.equipo-card.seleccionado').forEach(c => c.classList.remove('seleccionado'));
        actualizarBarra();
    });

    btnAbrirModal?.addEventListener('click', function () {
        if (!funcionarioActivo) return;
        document.getElementById('modal-cantidad').textContent = seleccionados.size;
        document.getElementById('modal-funcionario-nombre').textContent = funcionarioActivo.nombre;
        document.getElementById('modal-observacion').value = '';

        const tiposConHostname = ['Computador', 'Notebook'];
        const tbody = document.getElementById('modal-equipos-body');
        tbody.innerHTML = '';

        seleccionados.forEach(function (datos, id) {
            console.log('tipo:', JSON.stringify(datos.tipo));
            const hostnameCell = tiposConHostname.includes(datos.tipo)
                ? `<input type="text" class="form-control form-control-sm modal-hostname-input"
                      data-equipo="${id}" placeholder="PC-NOMBRE-01">`
                : `<span class="text-secondary small">No aplica</span>`;

            const row = document.createElement('tr');
            row.innerHTML = `
            <td class="small">${datos.marca} ${datos.modelo}</td>
            <td class="small fw-medium">${datos.inventario}</td>
            <td>${hostnameCell}</td>
        `;
            tbody.appendChild(row);
        });

        modalAsignar.show();
    });

    btnConfirmar?.addEventListener('click', function () {
        if (!funcionarioActivo || seleccionados.size === 0) return;

        btnConfirmar.disabled = true;
        btnConfirmar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Asignando...';

        // Recolectar hostnames por equipo
        const hostnames = {};
        document.querySelectorAll('.modal-hostname-input').forEach(input => {
            if (input.value.trim() !== '') {
                hostnames[input.dataset.equipo] = input.value.trim();
            }
        });

        fetch(routes.asignar, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': routes.csrf,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                funcionario_id: funcionarioActivo.id,
                equipos: Array.from(seleccionados.keys()),
                hostnames: hostnames,
                observacion: document.getElementById('modal-observacion').value,
            }),
        })
            .then(r => r.json())
            .then(res => {
                modalAsignar.hide();
                if (res.ok) {
                    mostrarToast(res.mensaje, 'success');
                    seleccionados.clear();
                    actualizarBarra();
                    cargarEquiposAsignados(funcionarioActivo.id);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    mostrarToast(res.mensaje ?? 'Error al asignar.', 'danger');
                }
            })
            .finally(() => {
                btnConfirmar.disabled = false;
                btnConfirmar.innerHTML = '<i class="fas fa-link me-1"></i>Confirmar asignación';
            });
    });

    // ─── Modal historial ──────────────────────────────────────────────────────
    document.querySelectorAll('.btn-historial').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const id = this.dataset.id;
            abrirHistorial(id);
        });
    });

    function abrirHistorial(detalleId) {
        // Reset tabs
        activarTabHistorial('detalle');
        document.getElementById('detalle-equipo-content').innerHTML =
            '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
        document.getElementById('historial-content').innerHTML =
            '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';

        modalHistorial.show();

        fetch(`${routes.historialEquipo}/${detalleId}/historial`)
            .then(r => r.json())
            .then(data => {
                document.getElementById('modal-historial-titulo').textContent =
                    `${data.detalle.marca} ${data.detalle.modelo} — ${data.detalle.inventario}`;

                // Tab detalle
                document.getElementById('detalle-equipo-content').innerHTML = `
                    <div class="col-md-4 text-center mb-3">
                        ${data.detalle.imagen
                        ? `<img src="${data.detalle.imagen}" class="img-fluid rounded shadow-sm" style="max-height:160px;object-fit:contain;">`
                        : `<div class="bg-secondary-subtle rounded d-flex align-items-center justify-content-center" style="height:120px;"><i class="fas fa-desktop fa-2x text-secondary"></i></div>`
                    }
                    </div>
                    <div class="col-md-8">
                        <ul class="list-unstyled small">
                            <li class="mb-1"><span class="text-secondary">Tipo:</span> <strong>${data.detalle.tipo}</strong></li>
                            <li class="mb-1"><span class="text-secondary">Marca/Modelo:</span> <strong>${data.detalle.marca} ${data.detalle.modelo}</strong></li>
                            <li class="mb-1"><span class="text-secondary">Inventario:</span> <strong>${data.detalle.inventario}</strong></li>
                            <li class="mb-1"><span class="text-secondary">Serial:</span> <strong>${data.detalle.serial}</strong></li>
                            <li class="mb-1"><span class="text-secondary">Estado:</span> <span class="badge bg-${data.detalle.color}-subtle text-${data.detalle.color}-emphasis border border-${data.detalle.color}-subtle">${data.detalle.estado}</span></li>
                            <li class="mb-1"><span class="text-secondary">RAM:</span> ${data.detalle.ram}</li>
                            <li class="mb-1"><span class="text-secondary">Disco:</span> ${data.detalle.disco}</li>
                            <li class="mb-1"><span class="text-secondary">IP:</span> ${data.detalle.ip}</li>
                            <li class="mb-1"><span class="text-secondary">MAC Ethernet:</span> ${data.detalle.mac_ethernet}</li>
                            <li class="mb-1"><span class="text-secondary">MAC WiFi:</span> ${data.detalle.mac_wifi}</li>
                            <li class="mb-1"><span class="text-secondary">Teletrabajo:</span> ${data.detalle.teletrabajo}</li>
                            <li class="mb-1"><span class="text-secondary">Préstamo:</span> ${data.detalle.es_prestamo}</li>
                            <li><span class="text-secondary">Adquisición:</span> ${data.detalle.fecha_adq}</li>
                        </ul>
                    </div>
                `;

                // Tab historial
                if (data.historial.length === 0) {
                    document.getElementById('historial-content').innerHTML =
                        '<p class="text-secondary text-center py-3">Sin historial de asignaciones.</p>';
                } else {
                    document.getElementById('historial-content').innerHTML = `
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Acción</th>
                                        <th>Funcionario</th>
                                        <th>Hostname</th>
                                        <th>Observación</th>
                                        <th>Usuario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.historial.map(h => `
                                        <tr>
                                            <td class="text-secondary small text-nowrap">${h.fecha}</td>
                                            <td><span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">${h.accion}</span></td>
                                            <td>${h.funcionario}</td>
                                            <td class="text-secondary small">${h.hostname ?? '—'}</td>
                                            <td class="text-secondary small">${h.observacion ?? '—'}</td>
                                            <td class="text-secondary small">${h.usuario}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    `;
                }
            });
    }

    // ─── Tabs funcionario ─────────────────────────────────────────────────────
    document.querySelectorAll('[data-tab]').forEach(btn => {
        btn.addEventListener('click', function () {
            const tab = this.dataset.tab;
            document.querySelectorAll('[data-tab]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            document.getElementById('tab-perfil').classList.toggle('d-none', tab !== 'perfil');
            document.getElementById('tab-equipos-asignados').classList.toggle('d-none', tab !== 'equipos-asignados');
        });
    });

    // ─── Tabs historial modal ─────────────────────────────────────────────────
    document.querySelectorAll('[data-tab-historial]').forEach(btn => {
        btn.addEventListener('click', function () {
            activarTabHistorial(this.dataset.tabHistorial);
        });
    });

    function activarTabHistorial(tab) {
        document.querySelectorAll('[data-tab-historial]').forEach(b => b.classList.remove('active'));
        document.querySelector(`[data-tab-historial="${tab}"]`)?.classList.add('active');
        document.getElementById('tab-historial-detalle').classList.toggle('d-none', tab !== 'detalle');
        document.getElementById('tab-historial-historial').classList.toggle('d-none', tab !== 'historial');
    }

    // ─── Toast ────────────────────────────────────────────────────────────────
    function mostrarToast(mensaje, tipo = 'success') {
        const id = 'toast-' + Date.now();
        const html = `
            <div id="${id}" class="toast align-items-center text-bg-${tipo} border-0 show"
                 role="alert" style="min-width:280px;">
                <div class="d-flex">
                    <div class="toast-body">${mensaje}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto"
                            data-bs-dismiss="toast"></button>
                </div>
            </div>`;

        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'position-fixed bottom-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }

        container.insertAdjacentHTML('beforeend', html);
        setTimeout(() => document.getElementById(id)?.remove(), 4000);
    }

    // ─── Buscador equipos con debounce ────────────────────────────────────────
    // ─── Buscador equipos con AJAX ────────────────────────────────────────────
    const buscarEquipos = document.getElementById('buscar');
    const formFiltros = document.getElementById('form-filtros');
    let timeoutEquipos = null;

    buscarEquipos?.addEventListener('input', function () {
        clearTimeout(timeoutEquipos);
        var q = buscarEquipos.value;
        timeoutEquipos = setTimeout(function () {
            var url = new URL(window.location.href);
            url.searchParams.set('buscar', q);

            fetch(url.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (r) { return r.text(); })
                .then(function (html) {
                    var parser = new DOMParser();
                    var doc = parser.parseFromString(html, 'text/html');
                    var grid = document.getElementById('grid-equipos');
                    var newGrid = doc.getElementById('grid-equipos');
                    var pag = document.getElementById('paginacion');
                    var newPag = doc.getElementById('paginacion');

                    if (grid && newGrid) {
                        grid.innerHTML = newGrid.innerHTML;
                        // Re-inicializar eventos en las nuevas cards
                        grid.querySelectorAll('.equipo-card').forEach(card => {
                            card.addEventListener('click', function (e) {
                                if (e.target.closest('.btn-historial')) return;
                                const id = this.dataset.id;
                                // Click card
                                if (seleccionados.has(id)) {
                                    seleccionados.delete(id);
                                    card.classList.remove('seleccionado');
                                } else {
                                    seleccionados.set(id, {
                                        id: id,
                                        tipo: card.dataset.tipo ?? '',
                                        marca: card.dataset.marca ?? '—',
                                        modelo: card.dataset.modelo ?? '—',
                                        inventario: card.dataset.inventario ?? id,
                                    });
                                    console.log('guardando:', id, 'tipo:', card.dataset.tipo, 'dataset completo:', card.dataset);
                                    card.classList.add('seleccionado');
                                }
                                actualizarBarra();
                            });
                        });
                        // Restaurar selección visual
                        seleccionados.forEach(function (datos, id) {
                            const card = grid.querySelector(`[data-id="${id}"]`);
                            if (card) card.classList.add('seleccionado');
                        });
                        iniciarZoom();
                    }
                    if (pag && newPag) pag.innerHTML = newPag.innerHTML;
                });
        }, 350);
    });

    formFiltros?.querySelectorAll('select').forEach(el => {
        el.addEventListener('change', () => formFiltros.submit());
    });


    // ─── Zoom de imágenes ─────────────────────────────────────────────────────
    function iniciarZoom() {
        document.querySelectorAll('.card-img-wrapper').forEach(function (wrapper) {
            if (wrapper.dataset.zoomInit) return;
            wrapper.dataset.zoomInit = '1';

            var timerZoom = null;
            var timerSalida = null;

            wrapper.addEventListener('mouseenter', function () {
                clearTimeout(timerSalida);
                timerZoom = setTimeout(function () {
                    wrapper.classList.add('zoom-activo');
                }, 1200);
            });

            wrapper.addEventListener('mouseleave', function () {
                clearTimeout(timerZoom);
                timerSalida = setTimeout(function () {
                    wrapper.classList.remove('zoom-activo');
                }, 200);
            });
        });
    }

    iniciarZoom();


    // ── Registrar equipo ──────────────────────────────────────────────────────
    var regTipoEquipo = document.getElementById('reg-tipo-equipo');
    var regEquipoId = document.getElementById('reg-equipo-id');

    regTipoEquipo?.addEventListener('change', function () {
        var tipoId = this.value;
        regEquipoId.innerHTML = '<option value="">Cargando...</option>';
        if (!tipoId) {
            regEquipoId.innerHTML = '<option value="">Seleccione tipo primero</option>';
            return;
        }
        fetch('/interno/equipos-por-tipo?tipo_id=' + tipoId)
            .then(function (r) { return r.json(); })
            .then(function (equipos) {
                regEquipoId.innerHTML = '<option value="">Seleccione modelo</option>';
                equipos.forEach(function (e) {
                    var opt = document.createElement('option');
                    opt.value = e.id;
                    opt.textContent = e.marca + ' ' + e.modelo;
                    regEquipoId.appendChild(opt);
                });
            });
    });

    document.getElementById('btn-guardar-equipo')?.addEventListener('click', function () {
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';

        var datos = {
            equipo_id: document.getElementById('reg-equipo-id').value,
            inventario: document.getElementById('reg-inventario').value,
            serial: document.getElementById('reg-serial').value,
            estado_id: document.getElementById('reg-estado').value,
            ram_gb: document.getElementById('reg-ram').value,
            tipo_disco_id: document.getElementById('reg-tipo-disco').value,
            capacidad_disco_gb: document.getElementById('reg-disco-gb').value,
            fecha_adquisicion: document.getElementById('reg-fecha').value,
            ip: document.getElementById('reg-ip').value,
            puerto_switch: document.getElementById('reg-puerto').value,
            mac_ethernet: document.getElementById('reg-mac-eth').value,
            mac_wifi: document.getElementById('reg-mac-wifi').value,
            teletrabajo: document.getElementById('reg-teletrabajo').checked ? 1 : 0,
            es_prestamo: document.getElementById('reg-prestamo').checked ? 1 : 0,
        };

        fetch('/inventario/ajax-store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': routes.csrf,
                'Accept': 'application/json',
            },
            body: JSON.stringify(datos),
        })
            .then(function (r) {
                if (!r.ok) {
                    return r.json().then(function (err) { throw err; });
                }
                return r.json();
            })
          .then(function(res) {
    var alerta = document.getElementById('alerta-equipo');
    alerta.className = 'alert alert-success';
    alerta.innerHTML = '<i class="fas fa-check me-1"></i>' + res.mensaje;
    alerta.classList.remove('d-none');
    limpiarFormularioEquipo();
})
            .catch(function (err) {
                var alerta = document.getElementById('alerta-equipo');
                alerta.className = 'alert alert-danger';
                alerta.classList.remove('d-none');

                if (err && err.errors) {
                    var msgs = Object.values(err.errors).flat().join('<br>');
                    alerta.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>' + msgs;
                } else if (err && err.message) {
                    alerta.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>' + err.message;
                } else {
                    alerta.innerHTML = 'Error de conexión.';
                }
            })
            .finally(function () {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save me-1"></i>Guardar equipo';
            });
    });

    function limpiarFormularioEquipo() {
    ['reg-inventario','reg-serial','reg-ram','reg-disco-gb',
     'reg-ip','reg-puerto','reg-mac-eth','reg-mac-wifi'].forEach(function(id) {
        document.getElementById(id).value = '';
    });
    document.getElementById('reg-tipo-equipo').value   = '';
    document.getElementById('reg-equipo-id').innerHTML = '<option value="">Seleccione tipo primero</option>';
    document.getElementById('reg-estado').selectedIndex = 0;
    document.getElementById('reg-tipo-disco').value    = '';
    document.getElementById('reg-teletrabajo').checked = false;
    document.getElementById('reg-prestamo').checked    = false;
}

    btnAbrirModal?.addEventListener('click', function () {
        if (!funcionarioActivo) {
            alert('Debes seleccionar un funcionario.');
            return;
        }
        if (seleccionados.size === 0) {
            alert('Debes seleccionar al menos un equipo.');
            return;
        }

        // Verificar si hay equipos ya asignados
        var equiposAsignados = [];
        seleccionados.forEach(function (id) {
            var card = document.querySelector('.equipo-card[data-id="' + id + '"]');
            if (card && card.dataset.asignado === '1') {
                equiposAsignados.push({
                    id: id,
                    funcionario: card.dataset.funcionario,
                    inventario: card.querySelector('.fa-barcode')?.parentElement?.textContent.trim() ?? id,
                });
            }
        });

        if (equiposAsignados.length > 0) {
            // Mostrar modal de confirmación de transferencia
            var lista = equiposAsignados.map(function (e) {
                return '<li><strong>' + e.inventario + '</strong> — actualmente asignado a <strong>' + e.funcionario + '</strong></li>';
            }).join('');

            var confirmacion = confirm(
                'Los siguientes equipos ya están asignados:\n\n' +
                equiposAsignados.map(e => e.inventario + ' → ' + e.funcionario).join('\n') +
                '\n\n¿Deseas transferirlos a ' + funcionarioActivo.nombre + '?'
            );

            if (!confirmacion) return;
        }

        // Continuar con el modal de hostname normal
        modalAsignar.show();
        // ... resto igual
    });


});