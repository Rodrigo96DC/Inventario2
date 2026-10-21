@extends('layouts.app')

@push('scripts')
    <script src="{{ asset('js/funcionarios.js') }}"></script>
@endpush

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12 col-sm-10 col-lg-8 mx-auto">
                <form action="{{ route('funcionario.store') }}" method="POST" class="py-3 px-4 shadow rounded bg-body"
                    enctype="multipart/form-data">
                    <h3 class="display-5 text-center mb-4">Nuevo Funcionarios</h3>
                    @include('funcionarios._form', ['btntext' => 'Guardar'])
                </form>
            </div>
        </div>
    </div>
@endsection