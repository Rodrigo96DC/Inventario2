@csrf

<div class="row">
    <div class="col-md-6 form-group">
        <label for="nombre">Nombre completo</label>
        <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $funcionario->nombre) }}"
            class="form-control bg-light shadow-sm @error('nombre') is-invalid @else border-0 @enderror">
        @error('nombre')
            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="col-md-6 form-group">
        <label for="rut">RUT <small class="text-muted">(opcional)</small></label>
        <input type="text" name="rut" id="rut" value="{{ old('rut', $funcionario->rut) }}"
            placeholder="12.345.678-9"
            class="form-control bg-light shadow-sm @error('rut') is-invalid @else border-0 @enderror">
        @error('rut')
            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 form-group">
        <label for="region_id">Región</label>
        <select name="region_id" id="region_id"
            class="form-control bg-light shadow-sm @error('region_id') is-invalid @else border-0 @enderror">
            <option value="">Seleccione</option>
            @foreach ($regiones as $region)
                <option value="{{ $region->id }}" @if ($region->id == old('region_id', $funcionario->region_id)) selected @endif>
                    {{ $region->nombre }}
                </option>
            @endforeach
        </select>
        @error('region_id')
            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
    <div class="col-md-6 form-group">
        <label for="correo">Correo electrónico <small class="text-muted">(opcional)</small></label>
        <input type="email" name="correo" id="correo" value="{{ old('correo', $funcionario->correo) }}"
            class="form-control bg-light shadow-sm @error('correo') is-invalid @else border-0 @enderror">
        @error('correo')
            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
  
</div>
{{-- Reemplaza el div de área --}}
<div class="row">
    <div class="col-md-6 form-group">
        <label for="division_id">División <small class="text-muted">(opcional)</small></label>
        <select name="division_id" id="division_id"
            class="form-control bg-light shadow-sm @error('division_id') is-invalid @else border-0 @enderror">
            <option value="">Seleccione</option>
            @foreach ($divisiones as $div)
                <option value="{{ $div->id }}"
                    @selected(old('division_id', $funcionario->division_id ?? '') == $div->id)>
                    {{ $div->nombre }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6 form-group">
        <label for="departamento_id">Departamento <small class="text-muted">(opcional)</small></label>
        <select name="departamento_id" id="departamento_id"
            class="form-control bg-light shadow-sm @error('departamento_id') is-invalid @else border-0 @enderror">
            <option value="">Seleccione división primero</option>
            @if (isset($funcionario) && $funcionario->departamento_id)
                <option value="{{ $funcionario->departamento_id }}" selected>
                    {{ $funcionario->departamento->nombre ?? '' }}
                </option>
            @endif
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-6 form-group">
        <label for="unidad_id">Unidad <small class="text-muted">(opcional)</small></label>
        <select name="unidad_id" id="unidad_id"
            class="form-control bg-light shadow-sm @error('unidad_id') is-invalid @else border-0 @enderror">
            <option value="">Seleccione departamento primero</option>
            @if (isset($funcionario) && $funcionario->unidad_id)
                <option value="{{ $funcionario->unidad_id }}" selected>
                    {{ $funcionario->unidad->nombre ?? '' }}
                </option>
            @endif
        </select>
    </div>

    <div class="col-md-6 form-group">
        <label for="cargo">Cargo <small class="text-muted">(opcional)</small></label>
        <input type="text" name="cargo" id="cargo" value="{{ old('cargo', $funcionario->cargo ?? '') }}"
            class="form-control bg-light shadow-sm @error('cargo') is-invalid @else border-0 @enderror">
    </div>
</div>

<div class="row">
    <div class="col-md-6 form-group">
        <label for="fecha_ingreso">Fecha ingreso <small class="text-muted">(opcional)</small></label>
        <input type="date" name="fecha_ingreso" id="fecha_ingreso"
            value="{{ old('fecha_ingreso', $funcionario->fecha_ingreso?->format('Y-m-d') ?? '') }}"
            class="form-control bg-light shadow-sm border-0">
    </div>
    <div class="col-md-6 form-group">
        <label for="fecha_fin_contrato">Fin de contrato <small class="text-muted">(opcional)</small></label>
        <input type="date" name="fecha_fin_contrato" id="fecha_fin_contrato"
            value="{{ old('fecha_fin_contrato', $funcionario->fecha_fin_contrato?->format('Y-m-d') ?? '') }}"
            class="form-control bg-light shadow-sm border-0">
    </div>
</div>
<div class="row">



</div>

<div class="form-group">
    <label for="avatar" class="custom-file-label">Foto <small class="text-muted">(opcional)</small></label>

@if ($funcionario->avatar)
    <div class="mb-2">
        <img src="{{ asset('storage/' . $funcionario->avatar) }}" alt="{{ $funcionario->nombre }}"
             style="max-height: 100px;" class="rounded border d-block mb-1">
        <small class="text-muted">Selecciona un archivo solo si quieres reemplazar la foto actual.</small>
        <div class="form-check mt-1">
            <input class="form-check-input" type="checkbox" name="eliminar_avatar" id="eliminar_avatar" value="1">
            <label class="form-check-label text-danger small" for="eliminar_avatar">
                <i class="fas fa-trash me-1"></i>Eliminar foto actual
            </label>
        </div>
    </div>
@endif

    <input type="file" name="avatar" id="avatar" accept="image/png, image/jpeg"
        class="form-control @error('avatar') is-invalid @else border-0 @enderror">
    @error('avatar')
        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
    @enderror
</div>

<div class="form-check form-switch mb-3">
    <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1"
        @checked(old('activo', $funcionario->activo ?? true))>
    <label class="form-check-label" for="activo">Funcionario activo</label>
</div>

<div class="d-grid gap-2">
    <input type="submit" class="btn btn-primary my-2" value="{{ $btntext }}">
    <a href="{{ route('funcionario.index') }}" class="btn btn-link btn-block">Cancelar</a>
</div>