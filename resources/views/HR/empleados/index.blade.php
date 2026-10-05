@extends('layouts.master')
@section('title') Colaboradores @endsection

@section('content')
@php
$huboError = $errors->any();
$modoEditar = old('modo') === 'editar';
$actionForm = ($huboError && $modoEditar) ? url('hr/empleados/'.old('empleado_id').'/actualizar') : route('empleados.store');
@endphp

<div class="group-data-[sidebar-size=lg]:ltr:md:ml-vertical-menu group-data-[sidebar-size=lg]:rtl:md:mr-vertical-menu group-data-[sidebar-size=md]:ltr:ml-vertical-menu-md group-data-[sidebar-size=md]:rtl:mr-vertical-menu-md group-data-[sidebar-size=sm]:ltr:ml-vertical-menu-sm group-data-[sidebar-size=sm]:rtl:mr-vertical-menu-sm pt-[calc(theme('spacing.header')_*_1)] pb-[calc(theme('spacing.header')_*_0.8)] px-4 group-data-[navbar=bordered]:pt-[calc(theme('spacing.header')_*_1.3)] group-data-[navbar=hidden]:pt-0 group-data-[layout=horizontal]:mx-auto group-data-[layout=horizontal]:max-w-screen-2xl group-data-[layout=horizontal]:px-0 group-data-[layout=horizontal]:group-data-[sidebar-size=lg]:ltr:md:ml-auto group-data-[layout=horizontal]:group-data-[sidebar-size=lg]:rtl:md:mr-auto group-data-[layout=horizontal]:md:pt-[calc(theme('spacing.header')_*_1.6)] group-data-[layout=horizontal]:px-3 group-data-[layout=horizontal]:group-data-[navbar=hidden]:pt-[calc(theme('spacing.header')_*_0.9)]">
    <div class="container-fluid group-data-[content=boxed]:max-w-boxed mx-auto">

        {{-- Breadcrumb --}}
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

        {{-- Filtros --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
                    <div class="xl:col-span-4">
                        <label class="inline-block mb-2 text-base font-medium">Estado</label>
                        <select id="filtroEstado" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 dark:text-zink-100 dark:bg-zink-700 w-full">
                            <option value="">Todos</option>
                            <option value="activo">Activo</option>
                            <option value="vacaciones">Vacaciones</option>
                            <option value="licencia">Licencia</option>
                            <option value="suspendido">Suspendido</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                    <div class="xl:col-span-4">
    <label class="inline-block mb-2 text-base font-medium">Cargo</label>
    <select id="filtroCargo" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 dark:text-zink-100 dark:bg-zink-700 w-full">
        <option value="">Todos</option>
        @foreach($cargos as $c)
        <option>{{ $c->nombre }}</option>
        @endforeach
    </select>
</div>
                    <div class="xl:col-span-4 flex items-end gap-2">
                        <button type="button" id="btnImportar" data-modal-target="modalImportar"
                                class="text-white btn bg-purple-500 border-purple-500 hover:bg-purple-600 w-full">
                            <i data-lucide="upload" class="inline-block size-4"></i>
                            <span class="align-middle">Importar</span>
                        </button>
                        <button type="button" id="btnNuevo" data-modal-target="modalEmpleado"
                                class="text-white btn bg-custom-500 border-custom-500 hover:text-white hover:bg-custom-600 w-full">
                            <i data-lucide="plus" class="inline-block size-4"></i>
                            <span class="align-middle">Nuevo Colaborador</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card">
            <div class="card-body">
                <div class="flex items-center">
                    <h6 class="text-15 grow">Lista de Colaboradores</h6>
                </div>
                <br>
                <table id="tablaEmpleados" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">Foto</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">CI</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">NOMBRE COMPLETO</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">CARGO</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">ESTADO</th>
                            <th class="px-3.5 py-2.5 font-semibold border border-slate-200 dark:border-zink-500">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($empleados as $e)
                        <tr>
                            <td class="text-center">
                                @if($e->foto)
                                    <img src="{{ asset('assets/images/empleados/'.$e->foto) }}" class="rounded-full mx-auto" width="40" height="40" style="object-fit:cover;">
                                @else
                                    <span class="px-2 py-0.5 text-xs rounded border bg-slate-100 border-slate-200 text-slate-500 dark:bg-slate-500/20 dark:text-zink-200">N/A</span>
                                @endif
                            </td>
                            <td>{{ $e->numero_documento }}</td>
                            <td>{{ $e->apellidos }}, {{ $e->nombres }}</td>
                            <td>{{ $e->cargo->nombre ?? '-' }}</td>
                            <td>
                                <span class="px-2.5 py-0.5 text-xs inline-block font-medium rounded border {{
                                    match($e->estado) {
                                        'activo'     => 'bg-green-100 border-green-200 text-green-500 dark:bg-green-500/20',
                                        'vacaciones' => 'bg-info-100 border-info-200 text-info-500 dark:bg-info-500/20',
                                        'licencia'   => 'bg-warning-100 border-warning-200 text-warning-500 dark:bg-warning-500/20',
                                        'suspendido' => 'bg-danger-100 border-danger-200 text-danger-500 dark:bg-danger-500/20',
                                        default      => 'bg-slate-100 border-slate-200 text-slate-500 dark:bg-slate-500/20 dark:text-zink-200'
                                    }
                                }}">{{ ucfirst($e->estado) }}</span>
                            </td>
                            <td class="text-center">
                                <div class="flex gap-2 justify-center">
                                    <button class="inline-flex items-center justify-center rounded-md size-8 bg-slate-100 text-slate-500 hover:text-blue-500 hover:bg-blue-100 dark:bg-zink-600 dark:text-zink-200 dark:hover:bg-blue-500/20 dark:hover:text-blue-500"
                                            onclick="verEmpleado({{ $e->id }})" title="Ver detalle">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                    <button class="inline-flex items-center justify-center rounded-md size-8 bg-slate-100 text-slate-500 hover:text-custom-500 hover:bg-custom-100 dark:bg-zink-600 dark:text-zink-200 dark:hover:bg-custom-500/20 dark:hover:text-custom-500"
                                            onclick="editarEmpleado({{ $e->id }})" title="Editar">
                                        <i class="ri-pencil-line"></i>
                                    </button>
                                    <button class="inline-flex items-center justify-center rounded-md size-8 bg-slate-100 text-slate-500 hover:text-red-500 hover:bg-red-100 dark:bg-zink-600 dark:text-zink-200 dark:hover:bg-red-500/20 dark:hover:text-red-500"
                                            onclick="confirmarAnular({{ $e->id }}, '{{ addslashes($e->apellidos.', '.$e->nombres) }}')"
                                            title="Anular" @if($e->estado === 'inactivo') disabled @endif>
                                        <i class="ri-close-circle-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================= --}}
{{-- BOTONES OCULTOS: disparan modales vía theme (igual que Liquidaciones) --}}
{{-- ========================================================= --}}
<div style="display:none">
    <button type="button" id="__openEmpleado" data-modal-target="modalEmpleado"></button>
    <button type="button" id="__openAnular"   data-modal-target="modalAnular"></button>
    <button type="button" id="__openVer"      data-modal-target="modalVer"></button>
