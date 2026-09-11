@extends('layouts.master')
@section('title') Liquidaciones Salariales @endsection

@section('content')
<div class="group-data-[sidebar-size=lg]:ltr:md:ml-vertical-menu group-data-[sidebar-size=lg]:rtl:md:mr-vertical-menu group-data-[sidebar-size=md]:ltr:ml-vertical-menu-md group-data-[sidebar-size=md]:rtl:mr-vertical-menu-md group-data-[sidebar-size=sm]:ltr:ml-vertical-menu-sm group-data-[sidebar-size=sm]:rtl:mr-vertical-menu-sm pt-[calc(theme('spacing.header')_*_1)] pb-[calc(theme('spacing.header')_*_0.8)] px-4 group-data-[navbar=bordered]:pt-[calc(theme('spacing.header')_*_1.3)] group-data-[navbar=hidden]:pt-0 group-data-[layout=horizontal]:mx-auto group-data-[layout=horizontal]:max-w-screen-2xl group-data-[layout=horizontal]:px-0 group-data-[layout=horizontal]:group-data-[sidebar-size=lg]:ltr:md:ml-auto group-data-[layout=horizontal]:group-data-[sidebar-size=lg]:rtl:md:mr-auto group-data-[layout=horizontal]:md:pt-[calc(theme('spacing.header')_*_1.6)] group-data-[layout=horizontal]:px-3 group-data-[layout=horizontal]:group-data-[navbar=hidden]:pt-[calc(theme('spacing.header')_*_0.9)]">
    <div class="container-fluid group-data-[content=boxed]:max-w-boxed mx-auto">

        {{-- Breadcrumb --}}
        <div class="flex flex-col gap-2 py-4 md:flex-row md:items-center print:hidden">
            <div class="grow">
                <h5 class="text-16">Liquidaciones Salariales</h5>
            </div>
            <ul class="flex items-center gap-2 text-sm font-normal shrink-0">
                <li class="relative before:content-['\ea54'] before:font-remix ltr:before:-right-1 rtl:before:-left-1 before:absolute before:text-[18px] before:-top-[3px] ltr:pr-4 rtl:pl-4 before:text-slate-400 dark:text-zink-200">
                    <a href="#" class="text-slate-400 dark:text-zink-200">RRHH</a>
                </li>
                <li class="text-slate-700 dark:text-zink-100">Liquidaciones</li>
            </ul>
        </div>

        {{-- Filtros --}}
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
                    <div class="xl:col-span-3 flex items-end">
                        <button id="btnFiltrar" class="text-white btn bg-custom-500 border-custom-500 hover:text-white hover:bg-custom-600 hover:border-custom-600 focus:text-white focus:bg-custom-600 focus:border-custom-600 focus:ring focus:ring-custom-100 active:text-white active:bg-custom-600 active:border-custom-600 active:ring active:ring-custom-100 dark:ring-custom-400/20 w-full">
                            <i data-lucide="filter" class="inline-block size-4"></i>
                            <span class="align-middle">Filtrar</span>
                        </button>
                    </div>
                    <div class="xl:col-span-2 flex items-end">
                        <button data-modal-target="modalGenerar" class="text-white btn bg-custom-500 border-custom-500 hover:text-white hover:bg-custom-600 hover:border-custom-600 focus:text-white focus:bg-custom-600 focus:border-custom-600 focus:ring focus:ring-custom-100 active:text-white active:bg-custom-600 active:border-custom-600 active:ring active:ring-custom-100 dark:ring-custom-400/20 w-full">
                            <i data-lucide="plus" class="inline-block size-4"></i>
                            <span class="align-middle">Generar</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card">
            <div class="card-body">
                <div class="flex items-center">
                    <h6 class="text-15 grow">Liquidaciones del período</h6>
                </div>
                <br>
                <table id="tablaLiquidaciones" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">N°</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">COLABORADOR</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">PERÍODO</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">INGRESOS</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">DESCUENTOS</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">IPS</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">NETO</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">ESTADO</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Generar --}}
