@extends('layouts.master')

@section('content')
<div class="group-data-[sidebar-size=lg]:ltr:md:ml-vertical-menu group-data-[sidebar-size=lg]:rtl:md:mr-vertical-menu group-data-[sidebar-size=md]:ltr:ml-vertical-menu-md group-data-[sidebar-size=md]:rtl:mr-vertical-menu-md group-data-[sidebar-size=sm]:ltr:ml-vertical-menu-sm group-data-[sidebar-size=sm]:rtl:mr-vertical-menu-sm pt-[calc(theme('spacing.header')_*_1)] pb-[calc(theme('spacing.header')_*_0.8)] px-4 group-data-[navbar=bordered]:pt-[calc(theme('spacing.header')_*_1.3)] group-data-[navbar=hidden]:pt-0 group-data-[layout=horizontal]:mx-auto group-data-[layout=horizontal]:max-w-screen-2xl group-data-[layout=horizontal]:px-0 group-data-[layout=horizontal]:group-data-[sidebar-size=lg]:ltr:md:ml-auto group-data-[layout=horizontal]:group-data-[sidebar-size=lg]:rtl:md:mr-auto group-data-[layout=horizontal]:md:pt-[calc(theme('spacing.header')_*_1.6)] group-data-[layout=horizontal]:px-3 group-data-[layout=horizontal]:group-data-[navbar=hidden]:pt-[calc(theme('spacing.header')_*_0.9)]">
    <div class="container-fluid group-data-[content=boxed]:max-w-boxed mx-auto">
        
        <!-- Breadcrumb -->
        <div class="flex flex-col gap-2 py-4 md:flex-row md:items-center print:hidden">
            <div class="grow">
                <h5 class="text-16">Gestión de Colaboradores</h5>
            </div>
            <ul class="flex items-center gap-2 text-sm font-normal shrink-0">
                <li class="relative before:content-['\ea54'] before:font-remix ltr:before:-right-1 rtl:before:-left-1 before:absolute before:text-[18px] before:-top-[3px] ltr:pr-4 rtl:pl-4 before:text-slate-400 dark:text-zink-200">
                    <a href="#" class="text-slate-400 dark:text-zink-200">RRHH</a>
                </li>
                <li class="text-slate-700 dark:text-zink-100">Colaboradores</li>
            </ul>
        </div>

        <!-- Alertas -->
        @if(session('success') || session('error'))
            <div class="px-4 py-3 mb-4 text-sm border rounded-md {{ session('success') ? 'text-green-500 border-green-200 bg-green-50 dark:bg-green-400/20 dark:border-green-500/50' : 'text-red-500 border-red-200 bg-red-50 dark:bg-red-400/20 dark:border-red-500/50' }} flex items-center justify-between">
                {{ session('success') ?? session('error') }}
                <button type="button" class="text-slate-400 hover:text-slate-600" onclick="this.parentElement.remove()">
                    <i data-lucide="x" class="size-4"></i>
                </button>
            </div>
        @endif

        <!-- Tarjeta de Tabla -->
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h6 class="text-15">Lista de Colaboradores</h6>
                    <button class="text-white btn bg-custom-500 border-custom-500 hover:text-white hover:bg-custom-600 hover:border-custom-600 focus:text-white focus:bg-custom-600 focus:border-custom-600 focus:ring focus:ring-custom-100 active:text-white active:bg-custom-600 active:border-custom-600 active:ring active:ring-custom-100 dark:ring-custom-400/20" data-modal-target="modalEmpleado" onclick="limpiarModal()">
                        <i data-lucide="plus" class="inline-block size-4"></i> 
                        <span class="align-middle">Nuevo Colaborador</span>
                    </button>
                </div>

                <div class="-mx-5 overflow-x-auto">
                    <table class="w-full whitespace-nowrap" id="tablaEmpleados">
                        <thead class="ltr:text-left rtl:text-right bg-slate-100 text-slate-500 dark:text-zink-200 dark:bg-zink-600">
                            <tr>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500 w-[60px]">Foto</th>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500">Código</th>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500">Documento</th>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500">Apellidos y Nombres</th>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500">Cargo</th>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500">Departamento</th>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500">Salario Base</th>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500">Estado Laboral</th>
                                <th class="px-3.5 py-2.5 first:pl-5 last:pr-5 font-semibold border-y border-slate-200 dark:border-zink-500 w-[120px]">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($empleados as $e)
                            <tr>
                                <td class="px-3.5 py-2.5 first:pl-5 last:pr-5 border-y border-slate-200 dark:border-zink-500 text-center">
                                    @if($e->foto)
                                        <img src="{{ asset('assets/images/empleados/'.$e->foto) }}" class="rounded-full" width="40" height="40" style="object-fit:cover;">
                                    @else
                                        <span class="px-2.5 py-0.5 text-xs font-medium rounded border bg-slate-100 border-slate-200 text-slate-500 dark:bg-slate-500/20 dark:border-slate-500/20 dark:text-zink-200">N/A</span>
                                    @endif
                                </td>
                                <td class="px-3.5 py-2.5 first:pl-5 last:pr-5 border-y border-slate-200 dark:border-zink-500 text-sm">{{ $e->codigo_empleado }}</td>
                                <td class="px-3.5 py-2.5 first:pl-5 last:pr-5 border-y border-slate-200 dark:border-zink-500 text-sm">{{ $e->tipo_documento }} {{ $e->numero_documento }}</td>
                                <td class="px-3.5 py-2.5 first:pl-5 last:pr-5 border-y border-slate-200 dark:border-zink-500 text-sm">{{ $e->apellidos }}, {{ $e->nombres }}</td>
                                <td class="px-3.5 py-2.5 first:pl-5 last:pr-5 border-y border-slate-200 dark:border-zink-500 text-sm">{{ $e->cargo->nombre ?? '-' }}</td>
                                <td class="px-3.5 py-2.5 first:pl-5 last:pr-5 border-y border-slate-200 dark:border-zink-500 text-sm">{{ $e->departamento->nombre ?? '-' }}</td>
                                <td class="px-3.5 py-2.5 first:pl-5 last:pr-5 border-y border-slate-200 dark:border-zink-500 text-sm">{{ number_format($e->salario_base, 0, ',', '.') }} Gs</td>
                                <td class="px-3.5 py-2.5 first:pl-5 last:pr-5 border-y border-slate-200 dark:border-zink-500">
                                    <span class="px-2.5 py-0.5 text-xs inline-block font-medium rounded border {{ 
                                        match($e->estado_laboral) {
                                            'activo' => 'bg-green-100 border-green-200 text-green-500 dark:bg-green-500/20 dark:border-green-500/20',
                                            'vacaciones' => 'bg-info-100 border-info-200 text-info-500 dark:bg-info-500/20 dark:border-info-500/20',
                                            'licencia' => 'bg-warning-100 border-warning-200 text-warning-500 dark:bg-warning-500/20 dark:border-warning-500/20',
                                            'suspendido' => 'bg-danger-100 border-danger-200 text-danger-500 dark:bg-danger-500/20 dark:border-danger-500/20',
                                            default => 'bg-slate-100 border-slate-200 text-slate-500 dark:bg-slate-500/20 dark:border-slate-500/20 dark:text-zink-200'
                                        }
                                    }}">
                                        {{ ucfirst($e->estado) }}
                                    </span>
                                </td>
                                <td class="px-3.5 py-2.5 first:pl-5 last:pr-5 border-y border-slate-200 dark:border-zink-500">
                                    <div class="flex gap-2 justify-center">
                                        <button class="flex items-center justify-center transition-all duration-200 ease-linear rounded-md size-8 bg-slate-100 text-slate-500 hover:text-custom-500 hover:bg-custom-100 dark:bg-zink-600 dark:text-zink-200 dark:hover:text-custom-500 dark:hover:bg-custom-500/20" onclick="editarEmpleado({{ $e->id }})" title="Editar">
                                            <i data-lucide="pencil" class="size-4"></i>
                                        </button>
                                        <button class="flex items-center justify-center transition-all duration-200 ease-linear rounded-md size-8 bg-slate-100 text-slate-500 hover:text-red-500 hover:bg-red-100 dark:bg-zink-600 dark:text-zink-200 dark:hover:text-red-500 dark:hover:bg-red-500/20" onclick="confirmarEliminar({{ $e->id }})" title="Eliminar">
                                            <i data-lucide="trash-2" class="size-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="px-3.5 py-2.5 first:pl-5 last:pr-5 border-y border-slate-200 dark:border-zink-500 text-center text-slate-500 dark:text-zink-200">
                                    No hay colaboradores registrados en la base de datos.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear/Editar (adaptado a Tailwind) -->