</div>

{{-- Modal Crear/Editar --}}
<div id="modalEmpleado" modal-center="" class="fixed flex flex-col hidden transition-all duration-300 ease-in-out left-2/4 z-drawer -translate-x-2/4 -translate-y-2/4 show">
    <div class="w-screen md:w-[40rem] bg-white shadow rounded-md dark:bg-zink-600">
        <div class="flex items-center justify-between p-4 border-b dark:border-zink-500">
            <h5 class="text-16" id="modalTitulo">Nuevo Colaborador</h5>
            <button data-modal-close="modalEmpleado" class="transition-all duration-200 ease-linear text-slate-400 hover:text-red-500">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="max-h-[calc(theme('height.screen')_-_180px)] p-4 overflow-y-auto">
            @if($errors->any())
            <div class="p-3 mb-4 text-sm text-red-500 border border-red-200 rounded-md bg-red-50 dark:bg-red-400/20 dark:border-red-500/50">
                <strong>Corrige los siguientes campos:</strong>
                <ul class="mt-1 list-disc list-inside">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
            </div>
            @endif

            <form id="formEmpleado" method="POST" enctype="multipart/form-data" action="{{ $actionForm }}">
                @csrf
                <div id="methodField">@if($huboError && $modoEditar)<input type="hidden" name="_method" value="PUT">@endif</div>
                <input type="hidden" name="modo" value="{{ $modoEditar ? 'editar' : 'crear' }}">
                <input type="hidden" id="empleado_id" name="empleado_id" value="{{ old('empleado_id') }}">

                <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
                    <div class="xl:col-span-4">
                        <label class="inline-block mb-2 text-base font-medium">Nombres *</label>
                        <input type="text" name="nombres" value="{{ old('nombres') }}" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full" required>
                    </div>
                    <div class="xl:col-span-4">
                        <label class="inline-block mb-2 text-base font-medium">Apellidos *</label>
                        <input type="text" name="apellidos" value="{{ old('apellidos') }}" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full" required>
                    </div>
                    <div class="xl:col-span-4">
                        <label class="inline-block mb-2 text-base font-medium">Foto</label>
                        <input type="file" name="foto" id="foto" accept="image/jpeg,image/png" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full">
                        <img id="fotoPreview" class="hidden mt-2 rounded-full size-16 object-cover" alt="">
                    </div>

                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Tipo Doc. *</label>
                        <select name="tipo_documento" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full" required>
                            @foreach(['CI' => 'CI', 'RUC' => 'RUC', 'PASAPORTE' => 'Pasaporte'] as $val => $lbl)
                            <option value="{{ $val }}" {{ old('tipo_documento', 'CI') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Nro. Documento *</label>
                        <input type="text" name="numero_documento" value="{{ old('numero_documento') }}" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full" required>
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Fecha Nac.</label>
                        <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full">
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Sexo</label>
                        <select name="sexo" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full">
                            @foreach(['M' => 'Masculino', 'F' => 'Femenino', 'OTRO' => 'Otro'] as $val => $lbl)
                            <option value="{{ $val }}" {{ old('sexo', 'M') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Estado Civil</label>
                        <select name="estado_civil" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full">
                            @foreach(['soltero' => 'Soltero/a', 'casado' => 'Casado/a', 'divorciado' => 'Divorciado/a', 'viudo' => 'Viudo/a', 'concubino' => 'Concubino/a'] as $val => $lbl)
                            <option value="{{ $val }}" {{ old('estado_civil', 'soltero') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Nacionalidad</label>
                        <input type="text" name="nacionalidad" value="{{ old('nacionalidad', 'paraguaya') }}" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full">
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Teléfono</label>
                        <input type="text" name="telefono" value="{{ old('telefono') }}" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full">
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Correo</label>
                        <input type="email" name="correo" value="{{ old('correo') }}" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full">
                    </div>

                    <div class="xl:col-span-6">
                        <label class="inline-block mb-2 text-base font-medium">Dirección</label>
                        <input type="text" name="direccion" value="{{ old('direccion') }}" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full">
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Dpto. Residencia</label>
                        <input type="text" name="departamento_residencia" id="departamento_residencia" value="{{ old('departamento_residencia') }}" list="listaDeptosRes" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full" placeholder="Escriba para buscar...">
                        <datalist id="listaDeptosRes"></datalist>
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Ciudad Residencia</label>
                        <input type="text" name="ciudad_residencia" id="ciudad_residencia" value="{{ old('ciudad_residencia') }}" list="listaCiudadesRes" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full" placeholder="Escriba para buscar...">
                        <datalist id="listaCiudadesRes"></datalist>
                    </div>

                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Código Empleado *</label>
                        <input type="text" name="codigo_empleado" id="codigo_empleado" value="{{ old('codigo_empleado', $siguienteCodigo) }}" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full" required>
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Empresa *</label>
                        <select name="empresa_id" id="empresa_id" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full" required>
                            <option value="">Seleccionar</option>
                            @foreach($empresas as $emp)
                            <option value="{{ $emp->id }}" {{ (string) old('empresa_id') === (string) $emp->id ? 'selected' : '' }}>{{ $emp->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Sucursal *</label>
                        <select name="sucursal_id" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full" required>
                            <option value="">Seleccionar</option>
                            @foreach($sucursales as $suc)
                            <option value="{{ $suc->id }}" {{ (string) old('sucursal_id') === (string) $suc->id ? 'selected' : '' }}>{{ $suc->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Departamento</label>
                        <select name="departamento_id" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full">
                            <option value="">Seleccionar</option>
                            @foreach($departamentos as $dep)
                            <option value="{{ $dep->id }}" {{ (string) old('departamento_id') === (string) $dep->id ? 'selected' : '' }}>{{ $dep->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Cargo *</label>
                        <select name="cargo_id" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full" required>
                            <option value="">Seleccionar</option>
                            @foreach($cargos as $c)
                            <option value="{{ $c->id }}" {{ (string) old('cargo_id') === (string) $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Tipo Contrato</label>
                        <select name="tipo_contrato_id" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full">
                            <option value="">Seleccionar</option>
                            @foreach($tiposContrato as $tc)
                            <option value="{{ $tc->id }}" {{ (string) old('tipo_contrato_id') === (string) $tc->id ? 'selected' : '' }}>{{ $tc->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Horario</label>
                        <select name="horario_id" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full">
                            <option value="">Seleccionar</option>
                            @foreach($horarios as $h)
                            <option value="{{ $h->id }}" {{ (string) old('horario_id') === (string) $h->id ? 'selected' : '' }}>{{ $h->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Jefe Inmediato</label>
                        <select name="jefe_inmediato_id" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full">
                            <option value="">Ninguno</option>
                            @foreach($jefes as $j)
                            <option value="{{ $j->id }}" {{ (string) old('jefe_inmediato_id') === (string) $j->id ? 'selected' : '' }}>{{ $j->apellidos }}, {{ $j->nombres }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Fecha Ingreso *</label>
                        <input type="date" name="fecha_ingreso" value="{{ old('fecha_ingreso') }}" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full" required>
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Salario Base (Gs) *</label>
                        <input type="number" name="salario_base" value="{{ old('salario_base') }}" min="0" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full" required>
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Nro. IPS</label>
                        <input type="text" name="numero_ips" value="{{ old('numero_ips') }}" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full">
                    </div>
                    <div class="xl:col-span-3">
                        <label class="inline-block mb-2 text-base font-medium">Profesión</label>
                        <input type="text" name="profesion" value="{{ old('profesion') }}" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full">
                    </div>

                    <div class="xl:col-span-12">
                        <label class="inline-block mb-2 text-base font-medium">Estado Laboral *</label>
                        <select name="estado" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full" required>
                            @foreach(['activo' => 'Activo', 'vacaciones' => 'Vacaciones', 'licencia' => 'Licencia', 'suspendido' => 'Suspendido', 'inactivo' => 'Inactivo'] as $val => $lbl)
                            <option value="{{ $val }}" {{ old('estado', 'activo') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="reset" data-modal-close="modalEmpleado" class="text-red-500 bg-white btn hover:text-red-500 hover:bg-red-100 dark:bg-zink-600 dark:hover:bg-red-500/10">Cancelar</button>
                    <button type="submit" class="text-white btn bg-custom-500 border-custom-500 hover:bg-custom-600">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Anular --}}
<div id="modalAnular" modal-center="" class="fixed flex flex-col hidden transition-all duration-300 ease-in-out left-2/4 z-drawer -translate-x-2/4 -translate-y-2/4 show">
    <div class="w-screen md:w-[30rem] bg-white shadow rounded-md dark:bg-zink-600">
        <div class="px-6 py-8">
            <div class="float-right">
                <button data-modal-close="modalAnular" class="text-slate-500 hover:text-red-500">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <i data-lucide="user-x" class="block h-12 mx-auto text-red-500"></i>
            <div class="mt-5 text-center">
                <h5 class="mb-1">¿Anular colaborador?</h5>
                <p class="text-slate-500 dark:text-zink-200">El estado pasará a <strong>Inactivo</strong>. El registro no se elimina.</p>
                <p class="text-slate-500 dark:text-zink-200">Colaborador: <strong id="nombreAnular"></strong></p>
                <div class="flex justify-center gap-2 mt-6">
                    <form id="formAnular" method="POST">
                        @csrf
                        <div class="flex gap-2">
                            <button type="reset" data-modal-close="modalAnular" class="bg-white text-slate-500 btn hover:bg-slate-100 dark:bg-zink-600">Cancelar</button>
                            <button type="submit" class="text-white bg-red-500 border-red-500 btn hover:bg-red-600">Sí, Anular</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Importar --}}
<div id="modalImportar" modal-center="" class="fixed flex flex-col hidden transition-all duration-300 ease-in-out left-2/4 z-drawer -translate-x-2/4 -translate-y-2/4 show">
    <div class="w-screen md:w-[30rem] bg-white shadow rounded-md dark:bg-zink-600">
        <div class="flex items-center justify-between p-4 border-b dark:border-zink-500">
            <h5 class="text-16">Importar Colaboradores</h5>
            <button data-modal-close="modalImportar" class="text-slate-400 hover:text-red-500">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="p-4">
            <p class="mb-3 text-sm text-slate-500 dark:text-zink-200">Subí un archivo <strong>.xlsx</strong> o <strong>.csv</strong>.</p>
            <input type="file" id="archivoImportar" accept=".xlsx,.xls,.csv" class="form-input border-slate-200 dark:border-zink-500 dark:text-zink-100 dark:bg-zink-700 w-full">
            <div class="flex justify-between gap-2 mt-4">
                <button type="button" onclick="descargarPlantilla()" class="btn bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-zink-500/20">
                    <i data-lucide="download" class="inline-block size-4"></i> Plantilla
                </button>
                <button type="reset" data-modal-close="modalImportar" class="text-red-500 bg-white btn hover:bg-red-100 dark:bg-zink-600">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Ver Detalle --}}
<div id="modalVer" modal-center="" class="fixed flex flex-col hidden transition-all duration-300 ease-in-out left-2/4 z-drawer -translate-x-2/4 -translate-y-2/4 show">
    <div class="w-screen md:w-[40rem] bg-white shadow rounded-md dark:bg-zink-600">
        <div class="flex items-center justify-between p-4 border-b dark:border-zink-500">
            <h5 class="text-16">Detalle del Colaborador</h5>
            <button data-modal-close="modalVer" class="text-slate-400 hover:text-red-500">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="max-h-[calc(theme('height.screen')_-_180px)] p-4 overflow-y-auto" id="verContenido"></div>
        <div class="flex justify-end p-4 border-t dark:border-zink-500">
            <button type="reset" data-modal-close="modalVer" class="text-slate-500 bg-white btn hover:bg-slate-100 dark:bg-zink-600">Cerrar</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
$(document).ready(function () {
    const rutaBase      = "{{ url('hr/empleados') }}";
    const rutaImportar  = "{{ route('empleados.importar') }}";
    const token         = "{{ csrf_token() }}";
    const codigoInicial = @json($siguienteCodigo ?? 'EMP001');

    const F = {
        ok:   (window.flasher && flasher.success) ? flasher.success.bind(flasher) : (m) => alert(m),
        warn: (window.flasher && flasher.warning) ? flasher.warning.bind(flasher) : (m) => alert(m),
        err:  (window.flasher && flasher.error)   ? flasher.error.bind(flasher)   : (m) => alert(m),
        info: (window.flasher && flasher.info)    ? flasher.info.bind(flasher)    : (m) => console.log(m),
    };

    /* ============ DataTable ============ */
    const tabla = $('#tablaEmpleados').DataTable({
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[2, 'asc']],
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
        dom: "Bfrtip",
        buttons: [
            { extend: 'excelHtml5', text: 'Excel', className: 'btn bg-green-500 text-white hover:bg-green-600',
              title: 'Listado de Colaboradores', exportOptions: { columns: [1, 2, 3, 4] } },
            { extend: 'pdfHtml5', text: 'PDF', className: 'btn bg-red-500 text-white hover:bg-red-600',
              title: 'Listado de Colaboradores', orientation: 'landscape', pageSize: 'A4',
              exportOptions: { columns: [1, 2, 3, 4] } },
            { extend: 'print', text: 'Imprimir', className: 'btn bg-slate-500 text-white hover:bg-slate-600',
              title: 'Listado de Colaboradores', exportOptions: { columns: [1, 2, 3, 4] } }
        ],
        responsive: true
    });

    /* Filtros */
    $('#filtroEstado').on('change', function () {
        const val = $(this).val();
        tabla.column(4).search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false).draw();
    });

$('#filtroCargo').on('change', function () {
    const val = $(this).val();
    // Columna Cargo = índice 3
    tabla.column(3).search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false).draw();
});

    /* ============ Abrir/cerrar modales (mismo patrón que Liquidaciones) ============ */
const setModal = (id, open) => {
    // Mapeo de modales a botones ocultos del theme
    const triggerMap = {
        modalEmpleado: '__openEmpleado',
        modalAnular:   '__openAnular',
        modalVer:      '__openVer',
        modalImportar: 'btnImportar',
    };

    const el = document.getElementById(id);
    if (!el) return;

    if (open) {
        // Si existe botón oculto con data-modal-target, hacer click en él
        // así el theme hace su trabajo (backdrop, body lock, etc.)
        const trigger = document.getElementById(triggerMap[id]);
        if (trigger && trigger.hasAttribute('data-modal-target')) {
            trigger.click();
            return;
        }
        // Fallback: abrir manualmente
        el.classList.remove('hidden');
        el.classList.add('show', 'flex');
    } else {
        // Cerrar: buscar botón con data-modal-close y hacer click
        const closeBtn = el.querySelector('[data-modal-close="' + id + '"]');
        if (closeBtn) { closeBtn.click(); return; }

        // Fallback: cerrar a mano + limpiar backdrop
        el.classList.add('hidden');
        el.classList.remove('show', 'flex');
        document.querySelectorAll('.modal-backdrop, [data-modal-backdrop], .backdrop').forEach(x => x.remove());
        document.body.classList.remove('overflow-hidden', 'modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    }
    if (window.lucide) lucide.createIcons();
};

window.abrirModal  = (id) => setModal(id, true);
window.cerrarModal = (id) => setModal(id, false);

    /* ============ Helpers ============ */
    const cargarDeptosResidencia = () => {
        const dl = document.getElementById('listaDeptosRes');
        if (!dl) return;
        const deptos = ['Asunción','Central','Alto Paraná','Alto Paraguay','Amambay','Boquerón','Caaguazú','Caazapá','Canindeyú','Concepción','Cordillera','Guairá','Itapúa','Misiones','Ñeembucú','Paraguarí','Presidente Hayes','San Pedro'];
        dl.innerHTML = deptos.map(d => `<option value="${d}">`).join('');
    };

    const cargarCiudadesResidencia = () => {
        const dep = document.getElementById('departamento_residencia');
        const dl  = document.getElementById('listaCiudadesRes');
        if (!dep || !dl) return;
        const mapa = {
            'Asunción':['Asunción'],
            'Central':['Asunción','San Lorenzo','Fernando de la Mora','Luque','Capiatá','Lambaré','Ñemby','Itauguá','Mariano Roque Alonso','Villa Elisa','Guarambaré','Itá','Ypané','Limpio','San Antonio','Areguá'],
            'Alto Paraná':['Ciudad del Este','Hernandarias','Presidente Franco','Minga Guazú','Santa Rita','Naranjal'],
            'Itapúa':['Encarnación','Hohenau','Obligado','San Juan del Paraná','Tomás Romero Pereira'],
            'Misiones':['San Ignacio','San Juan Bautista','Santiago','Ayolas','San Miguel'],
            'Paraguarí':['Paraguarí','Carapeguá','Yaguarón','Pirayú','Quiindy'],
            'Cordillera':['Caacupé','Villarrica','Piribebuy','Tobatí','Valenzuela'],
            'Caaguazú':['Coronel Oviedo','Caaguazú','Repatriación','J. Eulogio Estigarribia'],
        };
        dl.innerHTML = (mapa[dep.value.trim()] || []).map(c => `<option value="${c}">`).join('');
    };

    const depRes = document.getElementById('departamento_residencia');
    if (depRes) depRes.addEventListener('change', cargarCiudadesResidencia);

    const fotoInput = document.getElementById('foto');
    if (fotoInput) {
        fotoInput.addEventListener('change', function () {
            const prev = document.getElementById('fotoPreview');
            if (prev && this.files && this.files[0]) {
                prev.src = URL.createObjectURL(this.files[0]);
                prev.classList.remove('hidden');
            }
        });
    }

    /* ============ CRUD ============ */
    window.nuevoEmpleado = function () {
        const form = document.getElementById('formEmpleado');
        if (!form) return;
        form.reset();
        document.getElementById('empleado_id').value = '';
        document.getElementById('methodField').innerHTML = '';
        form.action = "{{ route('empleados.store') }}";
        form.querySelector('[name=modo]').value = 'crear';
        document.getElementById('modalTitulo').textContent = 'Nuevo Colaborador';
        const prev = document.getElementById('fotoPreview');
        if (prev) prev.classList.add('hidden');
        const cod = document.getElementById('codigo_empleado');
        if (cod) cod.value = codigoInicial;
        cargarDeptosResidencia();
        cargarCiudadesResidencia();
        abrirModal('modalEmpleado');
    };

    window.editarEmpleado = function (id) {
        fetch(`${rutaBase}/${id}/ver`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        }).then(data => {
            const form = document.getElementById('formEmpleado');
            form.reset();
            const set = (name, val) => {
                const el = form.querySelector(`[name="${name}"]`);
                if (el && val !== null && val !== undefined) el.value = val;
            };
            const soloFecha = v => v ? String(v).substring(0, 10) : '';
            set('nombres', data.nombres); set('apellidos', data.apellidos);
            set('tipo_documento', data.tipo_documento); set('numero_documento', data.numero_documento);
            set('fecha_nacimiento', soloFecha(data.fecha_nacimiento));
            set('sexo', data.sexo); set('estado_civil', data.estado_civil);
            set('nacionalidad', data.nacionalidad);
            set('telefono', data.telefono); set('correo', data.correo); set('direccion', data.direccion);
            set('departamento_residencia', data.departamento_residencia);
            set('ciudad_residencia', data.ciudad_residencia);
            set('codigo_empleado', data.codigo_empleado);
            set('empresa_id', data.empresa_id); set('sucursal_id', data.sucursal_id);
            set('departamento_id', data.departamento_id); set('cargo_id', data.cargo_id);
            set('tipo_contrato_id', data.tipo_contrato_id); set('horario_id', data.horario_id);
            set('jefe_inmediato_id', data.jefe_inmediato_id);
            set('fecha_ingreso', soloFecha(data.fecha_ingreso));
            set('fecha_egreso', soloFecha(data.fecha_egreso));
            set('salario_base', data.salario_base);
            set('numero_ips', data.numero_ips); set('profesion', data.profesion);
            set('estado', data.estado);

            document.getElementById('empleado_id').value = data.id;
            document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
            form.action = `${rutaBase}/${data.id}/actualizar`;
            form.querySelector('[name=modo]').value = 'editar';
            document.getElementById('modalTitulo').textContent = 'Editar Colaborador';

            const prev = document.getElementById('fotoPreview');
            if (prev) {
                if (data.foto) { prev.src = `/assets/images/empleados/${data.foto}`; prev.classList.remove('hidden'); }
                else prev.classList.add('hidden');
            }
            cargarDeptosResidencia();
            cargarCiudadesResidencia();
            abrirModal('modalEmpleado');
        }).catch(err => {
            console.error(err);
            F.err('No se pudo cargar el colaborador.');
        });
    };

    window.confirmarAnular = function (id, nombre) {
        document.getElementById('nombreAnular').textContent = nombre;
        document.getElementById('formAnular').action = `${rutaBase}/${id}/anular`;
        abrirModal('modalAnular');
    };

    window.verEmpleado = function (id) {
        fetch(`${rutaBase}/${id}/ver`, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(d => {
                const fmt = v => (v !== null && v !== undefined && v !== '') ? String(v) : '—';
                const fecha = v => v ? String(v).substring(0, 10) : '—';
                const fila = (label, val) => `
                    <div class="flex justify-between py-2 border-b border-slate-100 dark:border-zink-500">
                        <span class="text-slate-500 dark:text-zink-300 text-sm">${label}</span>
                        <span class="font-medium text-slate-700 dark:text-zink-100 text-sm text-right">${fmt(val)}</span>
                    </div>`;
                const foto = d.foto
                    ? `<img src="/assets/images/empleados/${d.foto}" class="rounded-full mx-auto mb-4" width="100" height="100" style="object-fit:cover;">`
                    : `<div class="rounded-full mx-auto mb-4 flex items-center justify-center bg-slate-100 dark:bg-zink-700" style="width:100px;height:100px;"><i data-lucide="user" class="w-10 h-10 text-slate-400"></i></div>`;

                document.getElementById('verContenido').innerHTML = `
                    ${foto}
                    <h5 class="text-center text-16 mb-4">${fmt(d.apellidos)}, ${fmt(d.nombres)}</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                        <div>
                            ${fila('Código', d.codigo_empleado)}
                            ${fila('Documento', (d.tipo_documento||'') + ' ' + (d.numero_documento||''))}
                            ${fila('Fecha Nacimiento', fecha(d.fecha_nacimiento))}
                            ${fila('Sexo', d.sexo)}
                            ${fila('Estado Civil', d.estado_civil)}
                            ${fila('Nacionalidad', d.nacionalidad)}
                            ${fila('Teléfono', d.telefono)}
                            ${fila('Correo', d.correo)}
                            ${fila('Dirección', d.direccion)}
                            ${fila('Dpto. Residencia', d.departamento_residencia)}
                            ${fila('Ciudad Residencia', d.ciudad_residencia)}
                        </div>
                        <div>
                            ${fila('Empresa', d.empresa?.nombre)}
                            ${fila('Sucursal', d.sucursal?.nombre)}
                            ${fila('Departamento', d.departamento?.nombre)}
                            ${fila('Cargo', d.cargo?.nombre)}
                            ${fila('Tipo Contrato', d.tipo_contrato?.nombre)}
                            ${fila('Horario', d.horario?.nombre)}
                            ${fila('Fecha Ingreso', fecha(d.fecha_ingreso))}
                            ${fila('Fecha Egreso', fecha(d.fecha_egreso))}
                            ${fila('Salario Base', d.salario_base ? Number(d.salario_base).toLocaleString('es-PY') + ' Gs' : '—')}
                            ${fila('Nro. IPS', d.numero_ips)}
                            ${fila('Profesión', d.profesion)}
                            ${fila('Estado', d.estado)}
                        </div>
                    </div>`;
                abrirModal('modalVer');
            })
            .catch(err => {
                console.error(err);
                F.err('No se pudo cargar el detalle.');
            });
    };

    /* ============ Importar ============ */
    const MAPA_COLUMNAS = {
        'nombres':'nombres','apellidos':'apellidos',
        'tipo documento':'tipo_documento','tipo_documento':'tipo_documento','tipo doc':'tipo_documento',
        'numero documento':'numero_documento','numero_documento':'numero_documento','documento':'numero_documento','ci':'numero_documento','nro documento':'numero_documento',
        'fecha nacimiento':'fecha_nacimiento','fecha_nacimiento':'fecha_nacimiento',
        'sexo':'sexo','estado civil':'estado_civil','estado_civil':'estado_civil','nacionalidad':'nacionalidad',
        'direccion':'direccion','departamento residencia':'departamento_residencia','ciudad residencia':'ciudad_residencia',
        'telefono':'telefono','correo':'correo','email':'correo',
        'codigo empleado':'codigo_empleado','codigo':'codigo_empleado',
        'empresa':'empresa','sucursal':'sucursal','departamento':'departamento','cargo':'cargo',
        'tipo contrato':'tipo_contrato','horario':'horario',
        'fecha ingreso':'fecha_ingreso','salario base':'salario_base','salario':'salario_base',
        'numero ips':'numero_ips','nro ips':'numero_ips','ips':'numero_ips','profesion':'profesion','estado':'estado'
    };
    const normTxt = s => String(s).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();

    const archivoImp = document.getElementById('archivoImportar');
    if (archivoImp) {
        archivoImp.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function (ev) {
                try {
                    const wb = XLSX.read(ev.target.result, { type: 'array' });
                    const json = XLSX.utils.sheet_to_json(wb.Sheets[wb.SheetNames[0]], { defval: '' });
                    const filas = json.map(row => {
                        const o = {};
                        Object.entries(row).forEach(([k, v]) => {
                            const clave = MAPA_COLUMNAS[normTxt(k)];
                            if (clave) o[clave] = (typeof v === 'string') ? v.trim() : v;
                        });
                        return o;
                    }).filter(f => f.nombres || f.apellidos || f.numero_documento);

                    if (!filas.length) { F.err('El archivo no contiene filas válidas.'); return; }
                    F.info(`Procesando ${filas.length} fila(s)...`);

                    fetch(rutaImportar, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                        body: JSON.stringify({ empleados: filas })
                    }).then(r => r.json()).then(res => {
                        cerrarModal('modalImportar');
                        if (res.ok) {
                            let msg = `Importación exitosa: ${res.importados} colaborador(es)`;
                            if (res.fallidos) msg += ` — ${res.fallidos} fila(s) con error`;
                            (res.fallidos ? F.warn : F.ok)(msg);
                            if (res.errores && res.errores.length) console.warn('Errores:', res.errores);
                            setTimeout(() => location.reload(), 2500);
                        } else {
                            F.err(res.mensaje || 'No se pudo importar.');
                        }
                    }).catch(() => F.err('Error al comunicar con el servidor.'));
                } catch (err) {
                    F.err('No se pudo leer el archivo.');
                }
                e.target.value = '';
            };
            reader.readAsArrayBuffer(file);
        });
    }

    window.descargarPlantilla = function () {
        const encabezados = ['nombres','apellidos','tipo_documento','numero_documento','fecha_nacimiento','sexo','estado_civil','nacionalidad','direccion','departamento_residencia','ciudad_residencia','telefono','correo','codigo_empleado','empresa','sucursal','departamento','cargo','tipo_contrato','horario','fecha_ingreso','salario_base','numero_ips','profesion','estado'];
        const ejemplo = ['JUAN','PEREZ','CI','123456','1980-01-01','M','soltero','paraguaya','Av. España 456','Central','Asunción','0981-123456','juan@correo.com','EMP001','Mi Empresa S.A.','Casa Central','Gerencia','Gerente General','Indefinido','Jornada Completa','2024-01-15','3044000','123456','Ingeniero','activo'];
        const ws = XLSX.utils.aoa_to_sheet([encabezados, ejemplo]);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Colaboradores');
        XLSX.writeFile(wb, 'plantilla_colaboradores.xlsx');
    };

    /* Reabrir modal si hubo error de validación */
    @if($errors->any())
        cargarDeptosResidencia();
        cargarCiudadesResidencia();
        abrirModal('modalEmpleado');
    @endif

    if (window.lucide) lucide.createIcons();
});
</script>
@endsection