<div id="modalGenerar" modal-center="" class="fixed flex flex-col hidden transition-all duration-300 ease-in-out left-2/4 z-drawer -translate-x-2/4 -translate-y-2/4 show">
    <div class="w-screen md:w-[40rem] bg-white shadow rounded-md dark:bg-zink-600">
        <div class="flex items-center justify-between p-4 border-b dark:border-zink-500">
            <h5 class="text-16">Generar Liquidación</h5>
            <button data-modal-close="modalGenerar" class="transition-all duration-200 ease-linear text-slate-400 hover:text-red-500">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="max-h-[calc(theme('height.screen')_-_180px)] p-4 overflow-y-auto">
            <form action="{{ route('liquidacion.generar', ['empleadoId' => ':id']) }}" method="POST" id="formGenerar">
                @csrf
                <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">

                    <div class="xl:col-span-12">
                        <label class="inline-block mb-2 text-base font-medium">Empleado *</label>
                        <select name="empleado_id" id="genEmpleado" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" required>
                            <option value="">Seleccione...</option>
                            @foreach($empleados as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->apellidos }}, {{ $emp->nombres }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="xl:col-span-6">
                        <label class="inline-block mb-2 text-base font-medium">Año *</label>
                        <input type="number" name="anio" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" value="{{ $anio }}" required>
                    </div>

                    <div class="xl:col-span-6">
                        <label class="inline-block mb-2 text-base font-medium">Mes *</label>
                        <select name="mes" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" required>
                            @foreach(['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'] as $idx => $nombre)
                                <option value="{{ $idx + 1 }}" {{ $mes == ($idx + 1) ? 'selected' : '' }}>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="reset" data-modal-close="modalGenerar" class="text-red-500 bg-white btn hover:text-red-500 hover:bg-red-100 focus:text-red-500 focus:bg-red-100 active:text-red-500 active:bg-red-100 dark:bg-zink-600 dark:hover:bg-red-500/10 dark:focus:bg-red-500/10 dark:active:bg-red-500/10">Cancelar</button>
                    <button type="submit" class="text-white btn bg-custom-500 border-custom-500 hover:text-white hover:bg-custom-600 hover:border-custom-600 focus:text-white focus:bg-custom-600 focus:border-custom-600 focus:ring focus:ring-custom-100 active:text-white active:bg-custom-600 active:border-custom-600 active:ring active:ring-custom-100 dark:ring-custom-400/20">Generar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    window.routesLiq = {
        data:    "{{ route('liquidacion.data') }}",
        show:    "{{ route('liquidacion.show', ['id' => ':id']) }}",
        pagar:   "{{ route('liquidacion.pagar', ['id' => ':id']) }}",
        anular:  "{{ route('liquidacion.anular', ['id' => ':id']) }}",
        generar: "{{ route('liquidacion.generar', ['empleadoId' => ':id']) }}",
    };

    $(document).ready(function () {
        const fmt = (n) => new Intl.NumberFormat('es-PY').format(n || 0);

        const tabla = $('#tablaLiquidaciones').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: window.routesLiq.data,
                type: 'GET',
                data: function (d) {
                    d.anio        = $('#filtroAnio').val();
                    d.mes         = $('#filtroMes').val();
                    d.empleado_id = $('#filtroEmpleado').val();
                }
            },
            columns: [
                { data: 'codigo', className: 'text-center' },
                {
                    data: null,
                    render: function (data, type, row) {
                        return `
                            <div class="flex items-center gap-2">
                                <div class="flex items-center justify-center font-medium rounded-full size-9 shrink-0 bg-custom-100 text-custom-700 dark:bg-custom-500/20 dark:text-custom-300">
                                    ${row.iniciales}
                                </div>
                                <div>
                                    <h6 class="mb-0 text-14">${row.colaborador}</h6>
                                    <p class="text-xs text-slate-500 dark:text-zink-200 mb-0">CI: ${row.ci}</p>
                                </div>
                            </div>`;
                    }
                },
                { data: 'periodo', className: 'text-center' },
                { data: 'total_ingresos',   className: 'text-end text-success', render: (d) => fmt(d) },
                { data: 'total_descuentos', className: 'text-end text-danger',  render: (d) => fmt(d) },
                { data: 'total_ips',        className: 'text-end text-warning', render: (d) => fmt(d) },
                { data: 'total_neto',       className: 'text-end fw-semibold text-custom-500', render: (d) => fmt(d) },
                {
                    data: 'estado',
                    className: 'text-center',
                    render: function (d) {
                        const map = {
                            borrador:   'bg-secondary-subtle text-secondary',
                            calculado:  'bg-warning-subtle text-warning',
                            aprobado:   'bg-info-subtle text-info',
                            pagado:     'bg-primary-subtle text-primary',
                            anulado:    'bg-danger-subtle text-danger',
                        };
                        return `<span class="badge ${map[d] || ''}">${d.charAt(0).toUpperCase()+d.slice(1)}</span>`;
                    }
                },
                {
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    render: function (data, type, row) {
                        const urlVer = window.routesLiq.show.replace(':id', row.id);
                        let btns = `
                            <a href="${urlVer}" target="_blank" title="Ver recibo"
                               class="inline-flex items-center justify-center rounded-md size-8 bg-slate-100 text-slate-500 hover:text-custom-500 hover:bg-custom-100 dark:bg-zink-600 dark:text-zink-200 dark:hover:bg-custom-500/20 dark:hover:text-custom-500">
                                <i class="ri-eye-line"></i>
                            </a>`;

                        if (row.estado === 'calculado' || row.estado === 'aprobado') {
                            btns += `
                                <a href="#!" title="Marcar pagado" data-id="${row.id}" data-accion="pagar"
                                   class="btn-accion inline-flex items-center justify-center rounded-md size-8 bg-slate-100 text-slate-500 hover:text-green-500 hover:bg-green-100 dark:bg-zink-600 dark:text-zink-200 dark:hover:bg-green-500/20 dark:hover:text-green-500">
                                    <i class="ri-money-dollar-circle-line"></i>
                                </a>
                                <a href="#!" title="Anular" data-id="${row.id}" data-accion="anular"
                                   class="btn-accion inline-flex items-center justify-center rounded-md size-8 bg-slate-100 text-slate-500 hover:text-red-500 hover:bg-red-100 dark:bg-zink-600 dark:text-zink-200 dark:hover:bg-red-500/20 dark:hover:text-red-500">
                                    <i class="ri-close-circle-line"></i>
                                </a>`;
                        }
                        return `<div class="flex gap-2 justify-center">${btns}</div>`;
                    }
                }
            ],
            language: {
                processing: "Procesando...",
                search: "Buscar:",
                lengthMenu: "Mostrar _MENU_ registros",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                infoEmpty: "Mostrando 0 a 0 de 0 registros",
                infoFiltered: "(filtrado de _MAX_ registros totales)",
                loadingRecords: "Cargando...",
                zeroRecords: "No se encontraron registros",
                emptyTable: "No hay datos disponibles",
                paginate: {
                    first: "Primero",
                    previous: '<i class="ri-arrow-left-s-line"></i>',
                    next: '<i class="ri-arrow-right-s-line"></i>',
                    last: "Último"
                }
            },
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            order: [[0, 'desc']],
            responsive: true
        });

        // Filtros
        $('#btnFiltrar').on('click', () => tabla.ajax.reload());
        $('#filtroAnio, #filtroMes, #filtroEmpleado').on('change', () => tabla.ajax.reload());

        // Generar → construir action según empleado
        $('#genEmpleado').on('change', function () {
            var id  = $(this).val();
            if (!id) return;
            var url = window.routesLiq.generar.replace(':id', id);
            $('#formGenerar').attr('action', url);
        });

        // Acciones pagar / anular
        $(document).on('click', '.btn-accion', function () {
            var id     = $(this).data('id');
            var accion = $(this).data('accion');
            var url    = accion === 'pagar'
                ? window.routesLiq.pagar.replace(':id', id)
                : window.routesLiq.anular.replace(':id', id);

            var msg = accion === 'pagar'
                ? '¿Marcar esta liquidación como pagada?'
                : '¿Anular esta liquidación?';

            if (!confirm(msg)) return;

            $.post(url, { _token: $('meta[name="csrf-token"]').attr('content') })
                .done(function () {
                    flasher.success('Operación realizada.');
                    tabla.ajax.reload(null, false);
                })
                .fail(function () {
                    flasher.error('Error al procesar la acción.');
                });
        });

        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endsection