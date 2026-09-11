@extends('layouts.master')
@section('title') Dashboard RRHH @endsection

@section('content')
<div class="group-data-[sidebar-size=lg]:ltr:md:ml-vertical-menu group-data-[sidebar-size=lg]:rtl:md:mr-vertical-menu group-data-[sidebar-size=md]:ltr:ml-vertical-menu-md group-data-[sidebar-size=md]:rtl:mr-vertical-menu-md group-data-[sidebar-size=sm]:ltr:ml-vertical-menu-sm group-data-[sidebar-size=sm]:rtl:mr-vertical-menu-sm pt-[calc(theme('spacing.header')_*_1)] pb-[calc(theme('spacing.header')_*_0.8)] px-4 group-data-[navbar=bordered]:pt-[calc(theme('spacing.header')_*_1.3)] group-data-[navbar=hidden]:pt-0 group-data-[layout=horizontal]:mx-auto group-data-[layout=horizontal]:max-w-screen-2xl group-data-[layout=horizontal]:px-0 group-data-[layout=horizontal]:group-data-[sidebar-size=lg]:ltr:md:ml-auto group-data-[layout=horizontal]:group-data-[sidebar-size=lg]:rtl:md:mr-auto group-data-[layout=horizontal]:md:pt-[calc(theme('spacing.header')_*_1.6)] group-data-[layout=horizontal]:px-3 group-data-[layout=horizontal]:group-data-[navbar=hidden]:pt-[calc(theme('spacing.header')_*_0.9)]">
    <div class="container-fluid group-data-[content=boxed]:max-w-boxed mx-auto">

        {{-- ============ BREADCRUMB + FECHA/HORA EN VIVO ============ --}}
        <div class="flex flex-col gap-2 py-4 md:flex-row md:items-center print:hidden">
            <div class="grow">
                <h5 class="text-16">Panel de Recursos Humanos</h5>
                <p class="text-sm text-slate-500 dark:text-zink-200 mt-1">
                    Bienvenido/a de nuevo, aquí está el resumen de tu equipo
                </p>
            </div>

            {{-- Widget de fecha y hora en vivo --}}
            <div class="shrink-0">
                <div class="flex items-center gap-3 px-4 py-2 rounded-md bg-custom-50 dark:bg-custom-500/10 border border-custom-100 dark:border-custom-500/20">
                    <i data-lucide="calendar-clock" class="size-5 text-custom-500"></i>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-custom-600 dark:text-custom-400" id="liveDate">
                            {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}
                        </p>
                        <p class="text-xs text-slate-500 dark:text-zink-200" id="liveTime">
                            {{ now()->format('H:i:s') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============ KPI CARDS ============ --}}
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-4">

            {{-- Colaboradores Activos --}}
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 dark:text-zink-200">Colaboradores Activos</p>
                            <h5 class="mt-2 text-2xl font-bold">{{ $totalEmpleadosActivos }}</h5>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 text-green-500 bg-green-100 rounded-full dark:bg-green-500/20">
                            <i data-lucide="users" class="size-6"></i>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-slate-500 dark:text-zink-200">
                        <span class="text-green-500">↗ {{ $nuevosIngresos }}</span> nuevos este mes
                    </p>
                </div>
            </div>

            {{-- Salario Promedio --}}
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 dark:text-zink-200">Salario Promedio</p>
                            <h5 class="mt-2 text-2xl font-bold">Gs. {{ number_format($salarioPromedio, 0, ',', '.') }}</h5>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 text-blue-500 bg-blue-100 rounded-full dark:bg-blue-500/20">
                            <i data-lucide="wallet" class="size-6"></i>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-slate-500 dark:text-zink-200">Basado en colaboradores activos</p>
                </div>
            </div>

            {{-- Departamentos --}}
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 dark:text-zink-200">Departamentos</p>
                            <h5 class="mt-2 text-2xl font-bold">{{ $totalDepartamentos }}</h5>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 text-purple-500 bg-purple-100 rounded-full dark:bg-purple-500/20">
                            <i data-lucide="building-2" class="size-6"></i>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-slate-500 dark:text-zink-200">Unidades organizativas activas</p>
                </div>
            </div>

            {{-- Cargos --}}
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 dark:text-zink-200">Cargos Registrados</p>
                            <h5 class="mt-2 text-2xl font-bold">{{ $totalCargos }}</h5>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 text-orange-500 bg-orange-100 rounded-full dark:bg-orange-500/20">
                            <i data-lucide="briefcase" class="size-6"></i>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-slate-500 dark:text-zink-200">Total de cargos en el sistema</p>
                </div>
            </div>
        </div>

        {{-- ============ GRÁFICOS ============ --}}
        <div class="grid grid-cols-1 gap-5 mt-5 lg:grid-cols-3">

            {{-- Gráfico 1: Distribución por estado --}}
            <div class="card">
                <div class="card-body">
                    <h6 class="text-15 mb-4">Colaboradores por Estado</h6>
                    <div id="chartEstado" style="min-height: 300px;"></div>
                </div>
            </div>

            {{-- Gráfico 2: Distribución por departamento --}}
            <div class="card lg:col-span-2">
                <div class="card-body">
                    <h6 class="text-15 mb-4">Colaboradores por Departamento</h6>
                    <div id="chartDepartamento" style="min-height: 300px;"></div>
                </div>
            </div>
        </div>

        {{-- ============ TABLA DE COLABORADORES RECIENTES ============ --}}
        <div class="mt-5 card">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h6 class="text-15">Colaboradores Recientes</h6>
                    <a href="{{ route('empleados.index') }}" class="text-sm text-custom-500 hover:text-custom-600">
                        Ver todos →
                    </a>
                </div>

                <table id="colaboradoresTable" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">COLABORADOR</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">CÓDIGO</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">DOCUMENTO</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">DEPARTAMENTO</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">CARGO</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">INGRESO</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">SALARIO</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">ESTADO</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($empleados as $empleado)
                            <tr>
                                <td class="px-3.5 py-2.5 border border-slate-200 dark:border-zink-500">
                                    <div class="flex items-center gap-2">
                                        <div class="flex items-center justify-center w-8 h-8 text-sm font-medium rounded-full bg-custom-100 text-custom-700 dark:bg-custom-500/20 dark:text-custom-300">
                                            {{ strtoupper(substr($empleado->nombres, 0, 1)) }}{{ strtoupper(substr($empleado->apellidos, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="text-sm mb-0">{{ $empleado->apellidos }}, {{ $empleado->nombres }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3.5 py-2.5 border border-slate-200 dark:border-zink-500 text-sm">{{ $empleado->codigo_empleado ?? '-' }}</td>
                                <td class="px-3.5 py-2.5 border border-slate-200 dark:border-zink-500 text-sm">{{ $empleado->tipo_documento ?? '' }} {{ $empleado->numero_documento ?? '' }}</td>
                                <td class="px-3.5 py-2.5 border border-slate-200 dark:border-zink-500 text-sm">{{ $empleado->departamento->nombre ?? '-' }}</td>
                                <td class="px-3.5 py-2.5 border border-slate-200 dark:border-zink-500 text-sm">{{ $empleado->cargo->nombre ?? '-' }}</td>
                                <td class="px-3.5 py-2.5 border border-slate-200 dark:border-zink-500 text-sm">{{ $empleado->fecha_ingreso ? \Carbon\Carbon::parse($empleado->fecha_ingreso)->format('d/m/Y') : '-' }}</td>
                                <td class="px-3.5 py-2.5 border border-slate-200 dark:border-zink-500 text-sm text-end">Gs. {{ number_format($empleado->salario_base ?? 0, 0, ',', '.') }}</td>
                                <td class="px-3.5 py-2.5 border border-slate-200 dark:border-zink-500 text-center">
                                    @php
                                        $estadoClass = match($empleado->estado ?? 'inactivo') {
                                            'activo'     => 'bg-green-100 border-green-200 text-green-500 dark:bg-green-500/20 dark:border-green-500/20',
                                            'vacaciones' => 'bg-blue-100 border-blue-200 text-blue-500 dark:bg-blue-500/20 dark:border-blue-500/20',
                                            'licencia'   => 'bg-yellow-100 border-yellow-200 text-yellow-500 dark:bg-yellow-500/20 dark:border-yellow-500/20',
                                            'suspendido' => 'bg-red-100 border-red-200 text-red-500 dark:bg-red-500/20 dark:border-red-500/20',
                                            default      => 'bg-slate-100 border-slate-200 text-slate-500 dark:bg-slate-500/20 dark:border-slate-500/20 dark:text-zink-200'
                                        };
                                    @endphp
                                    <span class="px-2.5 py-0.5 text-xs inline-block font-medium rounded border {{ $estadoClass }}">
                                        {{ ucfirst($empleado->estado ?? 'Inactivo') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-3.5 py-2.5 border border-slate-200 dark:border-zink-500 text-center">
                                    No hay colaboradores registrados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
{{-- Datos para JS --}}
<script>
    window.dashData = {
        estado:       @json($porEstado),
        departamento: @json($porDepartamento),
    };
</script>

{{-- Reloj en vivo --}}
<script>
    (function actualizarReloj() {
        const dias  = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
        const meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];

        function tick() {
            const now = new Date();
            const dia = dias[now.getDay()];
            const d   = now.getDate();
            const mes = meses[now.getMonth()];
            const anio = now.getFullYear();

            const hh = String(now.getHours()).padStart(2, '0');
            const mm = String(now.getMinutes()).padStart(2, '0');
            const ss = String(now.getSeconds()).padStart(2, '0');

            const elFecha = document.getElementById('liveDate');
            const elHora  = document.getElementById('liveTime');

            if (elFecha) elFecha.textContent = `${dia}, ${d} de ${mes} de ${anio}`;
            if (elHora)  elHora.textContent  = `${hh}:${mm}:${ss}`;
        }

        tick();
        setInterval(tick, 1000);
    })();
</script>

{{-- DataTable + ApexCharts --}}
<script>
$(document).ready(function () {

    // ===== DataTable =====
    $('#colaboradoresTable').DataTable({
        language: {
            processing:     "Procesando...",
            search:         "Buscar:",
            lengthMenu:     "Mostrar _MENU_ registros",
            info:           "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty:      "Mostrando 0 a 0 de 0 registros",
            infoFiltered:   "(filtrado de _MAX_ registros totales)",
            loadingRecords: "Cargando...",
            zeroRecords:    "No se encontraron registros",
            emptyTable:     "No hay datos disponibles",
            paginate: {
                first:    "Primero",
                previous: '<i class="ri-arrow-left-s-line"></i>',
                next:     '<i class="ri-arrow-right-s-line"></i>',
                last:     "Último"
            }
        },
        pageLength: 5,
        lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
        order: [[0, 'asc']],
        responsive: true,
        columnDefs: [{ orderable: false, targets: [7] }]
    });

    // ===== ApexCharts =====
    if (typeof ApexCharts !== 'undefined') {

        // 1) Colaboradores por estado (donut)
        const estadoLabels = Object.keys(window.dashData.estado).map(k => k.charAt(0).toUpperCase() + k.slice(1));
        const estadoSeries = Object.values(window.dashData.estado).map(v => parseInt(v));

        if (estadoSeries.length) {
            new ApexCharts(document.querySelector('#chartEstado'), {
                chart: { type: 'donut', height: 300, fontFamily: 'inherit' },
                series: estadoSeries,
                labels: estadoLabels,
                colors: ['#22c55e', '#3b82f6', '#f59e0b', '#ef4444', '#94a3b8'],
                legend: { position: 'bottom' },
                dataLabels: { enabled: true, formatter: v => Math.round(v) + '%' },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: w => w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                }
                            }
                        }
                    }
                }
            }).render();
        }

        // 2) Colaboradores por departamento (barra horizontal)
        const deptoLabels = Object.keys(window.dashData.departamento);
        const deptoSeries = Object.values(window.dashData.departamento).map(v => parseInt(v));

        if (deptoSeries.length) {
            new ApexCharts(document.querySelector('#chartDepartamento'), {
                chart: { type: 'bar', height: 300, fontFamily: 'inherit', toolbar: { show: false } },
                series: [{ name: 'Colaboradores', data: deptoSeries }],
                xaxis: { categories: deptoLabels },
                colors: ['#3b82f6'],
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 4,
                        distributed: false,
                        barHeight: '60%'
                    }
                },
                dataLabels: { enabled: true, style: { fontSize: '11px' } },
                grid: { borderColor: '#e5e7eb' }
            }).render();
        }
    }

    if (typeof lucide !== 'undefined') lucide.crateIcons?.();
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>
@endsection