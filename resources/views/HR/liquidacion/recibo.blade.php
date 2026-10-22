<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Liquidación de Salarios - {{ $liquidacion->empleado->apellidos }} {{ $liquidacion->empleado->nombres }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Questrial&display=swap" rel="stylesheet">

    @php
        $empleador = [
            'razon_social' => 'T.I.T. PARAGUAY S.R.L.',
            'patronal'     => '94731',
            'telefono'     => '0982 426 015',
            'email'        => 'titpysrl@gmail.com',
            'direccion'    => 'Callejón primera c/ Tte. Juan Benitez N° 151',
            'representante'=> 'EVER MEZA ALMEIDA',
        ];

        $det = $liquidacion->detalles;
        $sumar = fn($codigo) => (int) $det->where('formula_aplicada', $codigo)->sum('monto');

        $salarioBasico = $sumar('SUELDO');
        $horasExtras   = $sumar('EXTRA');
        $totalSalario  = (int) $liquidacion->total_ingresos;

        $ips           = (int) $liquidacion->total_aportes_ips;
        $adelantos     = $sumar('VALE');
        $llegTardias   = $sumar('LLEGADA_TARDIA');
        $otros         = $sumar('OTROS') + $sumar('AUSENCIA');

        $totalDesc     = (int) $liquidacion->total_descuentos + $ips;
        $saldoCobrar   = (int) $liquidacion->total_neto;

        $fmt = fn($n) => $n > 0 ? number_format($n, 0, ',', '.') : '';
    @endphp

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        /* Mantenemos A4 vertical, el recibo irá en la parte superior */
        @page { size: A4 portrait; margin: 10mm; }

        body {
            font-family: 'Questrial', 'Century Gothic', 'Gotham', 'Poppins', Arial, sans-serif;
            font-size: 13px;
            color: #111;
            background: #f0f0f0;
            padding: 15px;
        }

        /* ============ MARCO DOBLE ============ */
        .hoja {
            position: relative;
            width: 100%;
            max-width: 21cm; 
            height: 14cm; /* Aumentado de 10cm a 12cm para dar más espacio general */
            margin: 0 auto;
            background: #fff;
            border: 6px double #000;
            padding: 15px 25px; 
            overflow: hidden;
        }

        /* ============ ENCABEZADO ============ */
        .top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .contacto {
            font-size: 11px; 
            line-height: 1.6;
            padding-top: 4px;
        }

        .contacto .linea { display: flex; align-items: center; gap: 8px; margin-bottom: 2px; }
        .contacto svg { width: 12px; height: 12px; flex-shrink: 0; }

        .logo img { height: 45px; width: auto; object-fit: contain; }

        .titulo {
            text-align: center;
            margin-top: 1px;
            margin-bottom: 20px;
        }

        .titulo h1 {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .titulo p { font-size: 14px; margin-top: 2px; }

        /* ============ DATOS ============ */
        .datos { margin-bottom: 10px; }

        .datos .fila {
            display: flex;
            align-items: baseline;
            gap: 30px;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .datos b { font-weight: bold; }

        .valor {
            display: inline-block;
            border-bottom: 1px solid #444;
            padding: 0 8px;
            min-width: 50px;
            text-align: center;
        }

        .valor.largo { min-width: 350px; text-align: left; }

        /* ============ TABLA ============ */
        table.liq {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            table-layout: fixed;
        }

        table.liq th,
        table.liq td {
            border: 1px solid #000000;
            padding: 4px 5px; 
            font-size: 12px;
            text-align: center;
            vertical-align: top;
            font-weight: bold;
        }

        table.liq th { height: 30px; }

        table.liq td {
            height: 28px;
            text-align: right;
            font-weight: normal;
            vertical-align: middle;
            font-size: 12px;
        }

        table.liq th.centro { text-align: center; }
        table.liq th.sub    { height: 24px; }

        /* ============ CERTIFICO ============ */
        .certifico {
            font-size: 12px;
            line-height: 1.4;
            margin: 0; 
            padding-left: 20px;
            max-width: 750px;
        }

  /* ============ FIRMAS ============ */
        .firmas {
            display: flex;
            justify-content: space-between; 
            padding: 0 40px; 
            position: absolute;
            bottom: 90px;
            left: 0;
            right: 0;
            z-index: 2; 
        }

        .firmas .firma {
            width: 220px;
            border-top: 1px solid #000;
            text-align: center;
            padding-top: 5px;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
        }

                /* ============ DECORACIÓN (circuito) ============ */
        .circuito {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 80px;
            opacity: .85;
            z-index: 1;
        }

        .circuito svg { 
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%; 
            height: 80px; 
        }

        /* ============ ACCIONES ============ */
        .no-print { text-align: center; margin: 15px 0; }

        .no-print button,
        .no-print a {
            padding: 8px 18px;
            margin: 0 4px;
            cursor: pointer;
            font-size: 13px;
            font-family: inherit;
            border: 1px solid #333;
            background: #fff;
            text-decoration: none;
            color: #000;
            border-radius: 4px;
        }

        .no-print button:hover,
        .no-print a:hover { background: #333; color: #fff; }

        /* ============ REGLAS DE IMPRESIÓN ============ */
        @media print {
            body { 
                background: #fff; 
                padding: 0; 
                margin: 0;
                display: flex;
                align-items: flex-start;
                justify-content: flex-start;
            }
            .hoja { 
                max-width: 100%; 
                width: 21cm; 
                height: 14cm;
                margin: 0; 
                border: 6px double #000; 
            }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="hoja">

    <div class="top">
        <div class="contacto">
            <div class="linea">
                <svg viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="1.8"><rect x="6" y="2" width="12" height="20" rx="2"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
                <span>{{ $empleador['telefono'] }}</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="1.8" style="margin-left:10px"><rect x="2" y="5" width="20" height="14" rx="2"/><polyline points="2,6 12,13 22,6"/></svg>
                <span>{{ $empleador['email'] }}</span>
            </div>
            <div class="linea">
                <svg viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="1.8"><path d="M12 22s7-6.5 7-12a7 7 0 10-14 0c0 5.5 7 12 7 12z"/><circle cx="12" cy="10" r="2.5"/></svg>
                <span>{{ $empleador['direccion'] }}</span>
            </div>
        </div>

        <div class="logo">
            <img src="{{ asset('assets/images/logo-tit-BN.jpeg') }}" alt="T.I.T. Paraguay">
        </div>
    </div>

    <div class="titulo">
        <h1>Liquidación de Salarios</h1>
        <p>(Conforme al art. 236 del C. del T.)</p>
    </div>

    <div class="datos">
        <div class="fila">
            <span><b>Empleador:</b> <b>{{ $empleador['razon_social'] }}</b></span>
            <span><b>N° Patronal MTESS: {{ $empleador['patronal'] }}</b></span>
        </div>

        <div class="fila">
            <span>
                <b>Apellido y Nombre del Trabajador:</b>
                <span class="valor largo">{{ $liquidacion->empleado->apellidos }}, {{ $liquidacion->empleado->nombres }}</span>
            </span>
        </div>

        <div class="fila" style="gap: 15px;">
            <b>Periodo de Pago:</b>
            <span><span class="valor">&nbsp;</span> <b>al</b> <span class="valor">&nbsp;</span></span>
            <span><b>de</b> <span class="valor" style="min-width: 100px;">&nbsp;</span></span>
            <span><b>del 202</b> <span class="valor" style="min-width: 70px;">&nbsp;</span></span>
        </div>
    </div>

    <table class="liq">
        <thead>
            <tr>
                <th rowspan="2" style="width: 7%;">Días<br>Trab.</th>
                <th rowspan="2" style="width: 11%;">Salario<br>Básico</th>
                <th rowspan="2" style="width: 9%;">Horas<br>extras</th>
                <th rowspan="2" style="width: 11%;">Total,<br>Salario</th>
                <th colspan="2" class="centro">Descuentos</th>
                <th rowspan="2" style="width: 9%;">Lleg.<br>Tardías</th>
                <th rowspan="2" style="width: 9%;">Otros</th>
                <th rowspan="2" style="width: 10%;">Total<br>desc.</th>
                <th rowspan="2" style="width: 11%;">Saldo a<br>cobrar</th>
            </tr>
            <tr>
                <th class="sub" style="width: 9%;">I.P.S</th>
                <th class="sub" style="width: 10%;">Adelantos</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{-- Días trabajados --}}</td>
                <td>{{ $fmt($salarioBasico) }}</td>
                <td>{{ $fmt($horasExtras) }}</td>
                <td>{{ $fmt($totalSalario) }}</td>
                <td>{{ $fmt($ips) }}</td>
                <td>{{ $fmt($adelantos) }}</td>
                <td>{{ $fmt($llegTardias) }}</td>
                <td>{{ $fmt($otros) }}</td>
                <td>{{ $fmt($totalDesc) }}</td>
                <td><b>{{ number_format($saldoCobrar, 0, ',', '.') }}</b></td>
            </tr>
        </tbody>
    </table>

    <p class="certifico">
        Certifico que he recibido de mi Empleador Sr (a): {{ $empleador['representante'] }}<br>
        a mi total y entera satisfacción el saldo líquido indicado en la presente liquidación, sin tener
        cargo ni cobro posterior alguno que hacer, por los conceptos de esta liquidación.
    </p>

    <div class="firmas">
        <div class="firma">Fecha</div>
        <div class="firma">Firma del Trabajador</div>
    </div>

    <div class="circuito">
        <svg viewBox="0 0 1000 110" preserveAspectRatio="none" fill="none" stroke="#5a2a30" stroke-width="1">
            <g>
                <polyline points="20,75 80,75 100,90 160,90"/>
                <polyline points="60,55 120,55 140,70 220,70 240,60 330,60"/>
                <polyline points="180,95 260,95 280,80 360,80"/>
                <polyline points="300,40 360,40 380,55 460,55"/>
                <polyline points="400,100 470,100 490,85 560,85 580,70 640,70"/>
                <polyline points="520,45 600,45 620,35 700,35"/>
                <polyline points="680,95 740,95 760,80 840,80 860,90 960,90"/>
                <polyline points="740,55 800,55 820,45 900,45 920,55 980,55"/>
            </g>
            <g fill="#fff">
                <circle cx="100" cy="90" r="5"/><circle cx="140" cy="70" r="5"/><circle cx="280" cy="80" r="5"/>
                <circle cx="380" cy="55" r="5"/><circle cx="490" cy="85" r="6"/><circle cx="620" cy="35" r="5"/>
                <circle cx="760" cy="80" r="5"/><circle cx="820" cy="45" r="5"/><circle cx="920" cy="55" r="5"/>
            </g>
        </svg>
    </div>
</div>

<div class="no-print">
    <button onclick="window.print()">🖨 Imprimir</button>
    <a href="{{ route('liquidacion.index') }}">← Volver al listado</a>
</div>

</body>
</html>