@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <h3 class="mb-3">
        <i class="fas fa-calendar-xmark text-warning me-2"></i>Contratos por vencer
    </h3>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Funcionario</th>
                        <th>División</th>
                        <th>Cargo</th>
                        <th>Fin de contrato</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($funcionarios as $f)
                        @php $dias = (int) now()->diffInDays($f->fecha_fin_contrato, false); @endphp
                        <tr>
                            <td class="fw-medium">{{ $f->nombre }}</td>
                            <td class="small text-secondary">{{ $f->division?->nombre ?? '—' }}</td>
                            <td class="small">{{ $f->cargo ?? '—' }}</td>
                            <td>{{ $f->fecha_fin_contrato->format('d-m-Y') }}</td>
                            <td class="text-center">
                                @if ($dias < 0)
                                    <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                                        Vencido hace {{ abs($dias) }} día(s)
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                        Vence en {{ $dias }} día(s)
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('funcionario.show', $f) }}" class="btn btn-sm btn-outline-info" target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('funcionario.edit', $f) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-pen"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">
                                Sin contratos próximos a vencer.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection