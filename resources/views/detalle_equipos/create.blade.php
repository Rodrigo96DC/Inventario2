@extends('layouts.app')

@push('scripts')
    <script>
        const routeEquiposPorTipo = "{{ route('detalle-equipo.equiposPorTipo') }}";
    </script>
    <script src="{{ asset('js/detalle_equipos.js') }}"></script>
@endpush

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12 col-lg-10 mx-auto">
            <form action="{{ route('detalle-equipo.store') }}" method="POST"
                class="bg-body-tertiary py-3 px-4 shadow rounded">
                <h3 class="display-6 text-center mb-4">Registrar Equipo</h3>
             @include('detalle_equipos._form', [
    'btntext'        => 'Guardar',
    'detalle_equipo' => $detalleEquipo,
    'macEthernet'    => null,
    'macWifi'        => null,
])
            </form>
        </div>
    </div>
</div>
@endsection