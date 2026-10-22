@extends('layouts.master')
@section('title') Dashboard RRHH @endsection

@section('content')
<div class="group-data-[sidebar-size=lg]:ltr:md:ml-vertical-menu group-data-[sidebar-size=lg]:rtl:md:mr-vertical-menu group-data-[sidebar-size=md]:ltr:ml-vertical-menu-md group-data-[sidebar-size=md]:rtl:mr-vertical-menu-md group-data-[sidebar-size=sm]:ltr:ml-vertical-menu-sm group-data-[sidebar-size=sm]:rtl:mr-vertical-menu-sm pt-[calc(theme('spacing.header')_*_1)] pb-[calc(theme('spacing.header')_*_0.8)] px-4 group-data-[navbar=bordered]:pt-[calc(theme('spacing.header')_*_1.3)] group-data-[navbar=hidden]:pt-0 group-data-[layout=horizontal]:mx-auto group-data-[layout=horizontal]:max-w-screen-2xl group-data-[layout=horizontal]:px-0 group-data-[layout=horizontal]:group-data-[sidebar-size=lg]:ltr:md:ml-auto group-data-[layout=horizontal]:group-data-[sidebar-size=lg]:rtl:md:mr-auto group-data-[layout=horizontal]:md:pt-[calc(theme('spacing.header')_*_1.6)] group-data-[layout=horizontal]:px-3 group-data-[layout=horizontal]:group-data-[navbar=hidden]:pt-[calc(theme('spacing.header')_*_0.9)]">
    <div class="container-fluid group-data-[content=boxed]:max-w-boxed mx-auto">

        {{-- ============ HEADER ============ --}}
        <div class="flex flex-col gap-2 py-4 md:flex-row md:items-center print:hidden flex-wrap">
            <div class="grow">
                <h5 class="text-16">Panel de Recursos Humanos</h5>
                <p class="text-sm text-slate-500 dark:text-zink-200 mt-1">
                    Resumen financiero del período actual
                </p>
            </div>

            {{-- Filtros de Período --}}
            <div class="shrink-0 mt-2 md:mt-0">
                <form method="GET" action="{{ route('home') }}" id="formFiltroPeriodo" class="flex items-center gap-2">
                    <select name="mes" onchange="this.form.submit()"
                        class="form-input form-input-sm w-36 text-sm font-medium bg-slate-50 hover:bg-white border-slate-200 dark:bg-zink-700/50 dark:border-zink-500 rounded-lg shadow-sm cursor-pointer transition-colors">
                        @foreach (['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'] as $idx => $m)
                            <option value="{{ $idx + 1 }}" {{ $mes == $idx + 1 ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                    <select name="anio" onchange="this.form.submit()"
                        class="form-input form-input-sm w-24 text-sm font-medium bg-slate-50 hover:bg-white border-slate-200 dark:bg-zink-700/50 dark:border-zink-500 rounded-lg shadow-sm cursor-pointer transition-colors">
                        @for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}" {{ $anio == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </form>
            </div>

            <div class="shrink-0">
                <a href="{{ route('home.exportar_desglose', ['anio' => $anio, 'mes' => $mes]) }}"
                    class="text-white btn bg-green-500 border-green-500 hover:text-white hover:bg-green-600 hover:border-green-600 focus:ring focus:ring-green-100 shadow-md">
                    <i data-lucide="file-spreadsheet" class="inline-block size-4"></i>
                    <span class="align-middle">Movimientos {{ $nombreMes }}</span>
                </a>
            </div>
            <div class="shrink-0 flex items-center gap-3">
                <div class="flex items-center gap-3 px-4 py-2 rounded-md bg-green-50 dark:bg-green-500/10 border border-green-100 dark:border-green-500/20">
                    <i data-lucide="users" class="size-5 text-green-600 dark:text-green-400"></i>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-green-700 dark:text-green-400">
                            {{ $totalEmpleadosActivos }} Colaboradores
                        </p>
                        <p class="text-xs text-slate-500 dark:text-zink-200">{{ $nuevosIngresos }} nuevos este mes</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 px-4 py-2 rounded-md bg-custom-50 dark:bg-custom-500/10 border border-custom-100 dark:border-custom-500/20">
                    <i data-lucide="calendar-clock" class="size-5 text-custom-500"></i>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-custom-600 dark:text-custom-400" id="liveDate">
                            {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}
                        </p>
                        <p class="text-xs text-slate-500 dark:text-zink-200" id="liveTime">{{ now()->format('H:i:s') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============ KPIs FINANCIEROS ============ --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            {{-- 1) Neto --}}
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 dark:text-zink-200 text-sm">Neto a Pagar</p>
                            <h5 class="mt-1 text-xl font-bold">Gs. {{ number_format($totalNetoMes, 0, ',', '.') }}</h5>
                        </div>
                        <div class="flex items-center justify-center w-10 h-10 text-blue-500 bg-blue-100 rounded-full dark:bg-blue-500/20">
                            <i data-lucide="wallet" class="size-5"></i>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500 dark:text-zink-200">
                        Bruto: <span class="font-semibold">Gs. {{ number_format($totalDevengado, 0, ',', '.') }}</span>
                        · −IPS: <span class="font-semibold text-purple-500">Gs. {{ number_format($ipsTrabajador, 0, ',', '.') }}</span>
                    </p>
                </div>
            </div>

            {{-- 2) Pendientes --}}
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 dark:text-zink-200 text-sm">Liquidaciones Pendientes ({{ $nombreMes }})</p>
                            <h5 class="mt-1 text-xl font-bold">{{ $totalPendienteCount }}</h5>
                        </div>
                        <div class="flex items-center justify-center w-10 h-10 text-amber-500 bg-amber-100 rounded-full dark:bg-amber-500/20">
                            <i data-lucide="hourglass" class="size-5"></i>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500 dark:text-zink-200">
                        Monto estimado: <span class="font-semibold text-amber-500">Gs. {{ number_format($totalPendienteMonto, 0, ',', '.') }}</span>
                    </p>
                </div>
            </div>

            {{-- 3) IPS --}}
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 dark:text-zink-200 text-sm">Total Aporte IPS</p>
                            <h5 class="mt-1 text-xl font-bold">Gs. {{ number_format($ipsTrabajador + $ipsEmpleador, 0, ',', '.') }}</h5>
                        </div>
                        <div class="flex items-center justify-center w-10 h-10 text-emerald-500 bg-emerald-100 rounded-full dark:bg-emerald-500/20">
                            <i data-lucide="shield-plus" class="size-5"></i>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500 dark:text-zink-200">
                        Trab. ({{ $pctIpsTrabajador }}%): <span class="font-semibold">Gs. {{ number_format($ipsTrabajador, 0, ',', '.') }}</span>
                        · Empl. ({{ $pctIpsEmpleador }}%): <span class="font-semibold text-purple-500">Gs. {{ number_format($ipsEmpleador, 0, ',', '.') }}</span>
                    </p>
                </div>
            </div>

            {{-- 4) Costo total --}}
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 dark:text-zink-200 text-sm">Costo Total Empresa</p>
                            <h5 class="mt-1 text-xl font-bold">Gs. {{ number_format($costoTotalEmpresa, 0, ',', '.') }}</h5>
                        </div>
                        <div class="flex items-center justify-center w-10 h-10 text-amber-500 bg-amber-100 rounded-full dark:bg-amber-500/20">
                            <i data-lucide="building-2" class="size-5"></i>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500 dark:text-zink-200">
                        Costo prom./colab: <span class="font-semibold">Gs. {{ number_format($costoPromedio, 0, ',', '.') }}</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- ============ RESUMEN DEMOGRÁFICO (4 mini-cards) ============ --}}
        <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-2 lg:grid-cols-4">

            {{-- Edad promedio --}}
            <div class="card">
                <div class="card-body py-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-500 dark:text-zink-200">Edad Promedio</p>
                            <h5 class="mt-1 text-lg font-bold">{{ $edadPromedio }} años</h5>
                        </div>
                        <div class="flex items-center justify-center w-9 h-9 text-indigo-500 bg-indigo-100 rounded-full dark:bg-indigo-500/20">
                            <i data-lucide="cake" class="size-4"></i>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-slate-400">Rango: {{ $edadMinima }} a {{ $edadMaxima }} años</p>
                </div>
            </div>

            {{-- Antigüedad Promedio --}}
            <div class="card">
                <div class="card-body py-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-500 dark:text-zink-200">Antigüedad Promedio</p>
                            <h5 class="mt-1 text-lg font-bold">{{ $antiguedadPromedio }} años</h5>
                        </div>
                        <div class="flex items-center justify-center w-9 h-9 text-teal-500 bg-teal-100 rounded-full dark:bg-teal-500/20">
                            <i data-lucide="hourglass" class="size-4"></i>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-slate-400">Tiempo promedio en la empresa</p>
                </div>
            </div>

            {{-- Ausencias del mes --}}
            <div class="card">
                <div class="card-body py-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-500 dark:text-zink-200">Ausencias del Mes</p>
                            <h5 class="mt-1 text-lg font-bold">{{ $totalAusenciasMes }}</h5>
                        </div>
                        <div class="flex items-center justify-center w-9 h-9 text-red-500 bg-red-100 rounded-full dark:bg-red-500/20">
                            <i data-lucide="calendar-x" class="size-4"></i>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-slate-400">
                        Descuento: <span class="font-semibold text-red-500">Gs. {{ number_format($montoAusenciasMes, 0, ',', '.') }}</span>
                        · Rango + afectado: <span class="font-semibold text-amber-600">{{ $rangoMasAusencias }}</span>
                    </p>
                </div>
            </div>

            {{-- Exportar --}}
            <div class="card">
                <div class="card-body py-3 flex flex-col justify-center">
                    <p class="text-xs text-slate-500 dark:text-zink-200 mb-2">Reporte Demográfico (PDF)</p>
                    <button type="button" onclick="exportarPerfilDemografico()"
                        class="btn bg-custom-500 text-white hover:bg-custom-600 w-full text-sm">
                        <i data-lucide="file-down" class="inline-block size-4"></i>
                        <span class="align-middle ml-1">Exportar</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ============ GRÁFICOS DEMOGRÁFICOS ============ --}}
        <div class="grid grid-cols-1 gap-4 mt-4 lg:grid-cols-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-sm font-semibold mb-2">Distribución por Edad</h6>
                    <div id="chartRangosEdad" style="min-height: 240px;"></div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h6 class="text-sm font-semibold mb-2">Composición por Sexo</h6>
                    <div id="chartSexo" style="min-height: 240px;"></div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h6 class="text-sm font-semibold mb-2">Ausencias por Rango de Edad</h6>
                    <div id="chartAusenciasEdad" style="min-height: 240px;"></div>
                </div>
            </div>
        </div>

        {{-- ============ GRÁFICOS FINANCIEROS ============ --}}
        <div class="grid grid-cols-1 gap-4 mt-4 lg:grid-cols-3">
            <div class="card lg:col-span-2">
                <div class="card-body">
                    <h6 class="text-sm font-semibold mb-2">Evolución del Neto Pagado (6 meses)</h6>
                    <div id="chartEvolucion" style="min-height: 280px;"></div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h6 class="text-sm font-semibold mb-2">Devengado vs Descuentos</h6>
                    <div id="chartComparativa" style="min-height: 280px;"></div>
                </div>
            </div>
        </div>
        
    </div>
