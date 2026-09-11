{{-- resources/views/HR/nomina/reporte-individual.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Nómina - {{ $empleado->nombres }} {{ $empleado->apellidos }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #333; padding: 20px; }
        .header { border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin-bottom: 5px; }
        .header p { font-size: 12px; color: #666; }
        .info { margin-bottom: 20px; }
        .info table { width: 100%; }
        .info td { padding: 4px 0; }
        .info td:first-child { width: 150px; font-weight: bold; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data th, table.data td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        table.data th { background: #f0f0f0; font-weight: bold; }
        table.data td.right { text-align: right; }
        table.data tfoot td { background: #f9f9f9; font-weight: bold; }
        .total-box {
            margin-top: 20px; padding: 12px; border: 2px solid #333;
            text-align: right; font-size: 14px; font-weight: bold;
        }
        .footer { margin-top: 40px; font-size: 10px; color: #999; text-align: center; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="header">
    <h1>REPORTE DE NÓMINA</h1>
    <p>Período: {{ str_pad($mes, 2, '0', STR_PAD_LEFT) }}/{{ $anio }}</p>
</div>

<div class="info">
    <table>
        <tr>
            <td>Colaborador:</td>
            <td>{{ $empleado->nombres }} {{ $empleado->apellidos }}</td>
        </tr>
        <tr>
            <td>Documento:</td>
            <td>{{ $empleado->numero_documento ?? '-' }}</td>
        </tr>
        <tr>
            <td>Fecha de emisión:</td>
            <td>{{ now()->format('d/m/Y H:i') }}</td>
        </tr>
    </table>
</div>

<h3 style="margin-bottom: 10px;">Detalle de Movimientos</h3>
<table class="data">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Tipo</th>
            <th>Naturaleza</th>
            <th class="right">Monto (Gs.)</th>
            <th>Observación</th>
        </tr>
    </thead>
    <tbody>
        @forelse($movimientos as $mov)
            <tr>
                <td>{{ $mov->fecha->format('d/m/Y') }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $mov->tipo_movimiento)) }}</td>
                <td>{{ $mov->es_ingreso ? 'Ingreso' : 'Descuento' }}</td>
                <td class="right">{{ number_format($mov->monto, 0, ',', '.') }}</td>
                <td>{{ $mov->observacion ?: '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="5" style="text-align:center;">Sin movimientos</td></tr>
        @endforelse
    </tbody>
</table>

<h3 style="margin-bottom: 10px;">Resumen por Concepto</h3>
<table class="data">
    <thead>
        <tr>
            <th>Concepto</th>
            <th class="right">Monto (Gs.)</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>Sueldo</td><td class="right">{{ number_format($resumen['sueldo'], 0, ',', '.') }}</td></tr>
        <tr><td>Extra</td><td class="right">{{ number_format($resumen['extra'], 0, ',', '.') }}</td></tr>
        <tr><td>Vale</td><td class="right">{{ number_format($resumen['vale'], 0, ',', '.') }}</td></tr>
        <tr><td>Ausencia</td><td class="right">{{ number_format($resumen['ausencia'], 0, ',', '.') }}</td></tr>
        <tr><td>Llegada Tardía</td><td class="right">{{ number_format($resumen['llegada_tardia'], 0, ',', '.') }}</td></tr>
        <tr><td>Otros</td><td class="right">{{ number_format($resumen['otros'], 0, ',', '.') }}</td></tr>
    </tbody>
</table>

<div class="total-box">
    TOTAL NETO: Gs. {{ number_format($totalNeto, 0, ',', '.') }}
</div>

<div class="footer">
    Generado por HRSYS — {{ now()->format('d/m/Y H:i') }}
</div>

@if(request('print'))
    <script>window.onload = function() { window.print(); };</script>
@endif

</body>
</html>