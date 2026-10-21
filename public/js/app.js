document.addEventListener('DOMContentLoaded', function () {
    const boton = document.getElementById('toggle-tema');
    const icono = boton?.querySelector('i');
    const tema  = document.documentElement.getAttribute('data-bs-theme');

    if (icono) {
        icono.className = tema === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }

    boton?.addEventListener('click', function () {
        const html  = document.documentElement;
        const nuevo = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-bs-theme', nuevo);
        localStorage.setItem('tema', nuevo);
        icono.className = nuevo === 'dark' ? 'fas fa-sun' : 'fas fa-moon';

        // Actualizar modales abiertos
        document.querySelectorAll('.modal').forEach(modal => {
            modal.setAttribute('data-bs-theme', nuevo); // ← era nuevoTema, debe ser nuevo
        });
    });

    // Sincronizar tema al abrir cualquier modal
    document.addEventListener('show.bs.modal', function (e) {
        const temaActual = document.documentElement.getAttribute('data-bs-theme');
        if (temaActual) e.target.setAttribute('data-bs-theme', temaActual);
    });
});