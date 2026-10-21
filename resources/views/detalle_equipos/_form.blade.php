@csrf

{{-- Fila 1: Tipo → Equipo --}}
<div class="row">
    <div class="col-md-4 form-group mb-3">
        <label for="tipo_equipo_id">Tipo de Equipo</label>
        <select name="tipo_equipo_id" id="tipo_equipo_id"
            class="form-select bg-light border-0 shadow-sm">
            <option value="">Seleccione tipo</option>
        @foreach ($tipoEquipos as $tipo)
    <option value="{{ $tipo->id }}"
        @selected($tipo->id == old('tipo_equipo_id', $detalle_equipo->equipo->tipo_equipo_id ?? ''))>
        {{ $tipo->nombre }}
    </option>
@endforeach
        </select>
    </div>

    <div class="col-md-8 form-group mb-3">
        <label for="equipo_id">Modelo <small class="text-muted">(seleccione tipo primero)</small></label>
        <select name="equipo_id" id="equipo_id"
            class="form-select bg-light shadow-sm @error('equipo_id') is-invalid @else border-0 @enderror">
            <option value="">Seleccione modelo</option>
        @foreach ($equipos as $equipo)
    <option value="{{ $equipo->id }}"
        @selected($equipo->id == old('equipo_id', $detalle_equipo->equipo_id ?? ''))>
        {{ $equipo->marca }} {{ $equipo->modelo }}
    </option>
@endforeach
        </select>
        @error('equipo_id')
            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

{{-- Fila 2: Inventario + Serial --}}
<div class="row">
    <div class="col-md-6 form-group mb-3">
        <label for="inventario">N° Inventario</label>
        <input type="text" name="inventario" id="inventario"
            value="{{ old('inventario', $detalle_equipo->inventario) }}"
            class="form-control bg-light shadow-sm @error('inventario') is-invalid @else border-0 @enderror">
        @error('inventario')
            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="col-md-6 form-group mb-3">
        <label for="serial">Serial</label>
        <input type="text" name="serial" id="serial"
            value="{{ old('serial', $detalle_equipo->serial) }}"
            class="form-control bg-light shadow-sm @error('serial') is-invalid @else border-0 @enderror">
        @error('serial')
            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

{{-- Fila 3: Estado + Tipo Disco --}}
<div class="row">
    <div class="col-md-6 form-group mb-3">
        <label for="estado_id">Estado</label>
        <select name="estado_id" id="estado_id"
            class="form-select bg-light shadow-sm @error('estado_id') is-invalid @else border-0 @enderror">
            <option value="">Seleccione</option>
         @foreach ($estados as $estado)
    <option value="{{ $estado->id }}"
        @selected($estado->id == old('estado_id', $detalle_equipo->estado_id ?? ''))>
        {{ $estado->nombre }}
    </option>
@endforeach
        </select>
        @error('estado_id')
            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="col-md-6 form-group mb-3">
        <label for="tipo_disco_id">Tipo de Disco</label>
        <select name="tipo_disco_id" id="tipo_disco_id"
            class="form-select bg-light shadow-sm border-0">
            <option value="">Sin disco / No aplica</option>
           @foreach ($tiposDiscos as $disco)
    <option value="{{ $disco->id }}"
        @selected($disco->id == old('tipo_disco_id', $detalle_equipo->tipo_disco_id ?? ''))>
        {{ $disco->nombre }}
    </option>
@endforeach
        </select>
    </div>
</div>

{{-- Fila 4: RAM + Capacidad Disco --}}
<div class="row">
    <div class="col-md-6 form-group mb-3">
        <label for="ram_gb">RAM (GB)</label>
        <input type="number" name="ram_gb" id="ram_gb" min="1"
            value="{{ old('ram_gb', $detalle_equipo->ram_gb) }}"
            class="form-control bg-light shadow-sm @error('ram_gb') is-invalid @else border-0 @enderror">
        @error('ram_gb')
            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="col-md-6 form-group mb-3">
        <label for="capacidad_disco_gb">Capacidad Disco (GB)</label>
        <input type="number" name="capacidad_disco_gb" id="capacidad_disco_gb" min="1"
            value="{{ old('capacidad_disco_gb', $detalle_equipo->capacidad_disco_gb) }}"
            class="form-control bg-light shadow-sm @error('capacidad_disco_gb') is-invalid @else border-0 @enderror">
        @error('capacidad_disco_gb')
            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

