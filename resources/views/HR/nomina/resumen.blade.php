@extends('layouts.master')
@section('title') Resumen de Nómina @endsection
@section('content')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-bottom-dashed">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title mb-1">Resumen del Colaborador</h5>
                        <p class="text-muted mb-0">Período: {{ str_pad($mes, 2, '0', STR_PAD_LEFT) }}/{{ $anio }}</p>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('nomina.movimientos') }}" class="btn btn-dark btn-sm">
                            <i class="ri-arrow-left-line align-bottom me-1"></i> Volver a Movimientos
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body border-bottom-dashed">
                <form method="GET" action="{{ route('nomina.resumen') }}" class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Año</label>
                        <input type="number" name="anio" class="form-control" value="{{ $anio }}" min="2020" max="2030">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Mes</label>
                        <input type="number" name="mes" class="form-control" value="{{ $mes }}" min="1" max="12">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="ri-filter-3-line align-bottom me-1"></i> Filtrar
                        </button>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="{{ route('nomina.resumen.excel', ['anio' => $anio, 'mes' => $mes]) }}" class="btn btn-success btn-sm me-1">
                            <i class="ri-file-excel-2-line align-bottom me-1"></i> Excel
                        </a>
                        <a href="{{ route('nomina.resumen.csv', ['anio' => $anio, 'mes' => $mes]) }}" class="btn btn-primary btn-sm">
                            <i class="ri-download-cloud-2-line align-bottom me-1"></i> CSV
                        </a>
                    </div>
                </form>
            </div>

            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-12 text-end">
                        <h5 class="text-muted mb-1">TOTAL NETO GENERAL</h5>
                        <h3 class="fw-bold text-dark">Gs. {{ number_format($totalNetoGeneral, 0, ',', '.') }}</h3>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start">COLABORADOR</th>
                                <th>SUELDO</th>
                                <th>EXTRA</th>
                                <th>VALE</th>
                                <th>AUSENCIA</th>
                                <th>LLEGADA TARDÍA</th>
                                <th>OTROS</th>
                                <th>TOTAL NETO</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($resumen as $item)
                                <tr>
                                    <td class="text-start">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="avatar-xs bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:36px;height:36px;">
                                                    {{ strtoupper(substr($item['empleado']->nombres, 0, 1)) }}{{ strtoupper(substr($item['empleado']->apellidos, 0, 1)) }}
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-2">
                                                <h6 class="mb-0">{{ $item['empleado']->nombres }} {{ $item['empleado']->apellidos }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="{{ $item['sueldo'] > 0 ? 'text-success fw-semibold' : '' }}">
                                        {{ $item['sueldo'] > 0 ? number_format($item['sueldo'], 0, ',', '.') : '0' }}
                                    </td>
                                    <td class="{{ $item['extra'] > 0 ? 'text-success fw-semibold' : '' }}">
                                        {{ $item['extra'] > 0 ? number_format($item['extra'], 0, ',', '.') : '0' }}
                                    </td>
                                    <td class="{{ $item['vale'] > 0 ? 'text-danger fw-semibold' : '' }}">
                                        {{ $item['vale'] > 0 ? number_format($item['vale'], 0, ',', '.') : '0' }}
                                    </td>
                                    <td>{{ $item['ausencia'] > 0 ? number_format($item['ausencia'], 0, ',', '.') : '0' }}</td>
                                    <td>{{ $item['llegada_tardia'] > 0 ? number_format($item['llegada_tardia'], 0, ',', '.') : '0' }}</td>
                                    <td>{{ $item['otros'] > 0 ? number_format($item['otros'], 0, ',', '.') : '0' }}</td>
                                    <td class="fw-bold {{ $item['total_neto'] >= 0 ? 'text-primary' : 'text-danger' }}">
                                        {{ number_format($item['total_neto'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="ri-inbox-line fs-1 d-block mb-2"></i>
                                        No hay movimientos registrados para este período.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">Mostrando {{ count($resumen) }} colaboradores</small>
                    <small class="text-muted">Actualizado: {{ now()->format('d/m/Y H:i') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection