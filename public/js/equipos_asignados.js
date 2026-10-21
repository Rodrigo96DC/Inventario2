document.addEventListener('DOMContentLoaded', function () {
    // Tabs del detalle
document.querySelectorAll('[data-tab]').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('[data-tab]').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        const tabs = ['info', 'ad', 'observaciones', 'historial'];
        tabs.forEach(t => {
            document.getElementById(`tab-${t}`)?.classList.toggle('d-none', t !== this.dataset.tab);
        });

        // Guardar tab activo en URL
        history.replaceState(null, '', '#' + this.dataset.tab);
    });
});

// Al cargar la página, activar el tab del hash
const hash = window.location.hash.replace('#', '');
if (hash) {
    const btn = document.querySelector(`[data-tab="${hash}"]`);
    if (btn) btn.click();
}

    // Buscador con debounce
    const buscar = document.getElementById('buscar');
    const form   = document.getElementById('form-filtros');
    const btnExp = document.getElementById('btn-exportar');
    let timeout  = null;

buscar?.addEventListener('input', function () {
    clearTimeout(timeout);
    var q = this.value;
    timeout = setTimeout(function () {
        actualizarExportar();

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
            var pag      = document.getElementById('paginacion');
            var newPag   = doc.getElementById('paginacion');
            if (tbody && newTbody) tbody.innerHTML = newTbody.innerHTML;
            if (pag && newPag)     pag.innerHTML   = newPag.innerHTML;
        });
    }, 350);
});

    form?.querySelectorAll('select').forEach(el => {
        el.addEventListener('change', () => {
            actualizarExportar();
            form.submit();
        });
    });

    function actualizarExportar() {
        if (! btnExp || ! form) return;
        const params = new URLSearchParams(new FormData(form));
        btnExp.href = `${btnExp.dataset.url}?${params}`;
    }

// Copiar datos AD para correo
document.getElementById('btn-copiar-ad')?.addEventListener('click', function () {
    const hostname = document.getElementById('ad-hostname')?.textContent.trim();
    const mac      = document.getElementById('ad-mac')?.textContent.trim();
    const region   = document.getElementById('ad-region')?.textContent.trim();
    const area     = document.getElementById('ad-area')?.textContent.trim();

    const texto = `Nombre: ${hostname}\nMAC Ethernet/WiFi: ${mac}\nUbicación: ${region}\nÁrea: ${area}`;

    navigator.clipboard.writeText(texto).then(() => {
        this.innerHTML = '<i class="fas fa-check me-1"></i>Copiado';
        this.classList.replace('btn-outline-primary', 'btn-success');
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-copy me-1"></i>Copiar para correo';
            this.classList.replace('btn-success', 'btn-outline-primary');
        }, 2000);
    });
});

function copiarTexto(texto, btn, textoOriginal) {
    // Intenta clipboard API primero
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(texto).then(() => mostrarCopiado(btn, textoOriginal));
    } else {
        // Fallback para HTTP
        const ta = document.createElement('textarea');
        ta.value = texto;
        ta.style.position = 'fixed';
        ta.style.opacity  = '0';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        mostrarCopiado(btn, textoOriginal);
    }
}

function mostrarCopiado(btn, textoOriginal) {
    btn.innerHTML = '<i class="fas fa-check me-1"></i>Copiado';
    btn.classList.replace('btn-outline-primary', 'btn-success');
    setTimeout(() => {
        btn.innerHTML = textoOriginal;
        btn.classList.replace('btn-success', 'btn-outline-primary');
    }, 2000);
}

// Botón copiar AD
document.getElementById('btn-copiar-ad')?.addEventListener('click', function () {
    const hostname = document.getElementById('ad-hostname')?.textContent.trim();
    const mac      = document.getElementById('ad-mac')?.textContent.trim();
    const region   = document.getElementById('ad-region')?.textContent.trim();
    const area     = document.getElementById('ad-area')?.textContent.trim();

    const texto = `Nombre: ${hostname}\nMAC Ethernet/WiFi: ${mac}\nUbicación: ${region}\nÁrea: ${area}`;
    copiarTexto(texto, this, '<i class="fas fa-copy me-1"></i>Copiar para correo');
});

// Botón copiar Acta
document.getElementById('btn-copiar-acta')?.addEventListener('click', function () {
    const tipo       = document.getElementById('acta-tipo')?.textContent.trim();
    const marca      = document.getElementById('acta-marca')?.textContent.trim();
    const modelo     = document.getElementById('acta-modelo')?.textContent.trim();
    const inventario = document.getElementById('acta-inventario')?.textContent.trim();
    const serial     = document.getElementById('acta-serial')?.textContent.trim();
    const cpu        = document.getElementById('acta-cpu')?.textContent.trim();
    const ram        = document.getElementById('acta-ram')?.textContent.trim();
    const disco      = document.getElementById('acta-disco')?.textContent.trim();

    const texto = `Equipo: ${tipo}\nMarca: ${marca}\nModelo: ${modelo}\nInventario: ${inventario}\nSerial: ${serial}\nCPU: ${cpu}\nRAM: ${ram}\nDisco: ${disco}`;
    copiarTexto(texto, this, '<i class="fas fa-copy me-1"></i>Copiar datos acta');
});
document.getElementById('btn-editar-hostname')?.addEventListener('click', function() {
    document.getElementById('modal-hostname-input').value = this.dataset.hostname;
    new bootstrap.Modal(document.getElementById('modal-editar-hostname')).show();
});



});

function copiarTexto(texto, btn) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(texto).then(function() {
            mostrarCopiado(btn);
        });
    } else {
        var ta = document.createElement('textarea');
        ta.value = texto;
        ta.style.position = 'fixed';
        ta.style.opacity  = '0';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        mostrarCopiado(btn);
    }
}

function mostrarCopiado(btn) {
    var original = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check me-1"></i>Copiado';
    btn.classList.add('btn-success');
    setTimeout(function() {
        btn.innerHTML = original;
        btn.classList.remove('btn-success');
    }, 2000);
}