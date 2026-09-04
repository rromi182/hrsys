@extends('layouts.master')
@section('title') Movimientos de Nómina @endsection
@section('content')
<!-- Page-content -->
<div class="group-data-[sidebar-size=lg]:ltr:md:ml-vertical-menu group-data-[sidebar-size=lg]:rtl:md:mr-vertical-menu group-data-[sidebar-size=md]:ltr:ml-vertical-menu-md group-data-[sidebar-size=md]:rtl:mr-vertical-menu-md group-data-[sidebar-size=sm]:ltr:ml-vertical-menu-sm group-data-[sidebar-size=sm]:rtl:mr-vertical-menu-sm pt-[calc(theme('spacing.header')_*_1)] pb-[calc(theme('spacing.header')_*_0.8)] px-4 group-data-[navbar=bordered]:pt-[calc(theme('spacing.header')_*_1.3)] group-data-[navbar=hidden]:pt-0 group-data-[layout=horizontal]:mx-auto group-data-[layout=horizontal]:max-w-screen-2xl group-data-[layout=horizontal]:px-0 group-data-[layout=horizontal]:group-data-[sidebar-size=lg]:ltr:md:ml-auto group-data-[layout=horizontal]:group-data-[sidebar-size=lg]:rtl:md:mr-auto group-data-[layout=horizontal]:md:pt-[calc(theme('spacing.header')_*_1.6)] group-data-[layout=horizontal]:px-3 group-data-[layout=horizontal]:group-data-[navbar=hidden]:pt-[calc(theme('spacing.header')_*_0.9)]">
    <div class="container-fluid group-data-[content=boxed]:max-w-boxed mx-auto">
        <div class="flex flex-col gap-2 py-4 md:flex-row md:items-center print:hidden">
            <div class="grow">
                <h5 class="text-16">Movimientos de Nómina</h5>
            </div>
            <ul class="flex items-center gap-2 text-sm font-normal shrink-0">
                <li class="relative before:content-['\ea54'] before:font-remix ltr:before:-right-1 rtl:before:-left-1 before:absolute before:text-[18px] before:-top-[3px] ltr:pr-4 rtl:pl-4 before:text-slate-400 dark:text-zink-200">
                    <a href="#" class="text-slate-400 dark:text-zink-200">RRHH</a>
                </li>
                <li class="text-slate-700 dark:text-zink-100">Movimientos Nómina</li>
            </ul>
        </div>

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('nomina.movimientos') }}" method="GET" class="grid grid-cols-1 gap-4 xl:grid-cols-12">
                    <div class="xl:col-span-2">
                        <label class="inline-block mb-2 text-base font-medium">Año</label>
                        <select name="anio" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full">
                            @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                                <option value="{{ $y }}" {{ request('anio', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                        </select>
                    </div>
                    <div class="xl:col-span-2">
                        <label class="inline-block mb-2 text-base font-medium">Mes</label>
                        <select name="mes" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full">
                            @foreach(['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'] as $idx => $nombre)
                            <option value="{{ $idx + 1 }}" {{ request('mes', date('m')) == ($idx + 1) ? 'selected' : '' }}>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Empleado</label>
                        <select name="empleado_id" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full">
                            <option value="">Todos</option>
                            @foreach($empleados as $emp)
                            <option value="{{ $emp->id }}" {{ request('empleado_id') == $emp->id ? 'selected' : '' }}>{{ $emp->apellidos }}, {{ $emp->nombres }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Tipo Movimiento</label>
                        <select name="tipo" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full">
                            <option value="">Todos</option>
                            <option value="sueldo" {{ request('tipo') == 'sueldo' ? 'selected' : '' }}>Sueldo</option>
                            <option value="extra" {{ request('tipo') == 'extra' ? 'selected' : '' }}>Extra</option>
                            <option value="vale" {{ request('tipo') == 'vale' ? 'selected' : '' }}>Vale</option>
                            <option value="ausencia" {{ request('tipo') == 'ausencia' ? 'selected' : '' }}>Ausencia</option>
                            <option value="llegada_tardia" {{ request('tipo') == 'llegada_tardia' ? 'selected' : '' }}>Llegada Tardía</option>
                            <option value="otros" {{ request('tipo') == 'otros' ? 'selected' : '' }}>Otros</option>
                        </select>
                    </div>
                    <div class="xl:col-span-2 flex items-end">
                        <button type="submit" class="text-white btn bg-custom-500 border-custom-500 hover:text-white hover:bg-custom-600 hover:border-custom-600 focus:text-white focus:bg-custom-600 focus:border-custom-600 focus:ring focus:ring-custom-100 active:text-white active:bg-custom-600 active:border-custom-600 active:ring active:ring-custom-100 dark:ring-custom-400/20 w-full">
                            <i data-lucide="filter" class="inline-block size-4"></i>
                            <span class="align-middle">Filtrar</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla -->
        <div class="card">
            <div class="card-body">
                <div class="flex items-center">
                    <h6 class="text-15 grow">Registro de salarios, extras, vales y descuentos</h6>
                    <div class="shrink-0">
                        <button data-modal-target="modalNuevoMovimiento" type="button" class="text-white btn bg-custom-500 border-custom-500 hover:text-white hover:bg-custom-600 hover:border-custom-600 focus:text-white focus:bg-custom-600 focus:border-custom-600 focus:ring focus:ring-custom-100 active:text-white active:bg-custom-600 active:border-custom-600 active:ring active:ring-custom-100 dark:ring-custom-400/20">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="plus" class="lucide lucide-plus inline-block size-4">
                                <path d="M5 12h14"></path>
                                <path d="M12 5v14"></path>
                            </svg>
                            <span class="align-middle">Nuevo Movimiento</span>
                        </button>
                    </div>
                </div>
                <br>
                <table id="alternativePagination" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">#</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">FECHA</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">CI</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">EMPLEADO</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">TIPO</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">MONTO</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">NATURALEZA</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">ESTADO</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">OBSERVACIÓN</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movimientos as $key => $mov)
                        <tr>
                            <td class="px-3.5 py-2.5 border border-slate-200 dark:border-zink-500 text-center">{{ $loop->iteration }}</td>
                            <td class="px-3.5 py-2.5 border border-slate-200 dark:border-zink-500 text-center">{{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }}</td>
                            <td class="px-3.5 py-2.5 border border-slate-200 dark:border-zink-500 text-center">{{ $mov->empleado->numero_documento ?? '-' }}</td>
                            <td class="px-3.5 py-2.5 border border-slate-200 dark:border-zink-500">{{ $mov->empleado->apellidos ?? '' }}, {{ $mov->empleado->nombres ?? '' }}</td>
                            <td class="px-3.5 py-2.5 border border-slate-200 dark:border-zink-500 text-center">
                                @php
                                $tipos = [
                                'sueldo' => '<span class="badge bg-primary-subtle text-primary">Sueldo</span>',
                                'extra' => '<span class="badge bg-info-subtle text-info">Extra</span>',
                                'vale' => '<span class="badge bg-warning-subtle text-warning">Vale</span>',
                                'ausencia' => '<span class="badge bg-danger-subtle text-danger">Ausencia</span>',
                                'llegada_tardia' => '<span class="badge bg-secondary-subtle text-secondary">Llegada Tardía</span>',
                                'otros' => '<span class="badge bg-dark-subtle text-dark">Otros</span>',
                                ];
                                @endphp
                                {!! $tipos[$mov->tipo_movimiento] ?? $mov->tipo_movimiento !!}
                            </td>
                            <td class="px-3.5 py-2.5 border border-slate-200 dark:border-zink-500 text-end fw-semibold {{ $mov->es_ingreso ? 'text-success' : 'text-danger' }}">
                                {{ number_format($mov->monto, 0, ',', '.') }}
                            </td>
                            <td class="px-3.5 py-2.5 border border-slate-200 dark:border-zink-500 text-center">
                                @if($mov->es_ingreso)
                                <span class="badge bg-success-subtle text-success">Ingreso</span>
                                @else
                                <span class="badge bg-danger-subtle text-danger">Descuento</span>
                                @endif
                            </td>
                            <td class="px-3.5 py-2.5 border border-slate-200 dark:border-zink-500 text-center">
                                @if($mov->estado == 'activo')
                                <span class="badge bg-primary-subtle text-primary">Activo</span>
                                @else
                                <span class="badge bg-secondary-subtle text-secondary">Anulado</span>
                                @endif
                            </td>
                            <td class="px-3.5 py-2.5 border border-slate-200 dark:border-zink-500 text-muted small">{{ $mov->observacion ?: '-' }}</td>
                            <!-- En la tabla, reemplazar la columna ACCIONES -->
                            <td class="px-3.5 py-2.5 border border-slate-200 dark:border-zink-500">
                                <div class="flex gap-2 justify-center">
                                    @if($mov->estado == 'activo')
                                    <!-- Botón Editar -->
                                    <a href="#!" data-modal-target="modalEditarMovimiento"
                                        id="editarMovimiento"
                                        data-id="{{ $mov->id }}"
                                        class="flex items-center justify-center transition-all duration-200 ease-linear rounded-md size-8 bg-slate-100 text-slate-500 hover:text-custom-500 hover:bg-custom-100 dark:bg-zink-600 dark:text-zink-200 dark:hover:bg-custom-500/20 dark:hover:text-custom-500">
                                        <i data-lucide="pencil" class="size-4"></i>
                                    </a>
                                    <!-- Botón Anular -->
                                    <a href="#!" data-modal-target="modalAnular"
                                        id="anularMovimiento"
                                        data-id="{{ $mov->id }}"
                                        data-monto="{{ number_format($mov->monto, 0, ',', '.') }}"
                                        class="flex items-center justify-center transition-all duration-200 ease-linear rounded-md size-8 bg-slate-100 text-slate-500 hover:text-red-500 hover:bg-red-100 dark:bg-zink-600 dark:text-zink-200 dark:hover:text-red-500 dark:hover:bg-red-500/20">
                                        <i data-lucide="x-circle" class="size-4"></i>
                                    </a>
                                    @else
                                    <span class="text-muted small">Anulado</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="px-3.5 py-2.5 border border-slate-200 dark:border-zink-500 text-center">No hay movimientos registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- End Page-content -->

<!-- Modal Nuevo Movimiento -->
<div id="modalNuevoMovimiento" modal-center="" class="fixed flex flex-col hidden transition-all duration-300 ease-in-out left-2/4 z-drawer -translate-x-2/4 -translate-y-2/4 show">
    <div class="w-screen md:w-[40rem] bg-white shadow rounded-md dark:bg-zink-600">
        <div class="flex items-center justify-between p-4 border-b dark:border-zink-500">
            <h5 class="text-16">Nuevo Movimiento</h5>
            <button data-modal-close="modalNuevoMovimiento" class="transition-all duration-200 ease-linear text-slate-400 hover:text-red-500">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="max-h-[calc(theme('height.screen')_-_180px)] p-4 overflow-y-auto">
            <form action="{{ route('nomina.movimientos.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
                    <div class="xl:col-span-12">
                        <label for="empleado_id" class="inline-block mb-2 text-base font-medium">Empleado *</label>
                        <select name="empleado_id" id="empleado_id" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" required>
                            <option value="">Seleccione...</option>
                            @foreach($empleados as $emp)
                            <option value="{{ $emp->id }}" {{ old('empleado_id') == $emp->id ? 'selected' : '' }}>{{ $emp->apellidos }}, {{ $emp->nombres }} (CI: {{ $emp->numero_documento }})</option>
                            @endforeach
                        </select>
                        @error('empleado_id')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="xl:col-span-6">
                        <label for="fecha" class="inline-block mb-2 text-base font-medium">Fecha *</label>
                        <input type="date" name="fecha" id="fecha" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" required value="{{ old('fecha', date('Y-m-d')) }}">
                        @error('fecha')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="xl:col-span-6">
                        <label for="tipo_movimiento" class="inline-block mb-2 text-base font-medium">Tipo Movimiento *</label>
                        <select name="tipo_movimiento" id="tipo_movimiento" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" required>
                            <option value="">Seleccione...</option>
                            @foreach(\App\Models\MovimientoNomina::getTiposConMontos() as $valor => $data)
                            <option value="{{ $valor }}"
                                data-monto="{{ $data['monto'] }}"
                                data-es-ingreso="{{ $data['es_ingreso'] ? 'true' : 'false' }}"
                                {{ old('tipo_movimiento') == $valor ? 'selected' : '' }}>
                                {{ $data['label'] }}  
                            </option>
                            @endforeach
                        </select>
                        @error('tipo_movimiento')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="xl:col-span-12">
                        <label for="monto" class="inline-block mb-2 text-base font-medium">Monto (Gs.) *</label>
                        <input type="number" name="monto" id="monto" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" min="0" step="1" required value="{{ old('monto', 0) }}">
                        @error('monto')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="xl:col-span-12">
                        <label for="observacion" class="inline-block mb-2 text-base font-medium">Observación</label>
                        <textarea name="observacion" id="observacion" rows="2" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" placeholder="Opcional">{{ old('observacion') }}</textarea>
                        @error('observacion')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="reset" data-modal-close="modalNuevoMovimiento" class="text-red-500 bg-white btn hover:text-red-500 hover:bg-red-100 focus:text-red-500 focus:bg-red-100 active:text-red-500 active:bg-red-100 dark:bg-zink-600 dark:hover:bg-red-500/10 dark:focus:bg-red-500/10 dark:active:bg-red-500/10">Cancelar</button>
                    <button type="submit" class="text-white btn bg-custom-500 border-custom-500 hover:text-white hover:bg-custom-600 hover:border-custom-600 focus:text-white focus:bg-custom-600 focus:border-custom-600 focus:ring focus:ring-custom-100 active:text-white active:bg-custom-600 active:border-custom-600 active:ring active:ring-custom-100 dark:ring-custom-400/20">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!--end modal nuevo movimiento-->

<!-- Modal Editar Movimiento -->
<div id="modalEditarMovimiento" modal-center="" class="fixed flex flex-col hidden transition-all duration-300 ease-in-out left-2/4 z-drawer -translate-x-2/4 -translate-y-2/4 show">
    <div class="w-screen md:w-[40rem] bg-white shadow rounded-md dark:bg-zink-600">
        <div class="flex items-center justify-between p-4 border-b dark:border-zink-500">
            <h5 class="text-16">Editar Movimiento</h5>
            <button data-modal-close="modalEditarMovimiento" class="transition-all duration-200 ease-linear text-slate-400 hover:text-red-500">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="max-h-[calc(theme('height.screen')_-_180px)] p-4 overflow-y-auto">
            <form action="{{ route('nomina.movimientos.update', ['id' => ':id']) }}" method="POST" id="formEditarMovimiento">
                @csrf
                @method('PUT')
                <input type="hidden" name="id_editar" id="e_idEditar" value="">

                <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
                    <div class="xl:col-span-12">
                        <label class="inline-block mb-2 text-base font-medium">Empleado *</label>
                        <select name="empleado_id" id="e_empleado_id" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" required>
                            <option value="">Seleccione...</option>
                            @foreach($empleados as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->apellidos }}, {{ $emp->nombres }} (CI: {{ $emp->numero_documento }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="xl:col-span-6">
                        <label class="inline-block mb-2 text-base font-medium">Fecha *</label>
                        <input type="date" name="fecha" id="e_fecha" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" required>
                    </div>

                    <div class="xl:col-span-6">
                        <label class="inline-block mb-2 text-base font-medium">Tipo Movimiento *</label>
                        <select name="tipo_movimiento" id="e_tipo_movimiento" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" required>
                            <option value="">Seleccione...</option>
                            <option value="sueldo">Sueldo</option>
                            <option value="extra">Extra</option>
                            <option value="vale">Vale</option>
                            <option value="ausencia">Ausencia</option>
                            <option value="llegada_tardia">Llegada Tardía</option>
                            <option value="otros">Otros</option>
                        </select>
                    </div>

                    <div class="xl:col-span-12">
                        <label class="inline-block mb-2 text-base font-medium">Monto (Gs.) *</label>
                        <input type="number" name="monto" id="e_monto" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" min="0" step="1" required>
                    </div>

                    <div class="xl:col-span-12">
                        <label class="inline-block mb-2 text-base font-medium">Observación</label>
                        <textarea name="observacion" id="e_observacion" rows="2" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" placeholder="Opcional"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="reset" data-modal-close="modalEditarMovimiento" class="text-red-500 bg-white btn hover:text-red-500 hover:bg-red-100 focus:text-red-500 focus:bg-red-100 active:text-red-500 active:bg-red-100 dark:bg-zink-600 dark:hover:bg-red-500/10 dark:focus:bg-red-500/10 dark:active:bg-red-500/10">Cancelar</button>
                    <button type="submit" class="text-white btn bg-custom-500 border-custom-500 hover:text-white hover:bg-custom-600 hover:border-custom-600 focus:text-white focus:bg-custom-600 focus:border-custom-600 focus:ring focus:ring-custom-100 active:text-white active:bg-custom-600 active:border-custom-600 active:ring active:ring-custom-100 dark:ring-custom-400/20">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!--end modal editar movimiento-->

<!-- Modal Anular -->
<div id="modalAnular" modal-center="" class="fixed flex flex-col hidden transition-all duration-300 ease-in-out left-2/4 z-drawer -translate-x-2/4 -translate-y-2/4 show">
    <div class="w-screen md:w-[25rem] bg-white shadow rounded-md dark:bg-zink-600">
        <div class="max-h-[calc(theme('height.screen')_-_180px)] overflow-y-auto px-6 py-8">
            <div class="float-right">
                <button data-modal-close="modalAnular" class="transition-all duration-200 ease-linear text-slate-500 hover:text-red-500">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <img src="data:image/png;base64,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" alt="" class="block h-12 mx-auto">
            <form action="{{ route('nomina.movimientos.anular', ['id' => ':id']) }}" method="POST" id="formAnular">
                @csrf
                @method('POST')
                <input type="hidden" name="id_anular" id="e_idAnular" value="">
                <div class="mt-5 text-center">
                    <h5 class="mb-1">¿Anular movimiento?</h5>
                    <p class="text-slate-500 dark:text-zink-200">Esta acción marcará el movimiento como anulado.</p>
                    <p class="text-slate-500 dark:text-zink-200 mt-1">Monto: <strong id="anularMontoDisplay"></strong></p>
                    <div class="flex justify-center gap-2 mt-6">
                        <button type="reset" data-modal-close="modalAnular" class="bg-white text-slate-500 btn hover:text-slate-500 hover:bg-slate-100 focus:text-slate-500 focus:bg-slate-100 active:text-slate-500 active:bg-slate-100 dark:bg-zink-600 dark:hover:bg-slate-500/10 dark:focus:bg-slate-500/10 dark:active:bg-slate-500/10">Cancelar</button>
                        <button type="submit" class="text-white bg-red-500 border-red-500 btn hover:text-white hover:bg-red-600 hover:border-red-600 focus:text-white focus:bg-red-600 focus:border-red-600 focus:ring focus:ring-red-100 active:text-white active:bg-red-600 active:border-red-600 active:ring active:ring-red-100 dark:ring-custom-400/20">Sí, Anular</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!--end modal anular-->

@section('scripts')
<!-- Anular JS - Mismo estilo que holiday y employee -->
<script>
    $(document).on('click', '#editarMovimiento', function() {
        var _this = $(this);
        var id = _this.data('id');

        // Cargar datos del movimiento via AJAX
        $.ajax({
            url: "{{ route('nomina.movimientos.edit', ['id' => ':id']) }}".replace(':id', id),
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    var data = response.data;

                    // Actualizar action del form
                    var url = "{{ route('nomina.movimientos.update', ['id' => ':id']) }}";
                    url = url.replace(':id', id);
                    $('#formEditarMovimiento').attr('action', url);

                    // Llenar campos del modal
                    $('#e_idEditar').val(data.id);
                    $('#e_empleado_id').val(data.empleado_id).trigger('change');
                    $('#e_fecha').val(data.fecha);
                    $('#e_tipo_movimiento').val(data.tipo_movimiento).trigger('change');
                    $('#e_monto').val(data.monto);
                    $('#e_observacion').val(data.observacion || '');
                }
            },
            error: function(xhr) {
                var message = 'Error al cargar los datos.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                flasher.error(message);

                // Cerrar modal y mostrar error
                $('#modalEditarMovimiento').addClass('hidden');
                $('#modalEditarMovimiento').css('display', 'none');
            }
        });
    });

     $(document).ready(function() {
        // Auto-completar monto al seleccionar tipo
        $('#tipo_movimiento').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var monto = selectedOption.data('monto');
            var esIngreso = selectedOption.data('es-ingreso');
            
            // Si el monto es 0, dejar que el usuario lo ingrese
            if (monto > 0) {
                $('#monto').val(monto).prop('readonly', true);
            } else {
                $('#monto').val(0).prop('readonly', false);
            }
        });
        
        // Para el modal de edición
        $('#e_tipo_movimiento').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var monto = selectedOption.data('monto');
            
            if (monto > 0) {
                $('#e_monto').val(monto).prop('readonly', true);
            } else {
                $('#e_monto').val(0).prop('readonly', false);
            }
        });
    });

    $(document).on('click', '#anularMovimiento', function() {
        var _this = $(this);
        var id = _this.data('id');
        var monto = _this.data('monto');

        $('#e_idAnular').val(id);
        $('#anularMontoDisplay').text(monto);

        // Actualizar action del form
        var url = "{{ route('nomina.movimientos.anular', ['id' => ':id']) }}";
        url = url.replace(':id', id);
        $('#formAnular').attr('action', url);
    });
</script>
@endsection
@endsection