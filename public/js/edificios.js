document.addEventListener('DOMContentLoaded', function () {
    const modalEdificio = new bootstrap.Modal(document.getElementById('modal-edificio'));
    const modalPiso     = new bootstrap.Modal(document.getElementById('modal-piso'));
    const formEdificio  = document.getElementById('form-edificio');

    // Editar edificio
    document.querySelectorAll('.btn-editar-edificio').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('modal-edificio-titulo').innerHTML =
                '<i class="fas fa-building me-2"></i>Editar Edificio';
            document.getElementById('edificio-nombre').value    = this.dataset.nombre;
            document.getElementById('edificio-direccion').value = this.dataset.direccion ?? '';
            document.getElementById('edificio-ciudad').value    = this.dataset.ciudad ?? '';
            document.getElementById('edificio-method').value    = 'PUT';

            formEdificio.action = `/edificios/${this.dataset.id}`;
            modalEdificio.show();
        });
    });

    // Nuevo edificio — reset form
    document.querySelector('[data-bs-target="#modal-edificio"]')?.addEventListener('click', function () {
        document.getElementById('modal-edificio-titulo').innerHTML =
            '<i class="fas fa-building me-2"></i>Nuevo Edificio';
        formEdificio.reset();
        formEdificio.action = '/edificios';
        document.getElementById('edificio-method').value = 'POST';
    });

    // Agregar piso
    document.querySelectorAll('.btn-agregar-piso').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('modal-piso-edificio').textContent =
                '— ' + this.dataset.edificioNombre;
            document.getElementById('form-piso').action =
                `/edificios/${this.dataset.edificioId}/pisos`;
            modalPiso.show();
        });
    });


// Editar piso
const modalEditarPiso = new bootstrap.Modal(document.getElementById('modal-editar-piso'));

document.querySelectorAll('.btn-editar-piso').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('editar-piso-nombre').value  = this.dataset.nombre;
        document.getElementById('editar-piso-numero').value  = this.dataset.numero ?? '';
        document.getElementById('form-editar-piso').action   = `/pisos/${this.dataset.id}`;
        modalEditarPiso.show();
    });
});

});