{{-- Fila 5: MACs --}}
<div class="row">
    <div class="col-md-6 form-group mb-3">
        <label for="mac_ethernet">MAC Ethernet</label>
        <input type="text" name="mac_ethernet" id="mac_ethernet"
            value="{{ old('mac_ethernet', $macEthernet->mac ?? '') }}"
            placeholder="AA:BB:CC:DD:EE:FF"
            class="form-control bg-light shadow-sm @error('mac_ethernet') is-invalid @else border-0 @enderror">
        @error('mac_ethernet')
            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="col-md-6 form-group mb-3">
        <label for="mac_wifi">MAC WiFi <small class="text-muted">(opcional)</small></label>
        <input type="text" name="mac_wifi" id="mac_wifi"
            value="{{ old('mac_wifi', $macWifi->mac ?? '') }}"
            placeholder="AA:BB:CC:DD:EE:FF"
            class="form-control bg-light shadow-sm @error('mac_wifi') is-invalid @else border-0 @enderror">
        @error('mac_wifi')
            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

{{-- Fila 6: IP + Puerto Switch --}}
<div class="row">
    <div class="col-md-6 form-group mb-3">
        <label for="ip">IP <small class="text-muted">(opcional)</small></label>
        <input type="text" name="ip" id="ip"
            value="{{ old('ip', $detalle_equipo->ip) }}"
            placeholder="192.168.1.100"
            class="form-control bg-light shadow-sm @error('ip') is-invalid @else border-0 @enderror">
        @error('ip')
            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="col-md-6 form-group mb-3">
        <label for="puerto_switch">Puerto Switch <small class="text-muted">(opcional)</small></label>
        <input type="number" name="puerto_switch" id="puerto_switch" min="1"
           value="{{ old('puerto_switch', $detalle_equipo->puerto_switch) }}"
            class="form-control bg-light shadow-sm @error('puerto_switch') is-invalid @else border-0 @enderror">
        @error('puerto_switch')
            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

{{-- Fila 7: Fecha Adquisición --}}
<div class="row">
    <div class="col-md-6 form-group mb-3">
        <label for="fecha_adquisicion">Fecha Adquisición <small class="text-muted">(opcional)</small></label>
        <input type="date" name="fecha_adquisicion" id="fecha_adquisicion"
            value="{{ old('fecha_adquisicion', $detalle_equipo->fecha_adquisicion?->format('Y-m-d')) }}"
            class="form-control bg-light shadow-sm border-0">
    </div>
</div>

{{-- Fila 8: Switches --}}
<div class="row mb-3">
    <div class="col-md-6">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch"
                id="teletrabajo" name="teletrabajo" value="1"
                @checked(old('teletrabajo', $detalle_equipo->teletrabajo))>
            <label class="form-check-label" for="teletrabajo">Teletrabajo</label>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch"
                id="es_prestamo" name="es_prestamo" value="1"
                @checked(old('es_prestamo', $detalle_equipo->es_prestamo))>
            <label class="form-check-label" for="es_prestamo">Es préstamo</label>
        </div>
    </div>
</div>

<div class="d-grid gap-2">
    <input type="submit" class="btn btn-primary" value="{{ $btntext }}">
    @if (request('from') === 'show' || old('redirect_to') === 'show')
        <a href="{{ route('equipo-asignado.show', $detalle_equipo) }}" class="btn btn-link">Cancelar</a>
    @else
        <a href="{{ route('detalle-equipo.index') }}" class="btn btn-link">Cancelar</a>
    @endif
</div>