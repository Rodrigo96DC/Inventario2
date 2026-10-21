@extends('layouts.app')

@push('scripts')
    <script>
        const routeEquiposPorTipo = "{{ route('detalle-equipo.equiposPorTipo') }}";
        const tipoSeleccionado    = {{ $detalle_equipo->equipo->tipo_equipo_id ?? 'null' }};
        const equipoSeleccionado  = {{ $detalle_equipo->equipo_id ?? 'null' }};
    </script>
    <script src="{{ asset('js/detalle_equipos.js') }}"></script>
@endpush

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12 col-lg-10 mx-auto">
            <form action="{{ route('detalle-equipo.update', $detalle_equipo) }}" method="POST"
                class="bg-body-tertiary py-3 px-4 shadow rounded">
                @method('PUT')
                <h3 class="display-6 text-center mb-4">Editar Equipo — {{ $detalle_equipo->inventario }}</h3>
               @if (request('from') === 'show')
    <input type="hidden" name="redirect_to" value="show">
@endif
                @include('detalle_equipos._form', ['btntext' => 'Actualizar'])
            </form>
        </div>
    </div>
</div>
@endsection