</div>

{{-- Logo oculto para el PDF --}}
<img id="logoEmpresa" src="{{ asset('assets/images/logo-tit-BN.jpeg') }}" style="display:none" alt="">
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
    window.dashData = {
        evolucionLabels: @json($evolucionLabels),
        evolucionSeries: @json($evolucionSeries),
        compLabels: @json($compLabels),
        compIngresos: @json($compIngresos),
        compDescuentos: @json($compDescuentos),
    };
    window.demoData = {
        rangosEdad: @json($rangosEdad),
        porSexo: @json($porSexo),
        rangosAntiguedad: @json($rangosAntiguedad),
        edadPromedio: @json($edadPromedio),
        antiguedadPromedio: @json($antiguedadPromedio),
        totalEmpleados: @json($totalEmpleadosActivos),
        perfilPorDepartamento: @json($perfilPorDepartamento),
        fechaReporte: @json(now()->translatedFormat('d/m/Y H:i')),
        ausenciasPorEdad: @json($ausenciasPorEdad),
        totalAusenciasMes: @json($totalAusenciasMes),
        montoAusenciasMes: @json($montoAusenciasMes),
        empleadosConAusencia: @json($empleadosConAusenciaCount),
        rangoMasAusencias: @json($rangoMasAusencias),
        cantidadRangoMas: @json($cantidadRangoMasAusencias),
    };
