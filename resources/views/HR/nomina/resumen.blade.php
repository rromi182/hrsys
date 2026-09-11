{{-- resources/views/HR/nomina/resumen.blade.php --}}
@extends('layouts.master')
@section('title') Resumen de Nómina @endsection

@section('content')
<div class="group-data-[sidebar-size=lg]:ltr:md:ml-vertical-menu group-data-[sidebar-size=lg]:rtl:md:mr-vertical-menu group-data-[sidebar-size=md]:ltr:ml-vertical-menu-md group-data-[sidebar-size=md]:rtl:mr-vertical-menu-md group-data-[sidebar-size=sm]:ltr:ml-vertical-menu-sm group-data-[sidebar-size=sm]:rtl:mr-vertical-menu-sm pt-[calc(theme('spacing.header')_*_1)] pb-[calc(theme('spacing.header')_*_0.8)] px-4 group-data-[navbar=bordered]:pt-[calc(theme('spacing.header')_*_1.3)] group-data-[navbar=hidden]:pt-0 group-data-[layout=horizontal]:mx-auto group-data-[layout=horizontal]:max-w-screen-2xl group-data-[layout=horizontal]:px-0 group-data-[layout=horizontal]:group-data-[sidebar-size=lg]:ltr:md:ml-auto group-data-[layout=horizontal]:group-data-[sidebar-size=lg]:rtl:md:mr-auto group-data-[layout=horizontal]:md:pt-[calc(theme('spacing.header')_*_1.6)] group-data-[layout=horizontal]:px-3 group-data-[layout=horizontal]:group-data-[navbar=hidden]:pt-[calc(theme('spacing.header')_*_0.9)]">
    <div class="container-fluid group-data-[content=boxed]:max-w-boxed mx-auto">

        {{-- Breadcrumb --}}
        <div class="flex flex-col gap-2 py-4 md:flex-row md:items-center print:hidden">
            <div class="grow">
                <h5 class="text-16">Resumen de Nómina</h5>
            </div>
            <ul class="flex items-center gap-2 text-sm font-normal shrink-0">
                <li class="relative before:content-['\ea54'] before:font-remix ltr:before:-right-1 rtl:before:-left-1 before:absolute before:text-[18px] before:-top-[3px] ltr:pr-4 rtl:pl-4 before:text-slate-400 dark:text-zink-200">
                    <a href="#" class="text-slate-400 dark:text-zink-200">RRHH</a>
                </li>
                <li class="text-slate-700 dark:text-zink-100">Resumen Nómina</li>
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
                    <div class="xl:col-span-2 flex items-end">
                        <button id="btnFiltrar" class="text-white btn bg-custom-500 border-custom-500 hover:text-white hover:bg-custom-600 hover:border-custom-600 focus:text-white focus:bg-custom-600 focus:border-custom-600 focus:ring focus:ring-custom-100 active:text-white active:bg-custom-600 active:border-custom-600 active:ring active:ring-custom-100 dark:ring-custom-400/20 w-full">
                            <i data-lucide="filter" class="inline-block size-4"></i>
                            <span class="align-middle">Filtrar</span>
                        </button>
                    </div>
                   <div class="xl:col-span-3 flex items-end justify-end gap-2">
    {{-- Excel --}}
    <a id="btnExcel" href="#" title="Exportar a Excel"
       class="flex items-center justify-center transition-all duration-200 ease-linear rounded-md size-9 bg-green-500 text-white hover:bg-green-600 dark:bg-green-500 dark:hover:bg-green-600">
        <i data-lucide="file-spreadsheet" class="size-4"></i>
    </a>

    {{-- CSV --}}
    <a id="btnCsv" href="#" title="Exportar a CSV"
       class="flex items-center justify-center transition-all duration-200 ease-linear rounded-md size-9 bg-custom-500 text-white hover:bg-custom-600 dark:bg-custom-500 dark:hover:bg-custom-600">
        <i data-lucide="download" class="size-4"></i>
    </a>

    {{-- Imprimir Todo --}}
    <button type="button" onclick="window.print()" title="Imprimir listado"
            class="flex items-center justify-center transition-all duration-200 ease-linear rounded-md size-9 bg-slate-600 text-white hover:bg-slate-700 dark:bg-slate-600 dark:hover:bg-slate-700">
        <i data-lucide="printer" class="size-4"></i>
    </button>
