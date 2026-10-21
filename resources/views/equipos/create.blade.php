@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12 col-sm-10 col-lg-8 mx-auto">
                <form action="{{ route('equipo.store') }}" method="POST" class="py-3 px-4 shadow rounded"
                    enctype="multipart/form-data">
                    <h3 class="display-5 text-center mb-4">Nuevo Equipo</h3>
                    @include('equipos._form', ['btntext' => 'Guardar'])
                </form>
            </div>
        </div>
    </div>
@endsection