</script>

{{-- Reloj en vivo --}}
<script>
    (function actualizarReloj() {
        const dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
        const meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        function tick() {
            const now = new Date();
            const elFecha = document.getElementById('liveDate');
            const elHora = document.getElementById('liveTime');
            if (elFecha) elFecha.textContent = `${dias[now.getDay()]}, ${now.getDate()} de ${meses[now.getMonth()]} de ${now.getFullYear()}`;
            if (elHora) elHora.textContent = `${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')}:${String(now.getSeconds()).padStart(2,'0')}`;
        }
        tick();
        setInterval(tick, 1000);
    })();
</script>

{{-- ApexCharts --}}
<script>
$(document).ready(function() {
    if (typeof ApexCharts !== 'undefined') {
        // Evolución del neto
        if (window.dashData.evolucionSeries.length) {
            new ApexCharts(document.querySelector('#chartEvolucion'), {
                chart: { type: 'area', height: 280, fontFamily: 'inherit', toolbar: { show: false }, zoom: { enabled: false } },
                series: [{ name: 'Neto pagado', data: window.dashData.evolucionSeries }],
                xaxis: { categories: window.dashData.evolucionLabels, labels: { style: { fontSize: '11px' } } },
                colors: ['#3b82f6'],
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05 } },
                stroke: { curve: 'smooth', width: 2 },
                dataLabels: { enabled: false },
                yaxis: { labels: { formatter: v => 'Gs. ' + new Intl.NumberFormat('es-PY').format(Math.round(v / 1000000)) + 'M' } },
                grid: { borderColor: '#e5e7eb' },
                tooltip: { y: { formatter: v => 'Gs. ' + new Intl.NumberFormat('es-PY').format(v) } }
            }).render();
        }

        // Devengado vs Descuentos
        if (window.dashData.compLabels.length) {
            new ApexCharts(document.querySelector('#chartComparativa'), {
                chart: { type: 'bar', height: 280, fontFamily: 'inherit', toolbar: { show: false } },
                series: [
                    { name: 'Devengado',  data: window.dashData.compIngresos },
                    { name: 'Descuentos', data: window.dashData.compDescuentos }
                ],
                xaxis: { categories: window.dashData.compLabels, labels: { style: { fontSize: '11px' } } },
                colors: ['#22c55e', '#ef4444'],
                plotOptions: { bar: { horizontal: false, borderRadius: 4, columnWidth: '55%' } },
                dataLabels: { enabled: false },
                legend: { position: 'bottom' },
                yaxis: { labels: { formatter: v => 'Gs. ' + new Intl.NumberFormat('es-PY').format(Math.round(v / 1000000)) + 'M' } },
                grid: { borderColor: '#e5e7eb' },
                tooltip: { y: { formatter: v => 'Gs. ' + new Intl.NumberFormat('es-PY').format(v) } }
            }).render();
        }

        // Distribución por edad
        new ApexCharts(document.querySelector('#chartRangosEdad'), {
            chart: { type: 'bar', height: 240, fontFamily: 'inherit', toolbar: { show: false } },
            series: [{ name: 'Colaboradores', data: Object.values(window.demoData.rangosEdad) }],
            xaxis: { categories: Object.keys(window.demoData.rangosEdad) },
            colors: ['#6366f1'],
            plotOptions: { bar: { borderRadius: 6, columnWidth: '50%' } },
            dataLabels: { enabled: true, style: { fontWeight: 600 } },
            tooltip: { y: { formatter: v => v + ' colaborador(es)' } }
        }).render();

        // Sexo
        const sx = window.demoData.porSexo;
        new ApexCharts(document.querySelector('#chartSexo'), {
            chart: { type: 'donut', height: 240, fontFamily: 'inherit' },
            series: [sx.F, sx.M, sx.OTRO],
            labels: ['Femenino', 'Masculino', 'Otro'],
            colors: ['#ec4899', '#3b82f6', '#94a3b8'],
            legend: { position: 'bottom' },
            dataLabels: { enabled: true, formatter: v => Math.round(v) + '%' }
        }).render();

        // Ausencias por rango de edad
        // Si hay datos "Sin dato", los mostramos para que la suma cuadre con el total
        const a = window.demoData.ausenciasPorEdad;
        let labelsA = Object.keys(a);
        let valuesA = labelsA.map(k => a[k]);
        
        // Ocultamos "Sin dato" SOLO si su valor es 0
        if (a['Sin dato'] === 0) {
            labelsA = labelsA.filter(k => k !== 'Sin dato');
            valuesA = labelsA.map(k => a[k]);
        }

        new ApexCharts(document.querySelector('#chartAusenciasEdad'), {
            chart: { type: 'bar', height: 240, fontFamily: 'inherit', toolbar: { show: false } },
            series: [{ name: 'Ausencias', data: valuesA }],
            xaxis: { categories: labelsA },
            colors: ['#ef4444'],
            plotOptions: { bar: { borderRadius: 6, columnWidth: '50%' } },
            dataLabels: { enabled: true, style: { fontWeight: 600 }, formatter: v => v > 0 ? v : '' },
            tooltip: { y: { formatter: v => v + ' ausencia(s)' } },
            grid: { borderColor: '#e5e7eb' }
        }).render();
    }

    if (typeof lucide !== 'undefined') lucide.createIcons();
});

