document.addEventListener('DOMContentLoaded', function () {
    const badge   = document.getElementById('badge-notificaciones');
    const lista   = document.getElementById('lista-notificaciones');

    if (!badge) return;

    fetch('/interno/contador-contratos')
        .then(r => r.json())
        .then(data => {
            if (data.count > 0) {
                badge.textContent = data.count;
                badge.classList.remove('d-none');
            }

            if (data.funcionarios.length === 0) {
                lista.innerHTML = '<span class="dropdown-item-text text-secondary small">Sin contratos por vencer.</span>';
                return;
            }

            lista.innerHTML = '';
            const ul = document.createElement('div');
            data.funcionarios.forEach(f => {
                const item = document.createElement('a');
                item.className = 'dropdown-item small';
                item.href = `/funcionarios/${f.id}`;
                item.target = '_blank';

                const colorTexto = f.dias < 0 ? 'text-danger' : (f.dias <= 7 ? 'text-warning' : '');
                const etiqueta = f.dias < 0 ? 'Vencido' : `${f.dias} día(s)`;

                item.innerHTML = `
                    <div class="d-flex justify-content-between">
                        <span>${f.nombre}</span>
                        <span class="${colorTexto} fw-bold">${etiqueta}</span>
                    </div>
                    <small class="text-secondary">${f.fecha}</small>
                `;
                lista.parentElement.insertBefore(item, lista.nextSibling);
            });
            lista.remove();
        });
});