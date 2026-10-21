document.addEventListener('DOMContentLoaded', function () {

    const img = document.getElementById('imagenPiso');
    const canvas = document.getElementById('canvasPuestos');
    const ctx = canvas.getContext('2d');
    const modalInfo = new bootstrap.Modal(document.getElementById('modal-puesto-info'));
    // Copia mutable de los datos del servidor
const datos = puestosData.map(p => ({...p}));
    let modoEdicion = false;
    let drawing = false;
    let startX = 0, startY = 0;
    let rectTemporal = null;
    let puestoActivo = null;
    let puestoEditandoId = null;
    let funcionarioSelId = null;
    let puestos = [...puestosData];
    let puestosFiltrados = [...puestosData];
    let paginaActual = 1;
    const porPagina = 15;


    // Recordar modo edición después de recargar
const MODO_KEY = 'piso_' + window.location.pathname.split('/').pop() + '_modoEdicion';

if (localStorage.getItem(MODO_KEY) === '1') {
    modoEdicion = true;
    canvas.classList.add('modo-edicion');
    document.getElementById('panel-nuevo-puesto').classList.remove('d-none');
    const btn = document.getElementById('btn-modo-edicion');
    if (btn) {
        btn.innerHTML = '<i class="fas fa-eye me-1"></i>Vista normal';
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-outline-warning');
    }
    renderTabla(); // para mostrar botones de edición en tabla
}

    // ── Helpers ───────────────────────────────────────────────────────────────
    function esc(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function toast(msg, tipo = 'success') {
        const id = 'toast-' + Date.now();
        let c = document.getElementById('toast-container');
        if (!c) {
            c = document.createElement('div');
            c.id = 'toast-container';
            c.className = 'position-fixed bottom-0 end-0 p-3';
            c.style.zIndex = '9999';
            document.body.appendChild(c);
        }
        c.insertAdjacentHTML('beforeend', `
            <div id="${id}" class="toast align-items-center text-bg-${tipo} border-0 show" role="alert" style="min-width:260px;">
                <div class="d-flex">
                    <div class="toast-body">${msg}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>`);
        setTimeout(() => document.getElementById(id)?.remove(), 4000);
    }

    // ── Canvas ────────────────────────────────────────────────────────────────
    function ajustarCanvas() {
        canvas.width = img.clientWidth;
        canvas.height = img.clientHeight;
    }

    function colorPuesto(p) {
        if (!p.activo) return { fill: 'rgba(220,53,69,.25)', stroke: '#dc3545', text: '#dc3545' };
        if (p.ocupado) return { fill: 'rgba(37,99,235,.25)', stroke: '#2563eb', text: '#1d4ed8' };
        return { fill: 'rgba(108,117,125,.2)', stroke: '#6c757d', text: '#495057' };
    }

    function dibujarPuestos() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        puestosData.forEach(p => {
            if (p.x1 === null) return;
            const { fill, stroke, text } = colorPuesto(p);

            ctx.fillStyle = fill;
            ctx.strokeStyle = stroke;
            ctx.lineWidth = 2;
            ctx.fillRect(p.x1, p.y1, p.x2 - p.x1, p.y2 - p.y1);
            ctx.strokeRect(p.x1, p.y1, p.x2 - p.x1, p.y2 - p.y1);

            ctx.font = 'bold 11px Arial';
            ctx.fillStyle = text;
            ctx.textBaseline = 'top';
            ctx.fillText(p.numero, p.x1 + 4, p.y1 + 4);
        });

        if (rectTemporal) {
            ctx.strokeStyle = 'blue';
            ctx.lineWidth = 2;
            ctx.strokeRect(
                rectTemporal.x1, rectTemporal.y1,
                rectTemporal.x2 - rectTemporal.x1,
                rectTemporal.y2 - rectTemporal.y1
            );
        }
    }

    function colisiona(x1, y1, x2, y2) {
        return puestosData.some(p =>
            p.x1 !== null && !(x2 < p.x1 || x1 > p.x2 || y2 < p.y1 || y1 > p.y2)
        );
    }

    function puestoEnPunto(x, y) {
        return puestosData.find(p =>
            p.x1 !== null && x >= p.x1 && x <= p.x2 && y >= p.y1 && y <= p.y2
        );
    }

    img.addEventListener('load', () => { ajustarCanvas(); dibujarPuestos(); });
    window.addEventListener('resize', () => { ajustarCanvas(); dibujarPuestos(); });
    ajustarCanvas();
    dibujarPuestos();

    // ── Modo edición ──────────────────────────────────────────────────────────
    document.getElementById('btn-modo-edicion')?.addEventListener('click', function () {
        modoEdicion = !modoEdicion;
         localStorage.setItem(MODO_KEY, modoEdicion ? '1' : '0');
        canvas.classList.toggle('modo-edicion', modoEdicion);
        document.getElementById('panel-nuevo-puesto').classList.toggle('d-none', !modoEdicion);

        this.innerHTML = modoEdicion
            ? '<i class="fas fa-eye me-1"></i>Vista normal'
            : '<i class="fas fa-pen me-1"></i>Modo edición';
        this.classList.toggle('btn-outline-warning', modoEdicion);
        this.classList.toggle('btn-outline-primary', !modoEdicion);

        if (!modoEdicion) {
            salirModoEdicion();
            rectTemporal = null;
            dibujarPuestos();
        }

        renderTabla();
    });

    // ── Eventos canvas ────────────────────────────────────────────────────────
    canvas.addEventListener('mousedown', e => {
        const r = canvas.getBoundingClientRect();
        const x = e.clientX - r.left;
        const y = e.clientY - r.top;

        if (modoEdicion) {
            const p = puestoEnPunto(x, y);
            if (p) { cargarEdicion(p); return; }
            drawing = true;
            startX = x;
            startY = y;
        } else {
            const p = puestoEnPunto(x, y);
            if (p) abrirInfoPuesto(p.id);
        }
    });

    canvas.addEventListener('mousemove', e => {
        if (!drawing) return;
        const r = canvas.getBoundingClientRect();
        const x = e.clientX - r.left;
        const y = e.clientY - r.top;
        rectTemporal = {
            x1: Math.min(startX, x), y1: Math.min(startY, y),
            x2: Math.max(startX, x), y2: Math.max(startY, y),
        };
        dibujarPuestos();
    });

    canvas.addEventListener('mouseup', e => {
        if (!drawing) return;
        drawing = false;
        e.stopPropagation();
        const r = canvas.getBoundingClientRect();
        const endX = e.clientX - r.left;
        const endY = e.clientY - r.top;
        const x1 = Math.round(Math.min(startX, endX));
        const y1 = Math.round(Math.min(startY, endY));
        const x2 = Math.round(Math.max(startX, endX));
        const y2 = Math.round(Math.max(startY, endY));
        rectTemporal = null;

        if (Math.abs(x2 - x1) < 10 || Math.abs(y2 - y1) < 10) { dibujarPuestos(); return; }

        if (colisiona(x1, y1, x2, y2)) {
            toast('Área ocupada por otro puesto.', 'danger');
            dibujarPuestos();
            return;
        }

        const btn = document.getElementById('btn-guardar-puesto');
        btn.dataset.x1 = x1; btn.dataset.y1 = y1;
        btn.dataset.x2 = x2; btn.dataset.y2 = y2;
        btn.disabled = false;

        ctx.strokeStyle = 'blue';
        ctx.lineWidth = 2;
        ctx.strokeRect(x1, y1, x2 - x1, y2 - y1);
        toast('Área dibujada. Completa los datos y guarda.', 'primary');
    });

    // ── Guardar / actualizar puesto ───────────────────────────────────────────
    document.getElementById('btn-guardar-puesto')?.addEventListener('click', function () {
        const numero = document.getElementById('input-numero').value.trim();
        if (!numero) { toast('El número de puesto es obligatorio.', 'warning'); return; }

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';

        const x1 = parseInt(btn.dataset.x1);
        const y1 = parseInt(btn.dataset.y1);
        const x2 = parseInt(btn.dataset.x2);
        const y2 = parseInt(btn.dataset.y2);

        const body = {
            numero,
            seccion: document.getElementById('input-seccion').value.trim(),
            observacion: document.getElementById('input-observacion').value.trim(),
            x1, y1, x2, y2,
        };

        const url = puestoEditandoId ? `${routes.updatePuesto}/${puestoEditandoId}` : routes.storePuesto;
        const method = puestoEditandoId ? 'PUT' : 'POST';
        const esEdicion = !!puestoEditandoId;

        fetch(url, {
            method,
            headers: { 'X-CSRF-TOKEN': routes.csrf, 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        })
            .then(r => r.json())
            .then(res => {
                if (!res.ok) { toast(res.mensaje ?? 'Error al guardar.', 'danger'); return; }
                toast(res.mensaje, 'success');

                if (esEdicion) {
                    const idx = puestosData.findIndex(p => p.id === puestoEditandoId);
                    if (idx !== -1) {
                        puestosData[idx] = { ...puestosData[idx], numero, seccion: body.seccion, x1, y1, x2, y2 };
                        puestos[idx] = { ...puestosData[idx] };
                        puestosFiltrados = [...puestos];
                    }
                } else {
                    const nuevo = { id: res.id, numero, seccion: body.seccion, observacion: body.observacion, x1, y1, x2, y2, ocupado: false, activo: true, funcionario: null };
                    puestosData.push(nuevo);
                    puestos.push(nuevo);
                    puestosFiltrados.push(nuevo);
                }

                dibujarPuestos();
                renderTabla();
                salirModoEdicion();
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save me-1"></i>Guardar';
            });
    });

    // ── Editar puesto ─────────────────────────────────────────────────────────
    function cargarEdicion(p) {
        puestoEditandoId = p.id;
        document.getElementById('titulo-form').textContent = `Editando puesto ${p.numero}`;
        document.getElementById('input-numero').value = p.numero;
        document.getElementById('input-seccion').value = p.seccion ?? '';
        document.getElementById('input-observacion').value = p.observacion ?? '';

        const btn = document.getElementById('btn-guardar-puesto');
        btn.dataset.x1 = p.x1; btn.dataset.y1 = p.y1;
        btn.dataset.x2 = p.x2; btn.dataset.y2 = p.y2;
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-1"></i>Actualizar';

        const btnToggle = document.getElementById('btn-toggle-inline');
        if (btnToggle) {
            btnToggle.classList.remove('d-none');
            btnToggle.innerHTML = p.activo ? '<i class="fas fa-ban"></i>' : '<i class="fas fa-check"></i>';
            btnToggle.title = p.activo ? 'Deshabilitar puesto' : 'Habilitar puesto';
            btnToggle.className = p.activo ? 'btn btn-outline-warning btn-sm' : 'btn btn-outline-success btn-sm';
        }

        document.getElementById('btn-eliminar-inline')?.classList.remove('d-none');
        toast(`Editando puesto ${p.numero}. Puedes redibujar el área o solo editar los datos.`, 'primary');
    }

    function salirModoEdicion() {
        puestoEditandoId = null;
        document.getElementById('titulo-form').textContent = 'Nuevo Puesto';
        document.getElementById('input-numero').value = '';
        document.getElementById('input-seccion').value = '';
        document.getElementById('input-observacion').value = '';

        const btn = document.getElementById('btn-guardar-puesto');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-save me-1"></i>Guardar';

        document.getElementById('btn-toggle-inline')?.classList.add('d-none');
        document.getElementById('btn-eliminar-inline')?.classList.add('d-none');
    }

    document.getElementById('btn-cancelar-edicion')?.addEventListener('click', () => {
        salirModoEdicion();
        rectTemporal = null;
        dibujarPuestos();
    });

    // ── Toggle activo ─────────────────────────────────────────────────────────
    document.getElementById('btn-toggle-inline')?.addEventListener('click', function () {
        if (!puestoEditandoId) return;
        fetch(`${routes.togglePuesto}/${puestoEditandoId}/toggle`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': routes.csrf },
        })
            .then(r => r.json())
            .then(res => {
    if (res.ok) {
        toast(res.mensaje, res.activo ? 'success' : 'warning');
        setTimeout(() => location.reload(), 800);
    }
})
            // .then(res => {
            //     if (res.ok) {
            //         toast(res.mensaje, res.activo ? 'success' : 'warning');
            //         const p = puestosData.find(p => p.id === puestoEditandoId);
            //         if (p) p.activo = res.activo;
            //         sincronizarArrays();
            //         dibujarPuestos();
            //         renderTabla();
            //     }
            // });
    });

    // ── Eliminar puesto ───────────────────────────────────────────────────────
    function eliminarPuesto(id) {
        if (!confirm('¿Eliminar este puesto?')) return;
        fetch(`${routes.destroyPuesto}/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': routes.csrf },
        })
            .then(r => {
                if (!r.ok) { r.text().then(t => console.error('Error:', t)); throw new Error('Error del servidor'); }
                return r.json();
            })
            .then(res => {
                if (!res.ok) { toast('Error al eliminar.', 'danger'); return; }
                toast(res.mensaje, 'success');

                const idx = puestosData.findIndex(p => p.id === id);
                if (idx !== -1) puestosData.splice(idx, 1);
                sincronizarArrays();
                dibujarPuestos();
                renderTabla();
            })
            .catch(err => toast(err.message, 'danger'));
    }

    document.getElementById('btn-eliminar-inline')?.addEventListener('click', function () {
        if (!puestoEditandoId) return;
        eliminarPuesto(puestoEditandoId);
    });

    // ── Tabla de puestos ──────────────────────────────────────────────────────
    function renderTabla() {
        const tbody = document.querySelector('#tabla-puestos tbody');
        const inicio = (paginaActual - 1) * porPagina;
        const pagina = puestosFiltrados.slice(inicio, inicio + porPagina);
        const total = Math.max(1, Math.ceil(puestosFiltrados.length / porPagina));

        tbody.innerHTML = pagina.map((p, i) => {
            const estado = !p.activo
                ? '<span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">Deshabilitado</span>'
                : p.ocupado
                    ? '<span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">Ocupado</span>'
                    : '<span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">Disponible</span>';

            return `
                <tr style="cursor:pointer;" data-id="${p.id}">
                    <td>${inicio + i + 1}</td>
                    <td>${esc(p.numero)}</td>
                    <td>${esc(p.seccion) || '—'}</td>
                    <td>${estado}</td>
                </tr>
            `;
        }).join('');

        document.getElementById('page-info').textContent = `Página ${paginaActual} de ${total}`;
        document.getElementById('btn-prev').disabled = paginaActual === 1;
        document.getElementById('btn-next').disabled = paginaActual === total;

        tbody.querySelectorAll('tr[data-id]').forEach(fila => {
            fila.addEventListener('click', function (e) {
                if (e.target.closest('.btn-del-tabla')) return;
                const id = parseInt(this.dataset.id);
                const p = puestosData.find(p => p.id === id);
                if (!p) return;

                if (modoEdicion) {
                    cargarEdicion(p);
                } else {
                    abrirInfoPuesto(id);
                }

                dibujarPuestos();
                if (p.x1 !== null) {
                    ctx.strokeStyle = '#f59e0b';
                    ctx.lineWidth = 3;
                    ctx.strokeRect(p.x1, p.y1, p.x2 - p.x1, p.y2 - p.y1);
                }
            });
        });
    }

    document.getElementById('btn-prev')?.addEventListener('click', () => { paginaActual--; renderTabla(); });
    document.getElementById('btn-next')?.addEventListener('click', () => { paginaActual++; renderTabla(); });

    document.getElementById('buscador-puestos')?.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        puestosFiltrados = puestos.filter(p =>
            String(p.numero).toLowerCase().includes(q) ||
            String(p.seccion ?? '').toLowerCase().includes(q)
        );
        paginaActual = 1;
        renderTabla();
    });

    renderTabla();

    // ── Info puesto (vista normal) ────────────────────────────────────────────
    function abrirInfoPuesto(id) {
        puestoActivo = id;
        funcionarioSelId = null;

        fetch(`${routes.historialPuesto}/${id}/historial`)
            .then(r => r.json())
            .then(data => {
                const p = data.puesto;
                document.getElementById('info-numero').textContent = p.numero;
                activarTabPuesto('info');

                if (!p.activo) {
                    // Deshabilitado
                    document.getElementById('info-funcionario-card').classList.add('d-none');
                    document.getElementById('info-disponible').classList.remove('d-none');
                    document.getElementById('aviso-deshabilitado')?.classList.remove('d-none');
                    document.getElementById('buscar-funcionario-puesto').disabled = true;
                    document.getElementById('buscar-funcionario-puesto').value = '';
                    document.getElementById('buscar-funcionario-puesto').placeholder = 'Puesto deshabilitado';
                    document.getElementById('btn-asignar-puesto').disabled = true;
                    document.getElementById('observacion-asignacion').disabled = true;
                    document.getElementById('observacion-asignacion').value = '';
                    document.getElementById('funcionario-seleccionado-puesto').classList.add('d-none');

                } else if (p.ocupado && p.funcionario) {
                    // Ocupado
                    document.getElementById('info-funcionario-card').classList.remove('d-none');
                    document.getElementById('info-disponible').classList.add('d-none');
                    document.getElementById('aviso-deshabilitado')?.classList.add('d-none');

                    const f = p.funcionario;
                    document.getElementById('info-funcionario-nombre').textContent = f.nombre;
                    document.getElementById('info-funcionario-rut').textContent = f.rut ?? '';
                    document.getElementById('info-funcionario-cargo').textContent = f.cargo ?? '—';
                    document.getElementById('info-funcionario-area').textContent = f.area ?? '—';

                    const av = document.getElementById('info-avatar');
                    av.innerHTML = f.avatar
                        ? `<img src="${f.avatar}" class="rounded-circle" width="48" height="48" style="object-fit:cover;">`
                        : `<div class="rounded-circle bg-secondary-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;"><i class="fas fa-user text-secondary"></i></div>`;

                } else {
                    // Disponible
                    document.getElementById('info-funcionario-card').classList.add('d-none');
                    document.getElementById('info-disponible').classList.remove('d-none');
                    document.getElementById('aviso-deshabilitado')?.classList.add('d-none');
                    document.getElementById('buscar-funcionario-puesto').disabled = false;
                    document.getElementById('buscar-funcionario-puesto').placeholder = 'Buscar funcionario...';
                    document.getElementById('buscar-funcionario-puesto').value = '';
                    document.getElementById('resultados-funcionario-puesto').classList.add('d-none');
                    document.getElementById('funcionario-seleccionado-puesto').classList.add('d-none');
                    document.getElementById('btn-asignar-puesto').disabled = false;
                    document.getElementById('observacion-asignacion').disabled = false;
                    document.getElementById('observacion-asignacion').value = '';
                    funcionarioSelId = null;
                }

                renderizarHistorial(data.historial);
                modalInfo.show();
            })
            .catch(err => {
                console.error('Error:', err);
                toast('Error al cargar el puesto.', 'danger');
            });
    }

    function renderizarHistorial(historial) {
        const cont = document.getElementById('historial-puesto-content');
        if (historial.length === 0) {
            cont.innerHTML = '<p class="text-secondary text-center py-3">Sin historial.</p>';
            return;
        }
        cont.innerHTML = `
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Fecha</th><th>Acción</th><th>Funcionario</th><th>Obs.</th></tr>
                    </thead>
                    <tbody>
                        ${historial.map(h => `
                            <tr>
                                <td class="small text-nowrap">${h.fecha}</td>
                                <td><span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">${esc(h.accion)}</span></td>
                                <td class="small">${esc(h.funcionario)}</td>
                                <td class="small text-secondary">${esc(h.observacion) || '—'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    // ── Tabs ──────────────────────────────────────────────────────────────────
    document.querySelectorAll('[data-tab-puesto]').forEach(btn => {
        btn.addEventListener('click', function () { activarTabPuesto(this.dataset.tabPuesto); });
    });

    function activarTabPuesto(tab) {
        document.querySelectorAll('[data-tab-puesto]').forEach(b => b.classList.remove('active'));
        document.querySelector(`[data-tab-puesto="${tab}"]`)?.classList.add('active');
        document.getElementById('tab-puesto-info').classList.toggle('d-none', tab !== 'info');
        document.getElementById('tab-puesto-historial').classList.toggle('d-none', tab !== 'historial');
    }

    // ── Desasignar ────────────────────────────────────────────────────────────
    document.getElementById('btn-desasignar-puesto')?.addEventListener('click', function () {
        if (!confirm('¿Desasignar este puesto?')) return;
        fetch(`${routes.desasignarPuesto}/${puestoActivo}/desasignar`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': routes.csrf },
        })
            .then(r => r.json())
            .then(res => {
    if (res.ok) {
        toast(res.mensaje, 'success');
        modalInfo.hide();
        setTimeout(() => location.reload(), 800);
    }
})
            // .then(res => {
            //     if (res.ok) {
            //         toast(res.mensaje, 'success');
            //         modalInfo.hide();

            //         // Actualizar por id en los tres arrays
            //         const p = puestosData.find(p => p.id === puestoActivo);
            //         if (p) {
            //             p.ocupado = false;
            //             p.funcionario = null;
            //         }
            //         sincronizarArrays();
            //         dibujarPuestos();
            //         renderTabla();
            //     }
            // });
    });

    // ── Buscar funcionario ────────────────────────────────────────────────────
    let timeoutFunc = null;
    document.getElementById('buscar-funcionario-puesto')?.addEventListener('input', function () {
        clearTimeout(timeoutFunc);
        const q = this.value.trim();
        const res = document.getElementById('resultados-funcionario-puesto');
        if (q.length < 2) { res.classList.add('d-none'); return; }

        timeoutFunc = setTimeout(() => {
            fetch(`${routes.buscarFuncionarios}?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(lista => {
                    res.innerHTML = lista.length === 0
                        ? '<div class="list-group-item small text-secondary">Sin resultados</div>'
                        : lista.map(f => `
                            <button type="button" class="list-group-item list-group-item-action"
                                    data-id="${f.id}"
                                    data-nombre="${esc(f.nombre)}"
                                    data-tiene-puesto="${f.tiene_puesto ? '1' : '0'}"
                                    data-puesto-actual="${esc(f.puesto_actual ?? '')}">
                                <div class="fw-medium small">${esc(f.nombre)}</div>
                                <small class="text-secondary">${esc(f.rut) ?? ''} · ${esc(f.cargo) ?? ''}</small>
                                ${f.tiene_puesto
                                ? `<div class="mt-1">
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size:.65rem;">
                                            <i class="fas fa-map-pin me-1"></i>${esc(f.puesto_actual)}
                                        </span>
                                       </div>`
                                : ''
                            }
                            </button>`).join('');

                    res.classList.remove('d-none');

                    res.querySelectorAll('button[data-id]').forEach(btn => {
                        btn.addEventListener('click', function () {
                            const tienePuesto = this.dataset.tienePuesto === '1';
                            const puestoActualNombre = this.dataset.puestoActual;

                            if (tienePuesto) {
                                if (!confirm(`${this.dataset.nombre} ya está en ${puestoActualNombre}.\n¿Deseas cambiarle el puesto?`)) return;
                            }

                            funcionarioSelId = parseInt(this.dataset.id);
                            document.getElementById('funcionario-seleccionado-nombre').textContent = this.dataset.nombre;
                            document.getElementById('funcionario-seleccionado-puesto').classList.remove('d-none');
                            document.getElementById('buscar-funcionario-puesto').classList.add('d-none');
                            res.classList.add('d-none');
                            document.getElementById('btn-asignar-puesto').disabled = false;
                        });
                    });
                });
        }, 300);
    });

    document.getElementById('btn-cambiar-funcionario')?.addEventListener('click', function () {
        funcionarioSelId = null;
        document.getElementById('funcionario-seleccionado-puesto').classList.add('d-none');
        document.getElementById('buscar-funcionario-puesto').classList.remove('d-none');
        document.getElementById('buscar-funcionario-puesto').value = '';
        document.getElementById('btn-asignar-puesto').disabled = true;
    });

    // ── Asignar ───────────────────────────────────────────────────────────────
    document.getElementById('btn-asignar-puesto')?.addEventListener('click', function () {
        if (!funcionarioSelId) return;
        if (!puestoActivo) return;

        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Asignando...';

        fetch(`${routes.asignarPuesto}/${puestoActivo}/asignar`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': routes.csrf, 'Content-Type': 'application/json' },
            body: JSON.stringify({
                funcionario_id: funcionarioSelId,
                observacion: document.getElementById('observacion-asignacion').value,
            }),
        })
            .then(r => r.json())
            .then(res => {
    if (res.ok) {
        toast(res.mensaje, 'success');
        modalInfo.hide();
        setTimeout(() => location.reload(), 800);
    }
})
            // .then(res => {
            //     if (res.ok) {
            //         toast(res.mensaje, 'success');
            //         document.getElementById('observacion-asignacion').value = '';
            //         modalInfo.hide();

            //         // Reset completo del buscador para la próxima vez
            //         funcionarioSelId = null;
            //         document.getElementById('buscar-funcionario-puesto').classList.remove('d-none');
            //         document.getElementById('buscar-funcionario-puesto').value = '';
            //         document.getElementById('buscar-funcionario-puesto').disabled = false;
            //         document.getElementById('buscar-funcionario-puesto').placeholder = 'Buscar funcionario...';
            //         document.getElementById('funcionario-seleccionado-puesto').classList.add('d-none');
            //         document.getElementById('resultados-funcionario-puesto').classList.add('d-none');
            //         document.getElementById('btn-asignar-puesto').disabled = true;

            //         const nombre = document.getElementById('funcionario-seleccionado-nombre').textContent;
            //         const p = puestosData.find(p => p.id === puestoActivo);
            //         if (p) {
            //             p.ocupado = true;
            //             p.funcionario = nombre;
            //         }
            //         sincronizarArrays();
            //         dibujarPuestos();
            //         renderTabla();
            //     }
            // })
            .catch(err => {
                console.error('Error:', err);
                toast('Error al asignar.', 'danger');
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-user-plus me-1"></i>Asignar funcionario';
            });
    });


    function sincronizarArrays() {
        const q = document.getElementById('buscador-puestos')?.value.toLowerCase() ?? '';
        puestos = [...puestosData];
        puestosFiltrados = q
            ? puestosData.filter(p =>
                String(p.numero).toLowerCase().includes(q) ||
                String(p.seccion ?? '').toLowerCase().includes(q)
            )
            : [...puestosData];
    }

}); // fin DOMContentLoaded