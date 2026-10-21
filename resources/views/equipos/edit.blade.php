@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12 col-sm-10 col-lg-8 mx-auto">
                <form action="{{ route('equipo.update', $equipo) }}" method="POST" class="bg-white py-3 px-4 shadow rounded"
                    enctype="multipart/form-data">
                    @method('PUT')
                    <h3 class="display-5 text-center mb-4">Editar Equipo</h3>
                    @include('equipos._form', ['btntext' => 'Actualizar'])
                </form>
            </div>
        </div>
    </div>
@endsection