<div id="modalEmpleado" modal-center="" class="fixed flex flex-col hidden transition-all duration-300 ease-in-out left-2/4 z-drawer -translate-x-2/4 -translate-y-2/4">
    <div class="w-screen md:w-[40rem] bg-white shadow rounded-md dark:bg-zink-600">
        <div class="flex items-center justify-between p-4 border-b dark:border-zink-500">
            <h5 class="text-16" id="modalTitulo">Nuevo Colaborador</h5>
            <button data-modal-close="modalEmpleado" class="transition-all duration-200 ease-linear text-slate-400 hover:text-red-500">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="max-h-[calc(theme('height.screen')_-_180px)] p-4 overflow-y-auto">
            <form id="formEmpleado" method="POST" enctype="multipart/form-data">
                @csrf
                <div id="methodField"></div>
                <input type="hidden" id="empleado_id" name="empleado_id">

                <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
                    <!-- Datos Personales -->
                    <div class="xl:col-span-4">
                        <label class="inline-block mb-2 text-base font-medium">Nombres *</label>
                        <input type="text" name="nombres" id="nombres" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" required>
                    </div>
                    <div class="xl:col-span-4">
                        <label class="inline-block mb-2 text-base font-medium">Apellidos *</label>
                        <input type="text" name="apellidos" id="apellidos" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" required>
                    </div>
                    <div class="xl:col-span-4">
                        <label class="inline-block mb-2 text-base font-medium">Foto</label>
                        <input type="file" name="foto" id="foto" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" accept="image/*">
                    </div>

                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Tipo Doc. *</label>
                        <select name="tipo_documento" id="tipo_documento" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" required>
                            <option value="CI">CI</option>
                            <option value="RUC">RUC</option>
                            <option value="PASAPORTE">Pasaporte</option>
                        </select>
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Nro. Documento *</label>
                        <input type="text" name="numero_documento" id="numero_documento" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" required>
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Fecha Nac.</label>
                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full">
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Sexo</label>
                        <select name="sexo" id="sexo" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full">
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                            <option value="OTRO">Otro</option>
                        </select>
                    </div>

                    <div class="xl:col-span-4">
                        <label class="inline-block mb-2 text-base font-medium">Teléfono</label>
                        <input type="text" name="telefono" id="telefono" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full">
                    </div>
                    <div class="xl:col-span-4">
                        <label class="inline-block mb-2 text-base font-medium">Correo</label>
                        <input type="email" name="correo" id="correo" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full">
                    </div>
                    <div class="xl:col-span-4">
                        <label class="inline-block mb-2 text-base font-medium">Dirección</label>
                        <input type="text" name="direccion" id="direccion" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full">
                    </div>

                    <!-- Datos Laborales -->
                    <div class="xl:col-span-4">
                        <label class="inline-block mb-2 text-base font-medium">Código Empleado *</label>
                        <input type="text" name="codigo_empleado" id="codigo_empleado" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" required>
                    </div>
                    <div class="xl:col-span-4">
                        <label class="inline-block mb-2 text-base font-medium">Empresa *</label>
                        <select name="empresa_id" id="empresa_id" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" required>
                            <option value="">Seleccionar</option>
                            @foreach($empresas as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-4">
                        <label class="inline-block mb-2 text-base font-medium">Sucursal *</label>
                        <select name="sucursal_id" id="sucursal_id" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" required>
                            <option value="">Seleccionar</option>
                            @foreach($sucursales as $suc)
                                <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Departamento</label>
                        <select name="departamento_id" id="departamento_id" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full">
                            <option value="">Seleccionar</option>
                            @foreach($departamentos as $dep)
                                <option value="{{ $dep->id }}">{{ $dep->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Cargo *</label>
                        <select name="cargo_id" id="cargo_id" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" required>
                            <option value="">Seleccionar</option>
                            @foreach($cargos as $c)
                                <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Tipo Contrato</label>
                        <select name="tipo_contrato_id" id="tipo_contrato_id" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full">
                            <option value="">Seleccionar</option>
                            @foreach($tiposContrato as $tc)
                                <option value="{{ $tc->id }}">{{ $tc->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Horario</label>
                        <select name="horario_id" id="horario_id" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full">
                            <option value="">Seleccionar</option>
                            @foreach($horarios as $h)
                                <option value="{{ $h->id }}">{{ $h->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Fecha Ingreso *</label>
                        <input type="date" name="fecha_ingreso" id="fecha_ingreso" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" required>
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Salario Base *</label>
                        <input type="number" name="salario_base" id="salario_base" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" required min="0">
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Nro. IPS</label>
                        <input type="text" name="numero_ips" id="numero_ips" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full">
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Profesión</label>
                        <input type="text" name="profesion" id="profesion" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full">
                    </div>
                    <div class="xl:col-span-12">
                        <label class="inline-block mb-2 text-base font-medium">Estado Laboral *</label>
                        <select name="estado_laboral" id="estado_laboral" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200 w-full" required>
                            <option value="activo">Activo</option>
                            <option value="vacaciones">Vacaciones</option>
                            <option value="licencia">Licencia</option>
                            <option value="suspendido">Suspendido</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="reset" data-modal-close="modalEmpleado" class="text-red-500 bg-white btn hover:text-red-500 hover:bg-red-100 focus:text-red-500 focus:bg-red-100 active:text-red-500 active:bg-red-100 dark:bg-zink-600 dark:hover:bg-red-500/10 dark:focus:bg-red-500/10 dark:active:bg-red-500/10">Cancelar</button>
                    <button type="submit" class="text-white btn bg-custom-500 border-custom-500 hover:text-white hover:bg-custom-600 hover:border-custom-600 focus:text-white focus:bg-custom-600 focus:border-custom-600 focus:ring focus:ring-custom-100 active:text-white active:bg-custom-600 active:border-custom-600 active:ring active:ring-custom-100 dark:ring-custom-400/20">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Eliminar -->
<div id="modalEliminar" modal-center="" class="fixed flex flex-col hidden transition-all duration-300 ease-in-out left-2/4 z-drawer -translate-x-2/4 -translate-y-2/4">
    <div class="w-screen md:w-[35rem] bg-white shadow rounded-md dark:bg-zink-600">
        <div class="max-h-[calc(theme('height.screen')_-_180px)] overflow-y-auto px-6 py-8">
            <div class="float-right">
                <button data-modal-close="modalEliminar" class="transition-all duration-200 ease-linear text-slate-500 hover:text-red-500">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <img src="data:image/png;base64,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" alt="" class="block h-12 mx-auto">
            <div class="mt-5 text-center">
                <h5 class="mb-1">¿Estás seguro?</h5>
                <p class="text-slate-500 dark:text-zink-200">¿Estás seguro de que quieres eliminar este registro?</p>
                <p class="text-slate-500 dark:text-zink-200">Empleado: <strong id="nombreEliminar"></strong></p>
                <div class="flex justify-center gap-2 mt-6">
                    <form id="formEliminar" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="flex gap-2">
                            <button type="reset" data-modal-close="modalEliminar" class="bg-white text-slate-500 btn hover:text-slate-500 hover:bg-slate-100 focus:text-slate-500 focus:bg-slate-100 active:text-slate-500 active:bg-slate-100 dark:bg-zink-600 dark:hover:bg-slate-500/10 dark:focus:bg-slate-500/10 dark:active:bg-slate-500/10">Cancelar</button>
                            <button type="submit" class="text-white bg-red-500 border-red-500 btn hover:text-white hover:bg-red-600 hover:border-red-600 focus:text-white focus:bg-red-600 focus:border-red-600 focus:ring focus:ring-red-100 active:text-white active:bg-red-600 active:border-red-600 active:ring active:ring-red-100 dark:ring-custom-400/20">Sí, Eliminar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@section('scripts')
<script>
    const rutaStore = "{{ route('empleados.store') }}";
    const rutaUpdateBase = "{{ url('hr/empleados') }}";

    function limpiarModal() {
        document.getElementById('formEmpleado').reset();
        document.getElementById('empleado_id').value = '';
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('formEmpleado').action = rutaStore;
        document.getElementById('modalTitulo').textContent = 'Nuevo Colaborador';
    }

    function editarEmpleado(id) {
        fetch(`${rutaUpdateBase}/${id}/ver`)
            .then(r => r.json())
            .then(data => {
                document.getElementById('empleado_id').value = data.id;
                document.getElementById('nombres').value = data.nombres;
                document.getElementById('apellidos').value = data.apellidos;
                document.getElementById('tipo_documento').value = data.tipo_documento;
                document.getElementById('numero_documento').value = data.numero_documento;
                document.getElementById('fecha_nacimiento').value = data.fecha_nacimiento;
                document.getElementById('sexo').value = data.sexo;
                document.getElementById('telefono').value = data.telefono ?? '';
                document.getElementById('correo').value = data.correo ?? '';
                document.getElementById('direccion').value = data.direccion ?? '';
                document.getElementById('codigo_empleado').value = data.codigo_empleado;
                document.getElementById('empresa_id').value = data.empresa_id;
                document.getElementById('sucursal_id').value = data.sucursal_id;
                document.getElementById('departamento_id').value = data.departamento_id ?? '';
                document.getElementById('cargo_id').value = data.cargo_id;
                document.getElementById('tipo_contrato_id').value = data.tipo_contrato_id ?? '';
                document.getElementById('horario_id').value = data.horario_id ?? '';
                document.getElementById('fecha_ingreso').value = data.fecha_ingreso;
                document.getElementById('salario_base').value = data.salario_base;
                document.getElementById('numero_ips').value = data.numero_ips ?? '';
                document.getElementById('profesion').value = data.profesion ?? '';
                document.getElementById('estado_laboral').value = data.estado_laboral;

                document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
                document.getElementById('formEmpleado').action = `${rutaUpdateBase}/${id}/actualizar`;
                document.getElementById('modalTitulo').textContent = 'Editar Colaborador';

                const modal = document.getElementById('modalEmpleado');
                modal.classList.remove('hidden');
                modal.classList.add('show');
            })
            .catch(error => console.error('Error:', error));
    }

    function confirmarEliminar(id) {
        const empleados = @json($empleados);
        const empleado = empleados.find(e => e.id === id);
        document.getElementById('nombreEliminar').textContent = empleado ? `${empleado.apellidos}, ${empleado.nombres}` : '';
        document.getElementById('formEliminar').action = `${rutaUpdateBase}/${id}/eliminar`;
        
        const modal = document.getElementById('modalEliminar');
        modal.classList.remove('hidden');
        modal.classList.add('show');
    }

    // Función para cerrar modales
    document.addEventListener('DOMContentLoaded', function() {
        // Cerrar modales
        document.querySelectorAll('[data-modal-close]').forEach(btn => {
            btn.addEventListener('click', function() {
                const modalId = this.getAttribute('data-modal-close');
                const modal = document.getElementById(modalId);
                modal.classList.add('hidden');
                modal.classList.remove('show');
            });
        });

        // Abrir modales
        document.querySelectorAll('[data-modal-target]').forEach(btn => {
            btn.addEventListener('click', function() {
                const modalId = this.getAttribute('data-modal-target');
                const modal = document.getElementById(modalId);
                modal.classList.remove('hidden');
                modal.classList.add('show');
            });
        });

        // ⚠️ ELIMINA ESTA PARTE - DataTable ya se inicializa en el layout
        /*
        if (typeof $.fn !== 'undefined' && $.fn.DataTable) {
            $('#tablaEmpleados').DataTable({ ... });
        }
        */
    });
</script>
@endsection
@endsection