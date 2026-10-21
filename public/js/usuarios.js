document.addEventListener('DOMContentLoaded', function () {

    const checkTodos     = document.getElementById('check-todos');
    const checks         = document.querySelectorAll('.check-usuario');
    const panelMasivo    = document.getElementById('panel-masivo');
    const contador       = document.getElementById('contador-seleccionados');
    const btnLimpiar     = document.getElementById('btn-limpiar-sel');
    const inputsMasivo   = document.getElementById('inputs-masivo');
    const inputsCopiar   = document.getElementById('inputs-copiar');
    const masivoCantidad = document.getElementById('masivo-cantidad');
    const copiarCantidad = document.getElementById('copiar-cantidad');

    // ── Selección múltiple ────────────────────────────────────────────────────
    function seleccionados() {
        return Array.from(checks).filter(c => c.checked);
    }

    function actualizarPanel() {
        const sel = seleccionados();
        const n   = sel.length;
        contador.textContent = n;
        panelMasivo.style.setProperty('display', n > 0 ? 'block' : 'none', n > 0 ? '' : 'important');

        // Actualizar inputs ocultos en formularios masivos
        const ids = sel.map(c => `<input type="hidden" name="usuarios[]" value="${c.value}">`).join('');
        inputsMasivo.innerHTML = ids;
        inputsCopiar.innerHTML = ids;

        if (masivoCantidad) masivoCantidad.textContent = n;
        if (copiarCantidad) copiarCantidad.textContent = n;

        if (checkTodos) {
            checkTodos.checked       = n === checks.length && n > 0;
            checkTodos.indeterminate = n > 0 && n < checks.length;
        }
    }

    checkTodos?.addEventListener('change', function () {
        checks.forEach(c => c.checked = this.checked);
        actualizarPanel();
    });

    checks.forEach(c => c.addEventListener('change', actualizarPanel));

    btnLimpiar?.addEventListener('click', function () {
        checks.forEach(c => c.checked = false);
        if (checkTodos) checkTodos.checked = false;
        actualizarPanel();
    });

    // ── Editar usuario ────────────────────────────────────────────────────────
    document.querySelectorAll('.btn-editar').forEach(btn => {
        btn.addEventListener('click', function () {
            const id    = this.dataset.id;
            const roles = JSON.parse(this.dataset.roles);

            document.getElementById('editar-name').value  = this.dataset.name;
            document.getElementById('editar-email').value = this.dataset.email;
            document.getElementById('form-editar').action = `/usuarios/${id}`;

            document.querySelectorAll('.check-rol-editar').forEach(cb => {
                cb.checked = roles.includes(cb.value);
            });

            new bootstrap.Modal(document.getElementById('modal-editar')).show();
        });
    });

    // ── Cambiar contraseña ────────────────────────────────────────────────────
    document.querySelectorAll('.btn-password').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('password-nombre').textContent = this.dataset.name;
            document.getElementById('form-password').action        = `/usuarios/${this.dataset.id}/password`;
            new bootstrap.Modal(document.getElementById('modal-password')).show();
        });
    });
});