// ===== Exportar PDF =====
window.exportarPerfilDemografico = function() {
    const jsPDF = (window.jspdf && window.jspdf.jsPDF) || window.jsPDF;
    if (!jsPDF) {
        alert('Error: jsPDF no se ha cargado correctamente. Recargá la página.');
        return;
    }

    const d = window.demoData;
    const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
    const W = doc.internal.pageSize.getWidth();
    const M = 15;
    let y = M;

    const logo = document.getElementById('logoEmpresa');
    if (logo && logo.complete && logo.naturalWidth > 0) {
        try { doc.addImage(logo, 'PNG', M, y, 30, 15); } catch (e) {}
    }
    
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(16);
    doc.text('Reporte Demográfico de Plantilla', W - M, y + 7, { align: 'right' });
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(9);
    doc.setTextColor(100);
    doc.text('Generado: ' + d.fechaReporte, W - M, y + 13, { align: 'right' });

    y += 22;
    doc.setDrawColor(200);
    doc.line(M, y, W - M, y);
    y += 8;

    // Resumen general
    doc.setTextColor(0);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(12);
    doc.text('1. Resumen General', M, y); y += 7;
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(10);
    doc.text(`Total colaboradores activos: ${d.totalEmpleados}`, M, y); y += 6;
    doc.text(`Edad promedio: ${d.edadPromedio} años`, M, y); y += 6;
    doc.text(`Antigüedad promedio: ${d.antiguedadPromedio} años`, M, y); y += 6;
    doc.text(`Ausencias del mes: ${d.totalAusenciasMes} evento(s) en ${d.empleadosConAusencia} colaborador(es)`, M, y); y += 6;
    doc.text(`Monto descontado por ausencias: Gs. ${new Intl.NumberFormat('es-PY').format(d.montoAusenciasMes)}`, M, y); y += 6;
    doc.text(`Rango etario con más ausencias: ${d.rangoMasAusencias} (${d.cantidadRangoMas} eventos)`, M, y); y += 10;

    // Sexo
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(12);
    doc.text('2. Distribución por Sexo', M, y); y += 7;
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(10);
    const totalSexo = (d.porSexo.F + d.porSexo.M + d.porSexo.OTRO) || 1;
    const pct = n => Math.round((n / totalSexo) * 100);
    doc.text(`Femenino:  ${d.porSexo.F} (${pct(d.porSexo.F)}%)`, M, y); y += 6;
    doc.text(`Masculino: ${d.porSexo.M} (${pct(d.porSexo.M)}%)`, M, y); y += 6;
    if (d.porSexo.OTRO > 0) { doc.text(`Otro:      ${d.porSexo.OTRO} (${pct(d.porSexo.OTRO)}%)`, M, y); y += 6; }
    y += 4;

    // Rango de edad
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(12);
    doc.text('3. Distribución por Rango de Edad', M, y); y += 7;
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(10);
    Object.entries(d.rangosEdad).forEach(([k, v]) => {
        doc.text(`• ${k} años:  ${v} colaborador(es)`, M + 2, y); y += 6;
    });
    y += 4;

    // Ausencias por rango de edad
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(12);
    doc.text('4. Ausencias por Rango de Edad (mes actual)', M, y); y += 7;
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(10);
    Object.entries(d.ausenciasPorEdad || {}).forEach(([k, v]) => {
        if (k === 'Sin dato' && v === 0) return;
        doc.text(`• ${k} años:  ${v} ausencia(s)`, M + 2, y); y += 6;
    });
    y += 4;

    // Antigüedad
    if (y > 230) { doc.addPage(); y = M; }
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(12);
    doc.text('5. Distribución por Antigüedad', M, y); y += 7;
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(10);
    Object.entries(d.rangosAntiguedad).forEach(([k, v]) => {
        doc.text(`• ${k}:  ${v} colaborador(es)`, M + 2, y); y += 6;
    });
    y += 6;

    // Tabla por departamento
    if (y > 230) { doc.addPage(); y = M; }
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(12);
    doc.text('6. Perfil por Departamento', M, y); y += 7;
    doc.setFillColor(240, 240, 240);
    doc.rect(M, y - 4, W - 2 * M, 7, 'F');
    doc.setFontSize(9);
    doc.text('Departamento', M + 2, y);
    doc.text('Total', M + 70, y, { align: 'right' });
    doc.text('Edad prom.', M + 95, y, { align: 'right' });
    doc.text('F', M + 118, y, { align: 'right' });
    doc.text('M', M + 135, y, { align: 'right' });
    y += 7;
    doc.setFont('helvetica', 'normal');
    d.perfilPorDepartamento.forEach(p => {
        if (y > 275) { doc.addPage(); y = M; }
        doc.text(String(p.departamento).substring(0, 40), M + 2, y);
        doc.text(String(p.total), M + 70, y, { align: 'right' });
        doc.text(p.edad_prom + ' años', M + 95, y, { align: 'right' });
        doc.text(String(p.mujeres), M + 118, y, { align: 'right' });
        doc.text(String(p.hombres), M + 135, y, { align: 'right' });
        y += 6;
    });

    const pages = doc.internal.getNumberOfPages();
    for (let i = 1; i <= pages; i++) {
        doc.setPage(i);
        doc.setFontSize(8);
        doc.setTextColor(150);
        doc.text(`Reporte demográfico — Página ${i} de ${pages} — Datos agregados, sin identificación individual`, W / 2, 290, { align: 'center' });
    }

    doc.save(`reporte_demografico_${new Date().toISOString().slice(0,10)}.pdf`);
};
</script>
@endsection