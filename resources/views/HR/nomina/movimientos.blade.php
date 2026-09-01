@extends('layouts.master')
@section('title') Movimientos de Nómina @endsection
@section('content')

<div class="group-data-[sidebar-size=lg]:ltr:md:ml-vertical-menu group-data-[sidebar-size=lg]:rtl:md:mr-vertical-menu group-data-[sidebar-size=md]:ltr:ml-vertical-menu-md group-data-[sidebar-size=md]:rtl:mr-vertical-menu-md group-data-[sidebar-size=sm]:ltr:ml-vertical-menu-sm group-data-[sidebar-size=sm]:rtl:mr-vertical-menu-sm pt-[calc(theme('spacing.header')_*_1)] pb-[calc(theme('spacing.header')_*_0.8)] px-4 group-data-[navbar=bordered]:pt-[calc(theme('spacing.header')_*_1.3)] group-data-[navbar=hidden]:pt-0 group-data-[layout=horizontal]:mx-auto group-data-[layout=horizontal]:max-w-screen-2xl group-data-[layout=horizontal]:px-0 group-data-[layout=horizontal]:group-data-[sidebar-size=lg]:ltr:md:ml-auto group-data-[layout=horizontal]:group-data-[sidebar-size=lg]:rtl:md:mr-auto group-data-[layout=horizontal]:md:pt-[calc(theme('spacing.header')_*_1.6)] group-data-[layout=horizontal]:px-3 group-data-[layout=horizontal]:group-data-[navbar=hidden]:pt-[calc(theme('spacing.header')_*_0.9)]">
    <div class="container-fluid group-data-[content=boxed]:max-w-boxed mx-auto">

        <!-- Breadcrumb -->
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

        <!-- Alertas -->
        @if(session('success') || session('error'))
        <div class="px-4 py-3 mb-4 text-sm border rounded-md {{ session('success') ? 'text-green-500 border-green-200 bg-green-50 dark:bg-green-400/20 dark:border-green-500/50' : 'text-red-500 border-red-200 bg-red-50 dark:bg-red-400/20 dark:border-red-500/50' }} flex items-center justify-between">
            {{ session('success') ?? session('error') }}
            <button type="button" class="text-slate-400 hover:text-slate-600" onclick="this.parentElement.remove()">
                <i data-lucide="x" class="size-4"></i>
            </button>
        </div>
        @endif

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
                    <div class="xl:col-span-2">
                        <label class="inline-block mb-2 text-base font-medium">Año</label>
                        <select id="filtroAnio" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full">
                            @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                                <option value="{{ $y }}" {{ $anio == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                        </select>
                    </div>
                    <div class="xl:col-span-2">
                        <label class="inline-block mb-2 text-base font-medium">Mes</label>
                        <select id="filtroMes" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full">
                            @foreach(['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'] as $idx => $nombre)
                            <option value="{{ $idx + 1 }}" {{ $mes == ($idx + 1) ? 'selected' : '' }}>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Empleado</label>
                        <select id="filtroEmpleado" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full">
                            <option value="">Todos</option>
                            @foreach($empleados as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->apellidos }}, {{ $emp->nombres }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Tipo Movimiento</label>
                        <select id="filtroTipo" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full">
                            <option value="">Todos</option>
                            <option value="sueldo">Sueldo</option>
                            <option value="extra">Extra</option>
                            <option value="vale">Vale</option>
                            <option value="ausencia">Ausencia</option>
                            <option value="llegada_tardia">Llegada Tardía</option>
                            <option value="otros">Otros</option>
                        </select>
                    </div>
                    <div class="xl:col-span-2 flex items-end">
                        <button id="btnFiltrar" class="text-white btn bg-custom-500 border-custom-500 hover:text-white hover:bg-custom-600 hover:border-custom-600 focus:text-white focus:bg-custom-600 focus:border-custom-600 focus:ring focus:ring-custom-100 active:text-white active:bg-custom-600 active:border-custom-600 active:ring active:ring-custom-100 dark:ring-custom-400/20 w-full">
                            <i data-lucide="filter" class="inline-block size-4"></i>
                            <span class="align-middle">Filtrar</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h6 class="text-15">Registro de salarios, extras, vales y descuentos</h6>
                    <button class="text-white btn bg-custom-500 border-custom-500 hover:text-white hover:bg-custom-600 hover:border-custom-600 focus:text-white focus:bg-custom-600 focus:border-custom-600 focus:ring focus:ring-custom-100 active:text-white active:bg-custom-600 active:border-custom-600 active:ring active:ring-custom-100 dark:ring-custom-400/20" data-modal-target="modalNuevoMovimiento" onclick="limpiarModalMovimiento()">
                        <i data-lucide="plus" class="inline-block size-4"></i>
                        <span class="align-middle">Nuevo Movimiento</span>
                    </button>
                </div>

                <div class="-mx-5 overflow-x-auto">
                    <table class="w-full whitespace-nowrap" id="tablaMovimientos">
                        <thead class="ltr:text-left rtl:text-right bg-slate-100 text-slate-500 dark:text-zink-200 dark:bg-zink-600">
                            <tr>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500">FECHA</th>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500">CI</th>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500">EMPLEADO</th>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500">TIPO</th>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500">MONTO</th>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500">NATURALEZA</th>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500">ESTADO</th>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500">OBSERVACIÓN</th>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500 w-[100px]">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo Movimiento -->
<div id="modalNuevoMovimiento" modal-center="" class="fixed flex flex-col hidden transition-all duration-300 ease-in-out left-2/4 z-drawer -translate-x-2/4 -translate-y-2/4">
    <div class="w-screen md:w-[40rem] bg-white shadow rounded-md dark:bg-zink-600">
        <div class="flex items-center justify-between p-4 border-b dark:border-zink-500">
            <h5 class="text-16" id="modalTituloMov">Nuevo Movimiento</h5>
            <button data-modal-close="modalNuevoMovimiento" class="transition-all duration-200 ease-linear text-slate-400 hover:text-red-500">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="max-h-[calc(theme('height.screen')_-_180px)] p-4 overflow-y-auto">
            <form id="formNuevoMovimiento">
                @csrf
                <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
                    <div class="xl:col-span-12">
                        <label class="inline-block mb-2 text-base font-medium">Empleado *</label>
                        <select name="empleado_id" id="selectEmpleado" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" required>
                            <option value="">Seleccione...</option>
                            @foreach($empleados as $emp)
                            <option value="{{ $emp->id }}" data-salario="{{ $emp->salario_base }}">{{ $emp->apellidos }}, {{ $emp->nombres }} (CI: {{ $emp->numero_documento }})</option>
                            @endforeach
                        </select>
                        <div class="hidden mt-1 text-sm text-red-500" id="errorEmpleado">Debe seleccionar un empleado.</div>
                    </div>

                    <div class="xl:col-span-6">
                        <label class="inline-block mb-2 text-base font-medium">Fecha *</label>
                        <input type="date" name="fecha" id="inputFecha" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="xl:col-span-6">
                        <label class="inline-block mb-2 text-base font-medium">Tipo Movimiento *</label>
                        <select name="tipo_movimiento" id="selectTipoMovimiento" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" required>
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
                        <input type="number" name="monto" id="inputMonto" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" min="0" step="1" required value="0">
                    </div>

                    <div class="xl:col-span-12">
                        <label class="inline-block mb-2 text-base font-medium" id="labelObservacion">Observación</label>
                        <textarea name="observacion" id="inputObservacion" rows="2" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" placeholder="Requerido solo para OTROS"></textarea>
                        <div class="hidden mt-1 text-sm text-red-500" id="errorObservacion">La observación es obligatoria para movimientos tipo "Otros".</div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="reset" data-modal-close="modalNuevoMovimiento" class="text-red-500 bg-white btn hover:text-red-500 hover:bg-red-100 focus:text-red-500 focus:bg-red-100 active:text-red-500 active:bg-red-100 dark:bg-zink-600 dark:hover:bg-red-500/10 dark:focus:bg-red-500/10 dark:active:bg-red-500/10">Cancelar</button>
                    <button type="submit" class="text-white btn bg-custom-500 border-custom-500 hover:text-white hover:bg-custom-600 hover:border-custom-600 focus:text-white focus:bg-custom-600 focus:border-custom-600 focus:ring focus:ring-custom-100 active:text-white active:bg-custom-600 active:border-custom-600 active:ring active:ring-custom-100 dark:ring-custom-400/20" id="btnGuardarMovimiento">
                        <span class="hidden mr-2 spinner-border spinner-border-sm" id="spinnerGuardar"></span>
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Anular -->
<div id="modalAnular" modal-center="" class="fixed flex flex-col hidden transition-all duration-300 ease-in-out left-2/4 z-drawer -translate-x-2/4 -translate-y-2/4">
    <div class="w-screen md:w-[35rem] bg-white shadow rounded-md dark:bg-zink-600">
        <div class="max-h-[calc(theme('height.screen')_-_180px)] overflow-y-auto px-6 py-8">
            <div class="float-right">
                <button data-modal-close="modalAnular" class="transition-all duration-200 ease-linear text-slate-500 hover:text-red-500">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="mt-5 text-center">
                <i data-lucide="alert-triangle" class="block h-12 mx-auto text-orange-500"></i>
                <h5 class="mb-1 mt-4">¿Anular movimiento?</h5>
                <p class="text-slate-500 dark:text-zink-200">Esta acción marcará el movimiento como anulado.</p>
                <p class="text-slate-500 dark:text-zink-200 mt-1">Monto: <strong id="anularMonto"></strong></p>
                <div class="flex justify-center gap-2 mt-6">
                    <button data-modal-close="modalAnular" class="bg-white text-slate-500 btn hover:text-slate-500 hover:bg-slate-100 focus:text-slate-500 focus:bg-slate-100 active:text-slate-500 active:bg-slate-100 dark:bg-zink-600 dark:hover:bg-slate-500/10 dark:focus:bg-slate-500/10 dark:active:bg-slate-500/10">Cancelar</button>
                    <button id="btnConfirmarAnular" class="text-white bg-red-500 border-red-500 btn hover:text-white hover:bg-red-600 hover:border-red-600 focus:text-white focus:bg-red-600 focus:border-red-600 focus:ring focus:ring-red-100 active:text-white active:bg-red-600 active:border-red-600 active:ring active:ring-red-100 dark:ring-custom-400/20">Sí, Anular</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const rutaNominaData = "{{ route('nomina.movimientos.data') }}";
    const rutaNominaStore = "{{ route('nomina.movimientos.store') }}";
    const rutaNominaAnularBase = "{{ url('nomina/anular') }}";

    // Montos fijos por tipo
    const MONTOS_FIJOS = {
        'sueldo': null,
        'extra': 500000,
        'vale': 0,
        'ausencia': 0,
        'llegada_tardia': 0,
        'otros': 0,
    };

    let tablaMovimientos;
    let movimientoAnularId = null;

    function limpiarModalMovimiento() {
        document.getElementById('formNuevoMovimiento').reset();
        document.getElementById('inputMonto').value = 0;
        document.getElementById('inputMonto').disabled = false;
        document.getElementById('inputObservacion').required = false;
        document.getElementById('labelObservacion').classList.remove('text-red-500');
        document.getElementById('errorObservacion').classList.add('hidden');
        document.getElementById('errorEmpleado').classList.add('hidden');
        document.getElementById('selectEmpleado').classList.remove('border-red-500');
        document.getElementById('inputObservacion').classList.remove('border-red-500');
    }

    function actualizarMonto() {
        const tipo = document.getElementById('selectTipoMovimiento').value;
        const empleadoOption = document.getElementById('selectEmpleado').selectedOptions[0];
        const inputMonto = document.getElementById('inputMonto');

        if (!tipo) {
            inputMonto.value = 0;
            inputMonto.disabled = false;
            return;
        }

        if (tipo === 'sueldo') {
            const salario = empleadoOption ? empleadoOption.getAttribute('data-salario') : 0;
            if (salario && salario > 0) {
                inputMonto.value = salario;
                inputMonto.disabled = true;
            } else {
                inputMonto.value = 0;
                inputMonto.disabled = false;
            }
        } else if (MONTOS_FIJOS[tipo] !== undefined) {
            inputMonto.value = MONTOS_FIJOS[tipo];
            inputMonto.disabled = (MONTOS_FIJOS[tipo] !== 0);
        }
    }

    function validarObservacion() {
        const tipo = document.getElementById('selectTipoMovimiento').value;
        const label = document.getElementById('labelObservacion');
        const input = document.getElementById('inputObservacion');

        if (tipo === 'otros') {
            input.required = true;
            input.setAttribute('placeholder', 'Ingrese la observación (obligatorio)');
            label.classList.add('text-red-500');
        } else {
            input.required = false;
            input.setAttribute('placeholder', 'Requerido solo para OTROS');
            label.classList.remove('text-red-500');
            input.classList.remove('border-red-500');
            document.getElementById('errorObservacion').classList.add('hidden');
        }
    }

    function abrirModalAnular(id, monto) {
        movimientoAnularId = id;
        document.getElementById('anularMonto').textContent = 'Gs. ' + Number(monto).toLocaleString('es-PY');
        const modal = document.getElementById('modalAnular');
        modal.classList.remove('hidden');
        modal.classList.add('show');
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar DataTable
        if (typeof $.fn !== 'undefined' && $.fn.DataTable) {
            tablaMovimientos = $('#tablaMovimientos').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: rutaNominaData,
                    type: 'GET',
                    data: function(d) {
                        d.anio = document.getElementById('filtroAnio').value;
                        d.mes = document.getElementById('filtroMes').value;
                        d.empleado_id = document.getElementById('filtroEmpleado').value;
                        d.tipo = document.getElementById('filtroTipo').value;
                    },
                    dataSrc: 'data',
                },
                columns: [{
                        data: 'fecha',
                        className: 'px-3.5 py-2.5 border-y border-slate-200 dark:border-zink-500 text-sm text-center'
                    },
                    {
                        data: 'ci',
                        className: 'px-3.5 py-2.5 border-y border-slate-200 dark:border-zink-500 text-sm text-center'
                    },
                    {
                        data: 'empleado',
                        className: 'px-3.5 py-2.5 border-y border-slate-200 dark:border-zink-500 text-sm font-medium'
                    },
                    {
                        data: 'tipo_label',
                        className: 'px-3.5 py-2.5 border-y border-slate-200 dark:border-zink-500 text-sm text-center',
                        render: function(data) {
                            const badges = {
                                'Sueldo': '<span class="px-2.5 py-0.5 text-xs font-medium rounded border bg-blue-100 border-blue-200 text-blue-500 dark:bg-blue-500/20 dark:border-blue-500/20">Sueldo</span>',
                                'Extra': '<span class="px-2.5 py-0.5 text-xs font-medium rounded border bg-cyan-100 border-cyan-200 text-cyan-500 dark:bg-cyan-500/20 dark:border-cyan-500/20">Extra</span>',
                                'Vale': '<span class="px-2.5 py-0.5 text-xs font-medium rounded border bg-yellow-100 border-yellow-200 text-yellow-500 dark:bg-yellow-500/20 dark:border-yellow-500/20">Vale</span>',
                                'Ausencia': '<span class="px-2.5 py-0.5 text-xs font-medium rounded border bg-red-100 border-red-200 text-red-500 dark:bg-red-500/20 dark:border-red-500/20">Ausencia</span>',
                                'Llegada Tardía': '<span class="px-2.5 py-0.5 text-xs font-medium rounded border bg-slate-100 border-slate-200 text-slate-500 dark:bg-slate-500/20 dark:border-slate-500/20 dark:text-zink-200">Llegada Tardía</span>',
                                'Otros': '<span class="px-2.5 py-0.5 text-xs font-medium rounded border bg-purple-100 border-purple-200 text-purple-500 dark:bg-purple-500/20 dark:border-purple-500/20">Otros</span>',
                            };
                            return badges[data] || data;
                        }
                    },
                    {
                        data: 'monto',
                        className: 'px-3.5 py-2.5 border-y border-slate-200 dark:border-zink-500 text-sm text-right font-medium',
                        render: function(data, type, row) {
                            const color = row.es_ingreso ? 'text-green-500' : 'text-red-500';
                            return '<span class="' + color + '">' + data + '</span>';
                        }
                    },
                    {
                        data: 'naturaleza',
                        className: 'px-3.5 py-2.5 border-y border-slate-200 dark:border-zink-500 text-sm text-center',
                        render: function(data) {
                            return data;
                        }
                    },
                    {
                        data: 'estado',
                        className: 'px-3.5 py-2.5 border-y border-slate-200 dark:border-zink-500 text-sm text-center',
                        render: function(data) {
                            return data;
                        }
                    },
                    {
                        data: 'observacion',
                        className: 'px-3.5 py-2.5 border-y border-slate-200 dark:border-zink-500 text-sm text-slate-500 dark:text-zink-200'
                    },
                    {
                        data: null,
                        className: 'px-3.5 py-2.5 border-y border-slate-200 dark:border-zink-500 text-sm text-center',
                        orderable: false,
                        render: function(data, type, row) {
                            if (row.estado_raw === 'anulado') {
                                return '<span class="text-slate-400 dark:text-zink-300 text-xs">Anulado</span>';
                            }
                            return '<button class="flex items-center justify-center transition-all duration-200 ease-linear rounded-md size-8 bg-slate-100 text-slate-500 hover:text-red-500 hover:bg-red-100 dark:bg-zink-600 dark:text-zink-200 dark:hover:text-red-500 dark:hover:bg-red-500/20" onclick="abrirModalAnular(' + row.id + ', ' + row.monto_raw + ')" title="Anular">' +
                                '<i data-lucide="ban" class="size-4"></i></button>';
                        }
                    },
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                    paginate: {
                        previous: '<i class="ri-arrow-left-s-line"></i>',
                        next: '<i class="ri-arrow-right-s-line"></i>'
                    },
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ resultados',
                },
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                order: [
                    [0, 'desc']
                ],
                responsive: true,
                dom: '<"flex items-center justify-between mb-2"<"flex items-center gap-2"l><"f">>' +
                    '<"overflow-x-auto"tr>' +
                    '<"flex items-center justify-between mt-2"<"text-sm text-slate-500 dark:text-zink-200"i><"p">>',
            });
        }

        // Filtros
        document.getElementById('btnFiltrar').addEventListener('click', function() {
            if (tablaMovimientos) tablaMovimientos.ajax.reload();
        });

        // Cambio de empleado o tipo
        document.getElementById('selectEmpleado').addEventListener('change', actualizarMonto);
        document.getElementById('selectTipoMovimiento').addEventListener('change', function() {
            actualizarMonto();
            validarObservacion();
        });

        // Guardar movimiento
        document.getElementById('formNuevoMovimiento').addEventListener('submit', function(e) {
            e.preventDefault();

            const tipo = document.getElementById('selectTipoMovimiento').value;
            const observacion = document.getElementById('inputObservacion').value.trim();
            const empleado = document.getElementById('selectEmpleado').value;

            let hasError = false;

            if (!empleado) {
                document.getElementById('errorEmpleado').classList.remove('hidden');
                document.getElementById('selectEmpleado').classList.add('border-red-500');
                hasError = true;
            } else {
                document.getElementById('errorEmpleado').classList.add('hidden');
                document.getElementById('selectEmpleado').classList.remove('border-red-500');
            }

            if (tipo === 'otros' && observacion === '') {
                document.getElementById('inputObservacion').classList.add('border-red-500');
                document.getElementById('errorObservacion').classList.remove('hidden');
                hasError = true;
            } else {
                document.getElementById('inputObservacion').classList.remove('border-red-500');
                document.getElementById('errorObservacion').classList.add('hidden');
            }

            if (hasError) return;

            const btn = document.getElementById('btnGuardarMovimiento');
            const spinner = document.getElementById('spinnerGuardar');

            btn.disabled = true;
            spinner.classList.remove('hidden');

            const formData = new FormData(this);

            fetch(rutaNominaStore, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(r => r.json())
                .then(response => {
                    if (response.success) {
                        // Cerrar modal
                        const modal = document.getElementById('modalNuevoMovimiento');
                        modal.classList.add('hidden');
                        modal.classList.remove('show');

                        limpiarModalMovimiento();
                        if (tablaMovimientos) tablaMovimientos.ajax.reload();
                    } else {
                        alert(response.message || 'Error al guardar');
                    }
                })
                .catch(err => {
                    alert('Ocurrió un error al guardar el movimiento.');
                    console.error(err);
                })
                .finally(() => {
                    btn.disabled = false;
                    spinner.classList.add('hidden');
                });
        });

        // Confirmar anular
        document.getElementById('btnConfirmarAnular').addEventListener('click', function() {
            if (!movimientoAnularId) return;

            fetch(rutaNominaAnularBase + '/' + movimientoAnularId, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                    },
                })
                .then(r => r.json())
                .then(response => {
                    if (response.success) {
                        const modal = document.getElementById('modalAnular');
                        modal.classList.add('hidden');
                        modal.classList.remove('show');
                        if (tablaMovimientos) tablaMovimientos.ajax.reload();
                    } else {
                        alert(response.message || 'Error al anular');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Error al anular el movimiento.');
                });
        });
    });
</script>
@endsection