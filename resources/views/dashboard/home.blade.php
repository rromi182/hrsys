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
                    Resumen financiero de nómina y movimientos del mes
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

        {{-- ============ KPI CARDS FINANCIERAS ============ --}}
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-4">

            {{-- 1 Neto a pagar del mes --}}
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 dark:text-zink-200">Neto a Pagar ({{ now()->translatedFormat('F') }})</p>
                            <h5 class="mt-2 text-2xl font-bold">Gs. {{ number_format($totalNetoMes, 0, ',', '.') }}</h5>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 text-blue-500 bg-blue-100 rounded-full dark:bg-blue-500/20">
                            <i data-lucide="wallet" class="size-6"></i>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-slate-500 dark:text-zink-200">
                        <span class="text-green-500">↗ Gs. {{ number_format($totalIngresosMes, 0, ',', '.') }}</span> ingresos ·
                        <span class="text-red-500">↘ Gs. {{ number_format($totalDescuentosMes, 0, ',', '.') }}</span> descuentos
                    </p>
                </div>
            </div>

            {{-- 2 Liquidaciones pendientes de pago --}}
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 dark:text-zink-200">Pendientes de Pago</p>
                            <h5 class="mt-2 text-2xl font-bold">{{ $totalPendienteCount }}</h5>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 text-amber-500 bg-amber-100 rounded-full dark:bg-amber-500/20">
                            <i data-lucide="hourglass" class="size-6"></i>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-slate-500 dark:text-zink-200">
                        Por un total de <span class="font-semibold text-amber-500">Gs. {{ number_format($totalPendienteMonto, 0, ',', '.') }}</span>
                    </p>
                </div>
            </div>

            {{-- 3 Aportes IPS del mes --}}
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 dark:text-zink-200">Aportes IPS ({{ now()->translatedFormat('F') }})</p>
                            <h5 class="mt-2 text-2xl font-bold">Gs. {{ number_format($totalIpsMes, 0, ',', '.') }}</h5>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 text-purple-500 bg-purple-100 rounded-full dark:bg-purple-500/20">
                            <i data-lucide="shield-check" class="size-6"></i>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-slate-500 dark:text-zink-200">
                        Costo promedio/colaborador: <span class="font-semibold">Gs. {{ number_format($costoPromedio, 0, ',', '.') }}</span>
                    </p>
                </div>
            </div>

            {{-- 4) Movimientos del mes --}}
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 dark:text-zink-200">Vales / Adelantos ({{ now()->translatedFormat('F') }})</p>
                            <h5 class="mt-2 text-2xl font-bold">Gs. {{ number_format($valesMes, 0, ',', '.') }}</h5>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 text-red-500 bg-red-100 rounded-full dark:bg-red-500/20">
                            <i data-lucide="hand-coins" class="size-6"></i>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-slate-500 dark:text-zink-200">
                        Extras: <span class="text-green-500">+ Gs. {{ number_format($extrasMes, 0, ',', '.') }}</span> ·
                        Desc. por inasistencias: <span class="text-red-500">Gs. {{ number_format($inasistenciasMes, 0, ',', '.') }}</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- ============ GRÁFICOS FINANCIEROS ============ --}}
        <div class="grid grid-cols-1 gap-5 mt-5 lg:grid-cols-3">

            {{-- Gráfico 1: Evolución del neto pagado últimos 6 meses --}}
            <div class="card lg:col-span-2">
                <div class="card-body">
                    <h6 class="text-15 mb-4">Evolución del Neto Pagado (últimos 6 meses)</h6>
                    <div id="chartEvolucion" style="min-height: 300px;"></div>
                </div>
            </div>

            {{-- Gráfico 2: Ingresos vs Descuentos --}}
            <div class="card">
                <div class="card-body">
                    <h6 class="text-15 mb-4">Ingresos vs Descuentos</h6>
                    <div id="chartComparativa" style="min-height: 300px;"></div>
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
        evolucionLabels:  @json($evolucionLabels),
        evolucionSeries:  @json($evolucionSeries),
        compLabels:       @json($compLabels),
        compIngresos:     @json($compIngresos),
        compDescuentos:   @json($compDescuentos),
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

        // 1) Evolución del neto pagado (área)
        if (window.dashData.evolucionSeries.length) {
            new ApexCharts(document.querySelector('#chartEvolucion'), {
                chart: {
                    type: 'area',
                    height: 300,
                    fontFamily: 'inherit',
                    toolbar: { show: false },
                    zoom: { enabled: false }
                },
                series: [{ name: 'Neto pagado', data: window.dashData.evolucionSeries }],
                xaxis: {
                    categories: window.dashData.evolucionLabels,
                    labels: { style: { fontSize: '11px' } }
                },
                colors: ['#3b82f6'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.45,
                        opacityTo: 0.05
                    }
                },
                stroke: { curve: 'smooth', width: 2 },
                dataLabels: { enabled: false },
                yaxis: {
                    labels: {
                        formatter: v => 'Gs. ' + new Intl.NumberFormat('es-PY').format(Math.round(v / 1000000)) + 'M'
                    }
                },
                grid: { borderColor: '#e5e7eb' },
                tooltip: {
                    y: { formatter: v => 'Gs. ' + new Intl.NumberFormat('es-PY').format(v) }
                }
            }).render();
        }

        // 2) Ingresos vs Descuentos (barras agrupadas)
        if (window.dashData.compLabels.length) {
            new ApexCharts(document.querySelector('#chartComparativa'), {
                chart: {
                    type: 'bar',
                    height: 300,
                    fontFamily: 'inherit',
                    toolbar: { show: false }
                },
                series: [
                    { name: 'Ingresos',   data: window.dashData.compIngresos },
                    { name: 'Descuentos', data: window.dashData.compDescuentos }
                ],
                xaxis: {
                    categories: window.dashData.compLabels,
                    labels: { style: { fontSize: '11px' } }
                },
                colors: ['#22c55e', '#ef4444'],
                plotOptions: {
                    bar: {
                        horizontal: false,
                        borderRadius: 4,
                        columnWidth: '55%'
                    }
                },
                dataLabels: { enabled: false },
                legend: { position: 'bottom' },
                yaxis: {
                    labels: {
                        formatter: v => 'Gs. ' + new Intl.NumberFormat('es-PY').format(Math.round(v / 1000000)) + 'M'
                    }
                },
                grid: { borderColor: '#e5e7eb' },
                tooltip: {
                    y: { formatter: v => 'Gs. ' + new Intl.NumberFormat('es-PY').format(v) }
                }
            }).render();
        }
    }

    if (typeof lucide !== 'undefined') lucide.crateIcons?.();
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>
@endsection