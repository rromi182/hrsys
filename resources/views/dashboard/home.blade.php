@extends('layouts.master')

@section('content')
<!-- Page-content -->
<div class="group-data-[sidebar-size=lg]:ltr:md:ml-vertical-menu group-data-[sidebar-size=lg]:rtl:md:mr-vertical-menu group-data-[sidebar-size=md]:ltr:ml-vertical-menu-md group-data-[sidebar-size=md]:rtl:mr-vertical-menu-md group-data-[sidebar-size=sm]:ltr:ml-vertical-menu-sm group-data-[sidebar-size=sm]:rtl:mr-vertical-menu-sm pt-[calc(theme('spacing.header')_*_1)] pb-[calc(theme('spacing.header')_*_0.8)] px-4 group-data-[navbar=bordered]:pt-[calc(theme('spacing.header')_*_1.3)] group-data-[navbar=hidden]:pt-0 group-data-[layout=horizontal]:mx-auto group-data-[layout=horizontal]:max-w-screen-2xl group-data-[layout=horizontal]:px-0 group-data-[layout=horizontal]:group-data-[sidebar-size=lg]:ltr:md:ml-auto group-data-[layout=horizontal]:group-data-[sidebar-size=lg]:rtl:md:mr-auto group-data-[layout=horizontal]:md:pt-[calc(theme('spacing.header')_*_1.6)] group-data-[layout=horizontal]:px-3 group-data-[layout=horizontal]:group-data-[navbar=hidden]:pt-[calc(theme('spacing.header')_*_0.9)]">
    <div class="container-fluid group-data-[content=boxed]:max-w-boxed mx-auto">
        
        <!-- Breadcrumb -->
        <div class="flex flex-col gap-2 py-4 md:flex-row md:items-center print:hidden">
            <div class="grow">
                <h5 class="text-16">Recursos Humanos</h5>
            </div>
            <ul class="flex items-center gap-2 text-sm font-normal shrink-0">
                <li class="relative before:content-['\ea54'] before:font-remix ltr:before:-right-1 rtl:before:-left-1 before:absolute before:text-[18px] before:-top-[3px] ltr:pr-4 rtl:pl-4 before:text-slate-400 dark:text-zink-200">
                    <a href="#!" class="text-slate-400 dark:text-zink-200">Dashboards</a>
                </li>
                <li class="text-slate-700 dark:text-zink-100">HR</li>
            </ul>
        </div>

        <!-- Título de bienvenida -->
        <h5 class="mb-4">Panel de gestión de Recursos Humanos</h5>

        <!-- Tarjetas de estadísticas con datos reales -->
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4">
            
            <!-- Colaboradores Activos -->
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 dark:text-zink-200">Colaboradores Activos</p>
                            <h5 class="mt-2 text-2xl font-bold">{{ $totalEmpleadosActivos ?? 0 }}</h5>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 text-green-500 bg-green-100 rounded-full dark:bg-green-500/20">
                            <i data-lucide="users" class="size-6"></i>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-slate-500 dark:text-zink-200">
                        <span class="text-green-500">↗ {{ $nuevosIngresos ?? 0 }}</span> nuevos este mes
                    </p>
                </div>
            </div>

            <!-- Salario Promedio -->
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 dark:text-zink-200">Salario Promedio</p>
                            <h5 class="mt-2 text-2xl font-bold">Gs. {{ number_format($salarioPromedio ?? 0, 0, ',', '.') }}</h5>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 text-blue-500 bg-blue-100 rounded-full dark:bg-blue-500/20">
                            <i data-lucide="wallet" class="size-6"></i>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-slate-500 dark:text-zink-200">Basado en colaboradores activos</p>
                </div>
            </div>

            <!-- Departamentos -->
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 dark:text-zink-200">Departamentos</p>
                            <h5 class="mt-2 text-2xl font-bold">{{ $totalDepartamentos ?? 0 }}</h5>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 text-purple-500 bg-purple-100 rounded-full dark:bg-purple-500/20">
                            <i data-lucide="building-2" class="size-6"></i>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-slate-500 dark:text-zink-200">Unidades organizativas activas</p>
                </div>
            </div>

            <!-- Nuevos Ingresos -->
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-500 dark:text-zink-200">Nuevos Ingresos</p>
                            <h5 class="mt-2 text-2xl font-bold">{{ $nuevosIngresos ?? 0 }}</h5>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 text-orange-500 bg-orange-100 rounded-full dark:bg-orange-500/20">
                            <i data-lucide="user-plus" class="size-6"></i>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-slate-500 dark:text-zink-200">Este mes - {{ now()->format('F Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Tabla de Colaboradores Recientes con datos reales -->
        <div class="mt-5 card">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h6 class="text-15">Colaboradores Recientes</h6>
                    <a href="{{ route('empleados.index') }}" class="text-sm text-custom-500 hover:text-custom-600">Ver todos →</a>
                </div>

                <!-- Tabla con DataTable -->
                <div class="-mx-5 overflow-x-auto">
                    <table id="colaboradoresTable" class="w-full whitespace-nowrap">
                        <thead class="ltr:text-left rtl:text-right bg-slate-100 text-slate-500 dark:text-zink-200 dark:bg-zink-600">
                            <tr>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500">COLABORADOR</th>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500">CÓDIGO</th>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500">DOCUMENTO</th>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500">DEPARTAMENTO</th>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500">CARGO</th>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500">CONTRATO</th>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500">INGRESO</th>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500">SALARIO</th>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500">ESTADO</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($empleados ?? [] as $empleado)
                            <tr>
                                <td class="px-3.5 py-2.5 first:pl-5 last:pr-5 border-y border-slate-200 dark:border-zink-500">
                                    <div class="flex items-center gap-2">
                                        <div class="flex items-center justify-center w-8 h-8 text-sm font-medium text-white rounded-full bg-custom-500">
                                            {{ strtoupper(substr($empleado->nombres, 0, 1)) }}{{ strtoupper(substr($empleado->apellidos, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="text-sm">{{ $empleado->apellidos }}, {{ $empleado->nombres }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3.5 py-2.5 first:pl-5 last:pr-5 border-y border-slate-200 dark:border-zink-500 text-sm">{{ $empleado->codigo_empleado ?? '-' }}</td>
                                <td class="px-3.5 py-2.5 first:pl-5 last:pr-5 border-y border-slate-200 dark:border-zink-500 text-sm">{{ $empleado->tipo_documento ?? '' }} {{ $empleado->numero_documento ?? '' }}</td>
                                <td class="px-3.5 py-2.5 first:pl-5 last:pr-5 border-y border-slate-200 dark:border-zink-500 text-sm">{{ $empleado->departamento->nombre ?? '-' }}</td>
                                <td class="px-3.5 py-2.5 first:pl-5 last:pr-5 border-y border-slate-200 dark:border-zink-500 text-sm">{{ $empleado->cargo->nombre ?? '-' }}</td>
                                <td class="px-3.5 py-2.5 first:pl-5 last:pr-5 border-y border-slate-200 dark:border-zink-500 text-sm">{{ $empleado->tipoContrato->nombre ?? '-' }}</td>
                                <td class="px-3.5 py-2.5 first:pl-5 last:pr-5 border-y border-slate-200 dark:border-zink-500 text-sm">{{ $empleado->fecha_ingreso ? date('d/m/Y', strtotime($empleado->fecha_ingreso)) : '-' }}</td>
                                <td class="px-3.5 py-2.5 first:pl-5 last:pr-5 border-y border-slate-200 dark:border-zink-500 text-sm">Gs. {{ number_format($empleado->salario_base ?? 0, 0, ',', '.') }}</td>
                                <td class="px-3.5 py-2.5 first:pl-5 last:pr-5 border-y border-slate-200 dark:border-zink-500">
                                    @php
                                        $estadoClass = match($empleado->estado ?? 'inactivo') {
                                            'activo' => 'bg-green-100 border-green-200 text-green-500 dark:bg-green-500/20 dark:border-green-500/20',
                                            'vacaciones' => 'bg-blue-100 border-blue-200 text-blue-500 dark:bg-blue-500/20 dark:border-blue-500/20',
                                            'licencia' => 'bg-yellow-100 border-yellow-200 text-yellow-500 dark:bg-yellow-500/20 dark:border-yellow-500/20',
                                            'suspendido' => 'bg-red-100 border-red-200 text-red-500 dark:bg-red-500/20 dark:border-red-500/20',
                                            default => 'bg-slate-100 border-slate-200 text-slate-500 dark:bg-slate-500/20 dark:border-slate-500/20 dark:text-zink-200'
                                        };
                                    @endphp
                                    <span class="px-2.5 py-0.5 text-xs inline-block font-medium rounded border {{ $estadoClass }}">
                                        {{ ucfirst($empleado->estado ?? 'Inactivo') }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="px-3.5 py-2.5 text-center text-slate-500 dark:text-zink-200">
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
    <!-- container-fluid -->
</div>
<!-- End Page-content -->

@section('script')
    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            $('#colaboradoresTable').DataTable({
                language: {
                    "sProcessing":     "Procesando...",
                    "sLengthMenu":     "Mostrar _MENU_ registros",
                    "sZeroRecords":    "No se encontraron resultados",
                    "sEmptyTable":     "Ningún dato disponible en esta tabla",
                    "sInfo":           "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "sInfoEmpty":      "Mostrando 0 a 0 de 0 registros",
                    "sInfoFiltered":   "(filtrado de _MAX_ registros totales)",
                    "sSearch":         "Buscar:",
                    "oPaginate": {
                        "sFirst":    "Primero",
                        "sLast":     "Último",
                        "sNext":     "Siguiente",
                        "sPrevious": "Anterior"
                    }
                },
                order: [[6, 'desc']], // Ordenar por fecha de ingreso (columna 7, index 6)
                pageLength: 5,
                responsive: true,
                columnDefs: [
                    { orderable: false, targets: 0 } // Desactivar orden en columna COLABORADOR
                ]
            });
        });
    </script>
    <script src="{{ URL::to('assets/js/pages/dashboards-hr.init.js') }}"></script>
@endsection
@endsection