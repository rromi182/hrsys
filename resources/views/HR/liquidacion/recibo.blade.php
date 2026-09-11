<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Sueldo - {{ $liquidacion->empleado->nombres }} {{ $liquidacion->empleado->apellidos }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
            padding: 15px;
            background: #f4f4f4;
        }

        .container {
            max-width: 780px;
            margin: 0 auto;
            background: #fff;
            border: 1.5px solid #000;
            padding: 16px 18px;
        }

        /* ============ HEADER ============ */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1.5px solid #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
            gap: 15px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
        }

        .header-left img {
            height: 62px;
            width: auto;
            object-fit: contain;
        }

        .header-left .empresa-info h2 {
            font-size: 15px;
            margin-bottom: 2px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header-left .empresa-info p {
            font-size: 10px;
            line-height: 1.35;
            color: #222;
        }

        .header-right {
            border: 1.5px solid #000;
            padding: 6px 10px;
            text-align: center;
            min-width: 200px;
            background: #f9f9f9;
        }

        .header-right h3 {
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .header-right p {
            font-size: 10px;
            line-height: 1.4;
        }

        .header-right .nro {
            font-size: 12px;
            font-weight: bold;
        }

        /* ============ DATOS COLABORADOR ============ */
        .colaborador {
            border: 1px solid #666;
            padding: 6px 10px;
            margin-bottom: 10px;
            background: #fafafa;
        }

        .colaborador table {
            width: 100%;
            border-collapse: collapse;
        }

        .colaborador td {
            padding: 2px 4px;
            font-size: 10.5px;
            vertical-align: top;
        }

        .colaborador td.label {
            font-weight: bold;
            width: 90px;
            color: #333;
        }

        /* ============ TABLA DE CONCEPTOS ============ */
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        table.items th,
        table.items td {
            border: 1px solid #666;
            padding: 4px 6px;
            font-size: 10.5px;
        }

        table.items thead th {
            background: #e8e8e8;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }

        table.items td.right {
            text-align: right;
        }

        table.items td.center {
            text-align: center;
        }

        .section-title {
            background: #d9d9d9 !important;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.3px;
        }

        .section-ingreso   { background: #e8f5e9 !important; }
        .section-descuento { background: #ffebee !important; }
        .section-aporte    { background: #e3f2fd !important; }

        /* ============ TOTALES ============ */
        .totales {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 14px;
        }

        .totales table {
            width: 340px;
            border-collapse: collapse;
        }

        .totales td {
            border: 1px solid #666;
            padding: 5px 8px;
            font-size: 11px;
        }

        .totales td:first-child {
            background: #f0f0f0;
            font-weight: bold;
        }

        .totales td.right {
            text-align: right;
        }

        .totales tr.neto td {
            background: #000;
            color: #fff;
            font-size: 12.5px;
            font-weight: bold;
            padding: 7px 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* ============ FIRMAS ============ */
        .firmas {
            display: flex;
            justify-content: space-around;
            margin-top: 45px;
            margin-bottom: 10px;
        }

        .firmas div {
            border-top: 1px solid #000;
            width: 200px;
            text-align: center;
            padding-top: 5px;
            font-size: 10px;
        }

        .firmas div small {
            display: block;
            color: #555;
            font-size: 9px;
        }

        /* ============ PIE ============ */
        .footer {
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 6px;
            margin-top: 8px;
        }

        /* ============ ACCIONES (no imprimibles) ============ */
        .no-print {
            text-align: center;
            margin: 15px 0;
        }

        .no-print button,
        .no-print a {
            padding: 8px 18px;
            margin: 0 4px;
            cursor: pointer;
            font-size: 12px;
            border: 1px solid #333;
            background: #fff;
            text-decoration: none;
            color: #000;
            border-radius: 4px;
        }

        .no-print button:hover,
        .no-print a:hover {
            background: #333;
            color: #fff;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .container { border: none; padding: 0; max-width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="container">

    {{-- ============ ENCABEZADO ============ --}}
    <div class="header">
        <div class="header-left">
            <img src="{{ asset('assets/images/logo-tit-2.jpg') }}" alt="Logo">
            <div class="empresa-info">
                <h2>{{ $liquidacion->empresa->nombre ?? 'MI EMPRESA S.A.' }}</h2>
                <p>RUC: {{ $liquidacion->empresa->ruc ?? '-' }}</p>
                <p>{{ $liquidacion->empresa->direccion ?? '-' }}</p>
                @if($liquidacion->empresa->telefono)
                    <p>Tel: {{ $liquidacion->empresa->telefono }}</p>
                @endif
            </div>
        </div>
        <div class="header-right">
            <h3>Recibo de Sueldo</h3>
            <p class="nro">Nº {{ str_pad($liquidacion->id, 6, '0', STR_PAD_LEFT) }}</p>
            <p>Fecha: {{ now()->format('d/m/Y') }}</p>
            <p>Período: {{ str_pad($liquidacion->periodo_mes, 2, '0', STR_PAD_LEFT) }}/{{ $liquidacion->periodo_anio }}</p>
        </div>
    </div>

    {{-- ============ DATOS DEL COLABORADOR ============ --}}
    <div class="colaborador">
        <table>
            <tr>
                <td class="label">Nombre:</td>
                <td>{{ $liquidacion->empleado->nombres }} {{ $liquidacion->empleado->apellidos }}</td>
                <td class="label">C.I.:</td>
                <td>{{ $liquidacion->empleado->numero_documento }}</td>
                <td class="label">Código:</td>
                <td>{{ $liquidacion->empleado->codigo_empleado }}</td>
            </tr>
            <tr>
                <td class="label">Cargo:</td>
                <td>{{ $liquidacion->empleado->cargo->nombre ?? '-' }}</td>
                <td class="label">Ingreso:</td>
                <td>{{ \Carbon\Carbon::parse($liquidacion->empleado->fecha_ingreso)->format('d/m/Y') }}</td>
                <td class="label">IPS:</td>
                <td>{{ $liquidacion->empleado->numero_ips ?? '-' }}</td>
            </tr>
        </table>
    </div>

    {{-- ============ CONCEPTOS EN DOS COLUMNAS ============ --}}
    @php
        $ingresos   = $liquidacion->detalles->where('tipo', 'ingreso');
        $descuentos = $liquidacion->detalles->where('tipo', 'descuento');
        $aportes    = $liquidacion->detalles->where('tipo', 'aporte');

        // Combinamos descuentos y aportes para la columna derecha
        $deducciones = $descuentos->concat($aportes);

        $maxFilas = max($ingresos->count(), $deducciones->count());
    @endphp

    <table class="items">
        <thead>
            <tr>
                <th style="width: 55%;">INGRESOS</th>
                <th style="width: 15%;">MONTO (Gs.)</th>
                <th style="width: 15%;">DESCUENTOS / APORTES</th>
                <th style="width: 15%;">MONTO (Gs.)</th>
            </tr>
        </thead>
        <tbody>
            @for($i = 0; $i < $maxFilas; $i++)
                @php
                    $ing = $ingresos->values()->get($i);
                    $ded = $deducciones->values()->get($i);
                @endphp
                <tr>
                    <td>{{ $ing->concepto->nombre ?? ($ing->formula_aplicada ?? '') }}</td>
                    <td class="right">{{ $ing ? number_format($ing->monto, 0, ',', '.') : '' }}</td>
                    <td>{{ $ded->concepto->nombre ?? ($ded->formula_aplicada ?? '') }}</td>
                    <td class="right">{{ $ded ? number_format($ded->monto, 0, ',', '.') : '' }}</td>
                </tr>
            @endfor
        </tbody>
    </table>

    {{-- ============ TOTALES ============ --}}
    <div class="totales">
        <table>
            <tr>
                <td>Total Ingresos:</td>
                <td class="right">Gs. {{ number_format($liquidacion->total_ingresos, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total Descuentos:</td>
                <td class="right">- Gs. {{ number_format($liquidacion->total_descuentos, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Aporte IPS ({{ number_format($liquidacion->detalles->where('tipo','aporte')->first()?->cantidad ?? 9, 0) }}%):</td>
                <td class="right">- Gs. {{ number_format($liquidacion->total_aportes_ips, 0, ',', '.') }}</td>
            </tr>
            <tr class="neto">
                <td>Líquido a Pagar:</td>
                <td class="right">Gs. {{ number_format($liquidacion->total_neto, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    {{-- ============ FIRMAS ============ --}}
    <div class="firmas">
        <div>
            Firma del Empleador
            <small>{{ $liquidacion->empresa->nombre ?? '' }}</small>
        </div>
        <div>
            Firma del Colaborador
            <small>Recibí conforme</small>
        </div>
    </div>

    {{-- ============ PIE ============ --}}
    <div class="footer">
        Documento generado por el Sistema de RRHH — {{ now()->format('d/m/Y H:i') }}
    </div>
</div>

{{-- ============ ACCIONES ============ --}}
<div class="no-print">
    <button onclick="window.print()">🖨 Imprimir</button>
    <a href="{{ route('liquidacion.index') }}">← Volver al listado</a>
</div>

</body>
</html>