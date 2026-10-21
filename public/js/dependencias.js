document.addEventListener('DOMContentLoaded', function () {

    // ── Tabs ──────────────────────────────────────────────────────────────────
    document.querySelectorAll('[data-tab]').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('[data-tab]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            ['divisiones', 'departamentos', 'unidades'].forEach(t => {
                document.getElementById('tab-' + t)?.classList.toggle('d-none', t !== this.dataset.tab);
            });
            history.replaceState(null, '', '#' + this.dataset.tab);
        });
    });

    const hash = window.location.hash.replace('#', '');
    if (hash) document.querySelector(`[data-tab="${hash}"]`)?.click();

    // ── Editar división ───────────────────────────────────────────────────────
    document.querySelectorAll('.btn-editar-division').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('division-nombre').value  = this.dataset.nombre;
            document.getElementById('division-method').value  = 'PUT';
            document.getElementById('form-division').action   = `/divisiones/${this.dataset.id}`;
            document.querySelector('#modal-nueva-division .modal-title').innerHTML =
                '<i class="fas fa-pen me-2"></i>Editar División';
            new bootstrap.Modal(document.getElementById('modal-nueva-division')).show();
        });
    });

    document.querySelector('[data-bs-target="#modal-nueva-division"]')?.addEventListener('click', function () {
        document.getElementById('division-nombre').value  = '';
        document.getElementById('division-method').value  = 'POST';
        document.getElementById('form-division').action   = '/divisiones';
        document.querySelector('#modal-nueva-division .modal-title').innerHTML =
            '<i class="fas fa-building me-2"></i>Nueva División';
    });

    // ── Editar departamento ───────────────────────────────────────────────────
    document.querySelectorAll('.btn-editar-departamento').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('departamento-nombre').value   = this.dataset.nombre;
            document.getElementById('departamento-division').value = this.dataset.division;
            document.getElementById('departamento-method').value   = 'PUT';
            document.getElementById('form-departamento').action    = `/departamentos/${this.dataset.id}`;
            document.querySelector('#modal-nuevo-departamento .modal-title').innerHTML =
                '<i class="fas fa-pen me-2"></i>Editar Departamento';
            new bootstrap.Modal(document.getElementById('modal-nuevo-departamento')).show();
        });
    });

    // ── Editar unidad ─────────────────────────────────────────────────────────
  document.querySelectorAll('.btn-editar-unidad').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('unidad-nombre').value       = this.dataset.nombre;
        document.getElementById('unidad-method').value       = 'PUT';
        document.getElementById('form-unidad').action        = `/unidades/${this.dataset.id}`;
        document.querySelector('#modal-nueva-unidad .modal-title').innerHTML =
            '<i class="fas fa-pen me-2"></i>Editar Unidad';
        const depId = this.dataset.departamento;
        cargarDepartamentos(null, depId);
        new bootstrap.Modal(document.getElementById('modal-nueva-unidad')).show();
    });
});

document.querySelector('[data-bs-target="#modal-nueva-unidad"]')?.addEventListener('click', function () {
    document.getElementById('unidad-nombre').value   = '';
    document.getElementById('unidad-method').value   = 'POST';
    document.getElementById('form-unidad').action    = '/unidades';
    document.getElementById('unidad-division').value = '';
    document.getElementById('unidad-departamento').innerHTML = 
        '<option value="">Primero seleccione división</option>';
    document.querySelector('#modal-nueva-unidad .modal-title').innerHTML =
        '<i class="fas fa-users me-2"></i>Nueva Unidad';
});

    // ── Select en cascada División → Departamento ─────────────────────────────
    document.getElementById('unidad-division')?.addEventListener('change', function () {
        cargarDepartamentos(this.value, null);
    });

    function cargarDepartamentos(divisionId, seleccionarDepId) {
        const select = document.getElementById('unidad-departamento');
        select.innerHTML = '<option value="">Cargando...</option>';

        if (!divisionId && !seleccionarDepId) {
            select.innerHTML = '<option value="">Primero seleccione división</option>';
            return;
        }

        const url = divisionId
            ? `/interno/departamentos-por-division?division_id=${divisionId}`
            : `/interno/departamento/${seleccionarDepId}`;

        fetch(url)
            .then(r => r.json())
            .then(data => {
                select.innerHTML = '<option value="">Seleccione departamento</option>';
                const deps = Array.isArray(data) ? data : [data];
                deps.forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d.id;
                    opt.textContent = d.nombre;
                    if (seleccionarDepId && d.id == seleccionarDepId) opt.selected = true;
                    select.appendChild(opt);
                });
            });
    }


    // Guardar tab activo al enviar formularios
document.querySelectorAll('#form-division, #form-departamento, #form-unidad').forEach(form => {
    form.addEventListener('submit', function () {
        const tabActivo = document.querySelector('[data-tab].active')?.dataset.tab;
        if (tabActivo) {
            this.action = this.action.split('#')[0] + '#' + tabActivo;
        }
    });
});
});