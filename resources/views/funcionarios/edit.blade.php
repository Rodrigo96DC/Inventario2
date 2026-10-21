@extends('layouts.app')
@push('scripts')
    <script src="{{ asset('js/funcionarios.js') }}"></script>
@endpush
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12 col-sm-10 col-lg-8 mx-auto">
                <form action="{{ route('funcionario.update', $funcionario) }}" method="POST" class="bg-white py-3 px-4 shadow rounded"
                    enctype="multipart/form-data">
                    @method('PUT')
                    <h3 class="display-5 text-center mb-4">Editar Funcionario</h3>
                    @include('funcionarios._form', ['btntext' => 'Actualizar'])
                </form>
            </div>
        </div>
    </div>
@endsection