</div>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h6 class="text-15">Resumen del Colaborador</h6>
                    <div class="text-right">
                        <p class="text-sm text-slate-500 dark:text-zink-200 mb-1">TOTAL NETO GENERAL</p>
                        <h5 class="text-18 font-bold text-custom-500" id="totalGeneral">Gs. 0</h5>
                    </div>
                </div>

                {{-- Sin wrapper con overflow-x-auto (DataTable maneja el scroll) --}}
                <table id="tablaResumen" class="w-full whitespace-nowrap">
                    <thead class="ltr:text-left rtl:text-right bg-slate-100 text-slate-500 dark:text-zink-200 dark:bg-zink-600">
                        <tr>
                            <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500">COLABORADOR</th>
                            <th class="px-3.5 py-2.5 font-semibold border-y border-slate-200 dark:border-zink-500 text-center">SUELDO</th>
                            <th class="px-3.5 py-2.5 font-semibold border-y border-slate-200 dark:border-zink-500 text-center">EXTRA</th>
                            <th class="px-3.5 py-2.5 font-semibold border-y border-slate-200 dark:border-zink-500 text-center">VALE</th>
                            <th class="px-3.5 py-2.5 font-semibold border-y border-slate-200 dark:border-zink-500 text-center">AUSENCIA</th>
                            <th class="px-3.5 py-2.5 font-semibold border-y border-slate-200 dark:border-zink-500 text-center">LLEGADA TARDÍA</th>
                            <th class="px-3.5 py-2.5 font-semibold border-y border-slate-200 dark:border-zink-500 text-center">OTROS</th>
                            <th class="px-3.5 py-2.5 font-semibold border-y border-slate-200 dark:border-zink-500 text-center">TOTAL NETO</th>
                            <th class="px-3.5 py-2.5 font-semibold border-y border-slate-200 dark:border-zink-500 text-center w-[110px]">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    window.routesResumen = {
        data:    "{{ route('nomina.resumen.data') }}",
        excel:   "{{ route('nomina.resumen.excel') }}",
        csv:     "{{ route('nomina.resumen.csv') }}",
        reporte: "{{ route('nomina.resumen.reporte', ['empleadoId' => ':id']) }}",
    };
</script>
<script>
$(document).ready(function () {

    const fmt = (n) => new Intl.NumberFormat('es-PY').format(n || 0);

    const tabla = $('#tablaResumen').DataTable({
        processing: true,
        serverSide: true,
        scrollX: false,          // ← clave: sin scrollX de DataTable
        autoWidth: false,
        ajax: {
            url: window.routesResumen.data,
            type: 'GET',
            data: function (d) {
                d.anio        = $('#filtroAnio').val();
                d.mes         = $('#filtroMes').val();
                d.empleado_id = $('#filtroEmpleado').val();
            },
            dataSrc: function (json) {
                $('#totalGeneral').text('Gs. ' + fmt(json.totalGeneral));
                return json.data;
            }
        },
        columns: [
            {
                data: null,
                className: 'text-left',
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
            { data: 'sueldo',         className: 'text-center text-success', render: (d) => d > 0 ? fmt(d) : '0' },
            { data: 'extra',          className: 'text-center text-success', render: (d) => d > 0 ? fmt(d) : '0' },
            { data: 'vale',           className: 'text-center text-danger',  render: (d) => d > 0 ? fmt(d) : '0' },
            { data: 'ausencia',       className: 'text-center text-danger',  render: (d) => d > 0 ? fmt(d) : '0' },
            { data: 'llegada_tardia', className: 'text-center text-danger',  render: (d) => d > 0 ? fmt(d) : '0' },
            { data: 'otros',          className: 'text-center',              render: (d) => d > 0 ? fmt(d) : '0' },
            {
                data: 'total_neto',
                className: 'text-center font-bold',
                render: function (d) {
                    const cls = d >= 0 ? 'text-custom-500' : 'text-danger';
                    return `<span class="${cls}">${fmt(d)}</span>`;
                }
            },
            {
                data: null,
                className: 'text-center',
                orderable: false,
                render: function (data, type, row) {
                    const urlVer      = window.routesResumen.reporte.replace(':id', row.empleado_id);
                    const urlImprimir = urlVer + '?print=1';
                    return `
                        <div class="flex gap-2 justify-center">
                            <a href="${urlVer}" target="_blank" title="Ver reporte"
                               class="flex items-center justify-center transition-all duration-200 ease-linear rounded-md size-8 bg-slate-100 text-slate-500 hover:text-custom-500 hover:bg-custom-100 dark:bg-zink-600 dark:text-zink-200 dark:hover:bg-custom-500/20 dark:hover:text-custom-500">
                                <i class="ri-eye-line"></i>
                            </a>
                            <a href="${urlImprimir}" target="_blank" title="Imprimir reporte"
                               class="flex items-center justify-center transition-all duration-200 ease-linear rounded-md size-8 bg-slate-100 text-slate-500 hover:text-green-500 hover:bg-green-100 dark:bg-zink-600 dark:text-zink-200 dark:hover:bg-green-500/20 dark:hover:text-green-500">
                                <i class="ri-printer-line"></i>
                            </a>
                        </div>`;
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
        order: [[0, 'asc']],
        responsive: true
    });

    // Filtros
    $('#btnFiltrar').on('click', function () {
        tabla.ajax.reload();
        actualizarUrlsExport();
    });

    $('#filtroAnio, #filtroMes, #filtroEmpleado').on('change', function () {
        tabla.ajax.reload();
        actualizarUrlsExport();
    });

    function actualizarUrlsExport() {
        const anio = $('#filtroAnio').val();
        const mes  = $('#filtroMes').val();
        const emp  = $('#filtroEmpleado').val();

        $('#btnExcel').attr('href', window.routesResumen.excel + `?anio=${anio}&mes=${mes}` + (emp ? `&empleado_id=${emp}` : ''));
        $('#btnCsv').attr('href',   window.routesResumen.csv   + `?anio=${anio}&mes=${mes}` + (emp ? `&empleado_id=${emp}` : ''));
    }
    actualizarUrlsExport();

    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>
@endsection