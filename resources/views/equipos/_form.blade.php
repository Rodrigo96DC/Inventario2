@csrf

<div class="row">
    <div class="col-md-6 form-group">
        <label for="tipo_equipo_id">Tipo de Equipo</label>
        <select name="tipo_equipo_id" id="tipo_equipo_id"
            class="form-control bg-light shadow-sm @error('tipo_equipo_id') is-invalid @else border-0 @enderror">
            <option value="">Seleccione</option>
            @foreach ($tipoEquipos as $tipo)
                <option value="{{ $tipo->id }}" @if ($tipo->id == old('tipo_equipo_id', $equipo->tipo_equipo_id)) selected @endif>
                    {{ $tipo->nombre }}
                </option>
            @endforeach
        </select>
        @error('tipo_equipo_id')
            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="col-md-6 form-group">
        <label for="marca">Marca</label>
        <input type="text" name="marca" id="marca" value="{{ old('marca', $equipo->marca) }}"
            class="form-control bg-light shadow-sm @error('marca') is-invalid @else border-0 @enderror">
        @error('marca')
            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="form-group">
    <label for="modelo">Modelo</label>
    <input type="text" name="modelo" id="modelo" value="{{ old('modelo', $equipo->modelo) }}"
        class="form-control bg-light shadow-sm @error('modelo') is-invalid @else border-0 @enderror">
    @error('modelo')
        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
    @enderror
</div>

<div class="row">
    <div class="col-md-6 form-group">
        <label for="entradas">Entradas <small class="text-muted">(Monitor, Proyector, Tablet)</small></label>
        <input type="text" name="entradas" id="entradas" value="{{ old('entradas', $equipo->entradas) }}"
            placeholder="Ej: HDMI/VGA"
            class="form-control bg-light shadow-sm @error('entradas') is-invalid @else border-0 @enderror">
        @error('entradas')
            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="col-md-6 form-group">
        <label for="procesador">Procesador <small class="text-muted">(Computador, Notebook)</small></label>
        <input type="text" name="procesador" id="procesador" value="{{ old('procesador', $equipo->procesador) }}"
            class="form-control bg-light shadow-sm @error('procesador') is-invalid @else border-0 @enderror">
        @error('procesador')
            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="form-group">
    <label for="imagen">Imagen</label>

    @if ($equipo->imagen)
        <div class="mb-2">
            <img src="{{ asset('storage/' . $equipo->imagen) }}" alt="{{ $equipo->modelo }}"
                 style="max-height: 100px;" class="rounded border d-block mb-1">
            <small class="text-muted">Selecciona un archivo solo si quieres reemplazar la imagen actual.</small>
        </div>
    @endif

    <input type="file" name="imagen" id="imagen" accept="image/png, image/jpeg"
        class="form-control @error('imagen') is-invalid @else border-0 @enderror">
    @error('imagen')
        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
    @enderror
</div>

<div class="form-group">
    <label for="observacion">Observación <small class="text-muted">(opcional)</small></label>
    <textarea name="observacion" id="observacion" rows="3"
        class="form-control bg-light shadow-sm @error('observacion') is-invalid @else border-0 @enderror">{{ old('observacion', $equipo->observacion) }}</textarea>
    @error('observacion')
        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
    @enderror
</div>

<div class="d-grid gap-2">
    <input type="submit" class="btn btn-primary my-2" value="{{ $btntext }}">
    <a href="{{ route('equipo.index') }}" class="btn btn-link btn-block">Cancelar</a>
</div>