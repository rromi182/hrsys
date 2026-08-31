<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\Departamento;
use App\Models\Cargo;
use App\Models\HorarioLaboral;
use App\Models\TipoContrato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmpleadoController extends Controller
{
    /** Listado de empleados */
    public function index()
    {
        $empleados = Empleado::with(['empresa', 'sucursal', 'departamento', 'cargo'])
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        // Datos para los selects del modal
        $empresas = Empresa::where('estado', 1)->get();
        $sucursales = Sucursal::where('estado', 1)->get();
        $departamentos = Departamento::where('estado', 1)->get();
        $cargos = Cargo::where('estado', 1)->get();
        $horarios = HorarioLaboral::where('estado', 1)->get();
        $tiposContrato = TipoContrato::all();

        return view('HR.empleados.index', compact(
            'empleados', 'empresas', 'sucursales', 'departamentos', 'cargos', 'horarios', 'tiposContrato'
        ));
    }

    /** Guardar nuevo empleado */
    public function store(Request $request)
    {
        $request->validate([
            'nombres' => 'required|string|max:50',
            'apellidos' => 'required|string|max:50',
            'tipo_documento' => 'required|in:CI,RUC,PASAPORTE',
            'numero_documento' => 'required|string|max:20|unique:empleados,numero_documento',
            'empresa_id' => 'required|exists:empresas,id',
            'sucursal_id' => 'required|exists:sucursales,id',
            'cargo_id' => 'required|exists:cargos,id',
            'codigo_empleado' => 'required|string|max:20|unique:empleados,codigo_empleado',
            'fecha_ingreso' => 'required|date',
            'salario_base' => 'required|integer|min:0',
            'estado' => 'required|in:activo,vacaciones,licencia,suspendido,inactivo',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->except('foto');
        $data['creado_por'] = auth()->id();

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . \Str::slug($request->nombres . ' ' . $request->apellidos) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/images/empleados'), $filename);
            $data['foto'] = $filename;
        }

        Empleado::create($data);

        flash()->success('Empleado registrado correctamente.');
        return redirect()->route('empleados.index');
    }

    /** Ver empleado (JSON para modal o vista) */
    public function show(Empleado $empleado)
    {
        return response()->json($empleado->load(['empresa', 'sucursal', 'departamento', 'cargo']));
    }

    /** Actualizar empleado */
    public function update(Request $request, Empleado $empleado)
    {
        $request->validate([
            'nombres' => 'required|string|max:50',
            'apellidos' => 'required|string|max:50',
            'tipo_documento' => 'required|in:CI,RUC,PASAPORTE',
            'numero_documento' => 'required|string|max:20|unique:empleados,numero_documento,' . $empleado->id,
            'empresa_id' => 'required|exists:empresas,id',
            'sucursal_id' => 'required|exists:sucursales,id',
            'cargo_id' => 'required|exists:cargos,id',
            'codigo_empleado' => 'required|string|max:20|unique:empleados,codigo_empleado,' . $empleado->id,
            'fecha_ingreso' => 'required|date',
            'salario_base' => 'required|integer|min:0',
            'estado' => 'required|in:activo,vacaciones,licencia,suspendido,inactivo',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->except('foto');
        $data['actualizado_por'] = auth()->id();

        if ($request->hasFile('foto')) {
            // Eliminar foto anterior
            if ($empleado->foto && file_exists(public_path('assets/images/empleados/' . $empleado->foto))) {
                unlink(public_path('assets/images/empleados/' . $empleado->foto));
            }
            $file = $request->file('foto');
            $filename = time() . '_' . \Str::slug($request->nombres . ' ' . $request->apellidos) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/images/empleados'), $filename);
            $data['foto'] = $filename;
        }

        $empleado->update($data);

        flash()->success('Empleado actualizado correctamente.');
        return redirect()->route('empleados.index');
    }

    /** Eliminar empleado */
    public function destroy(Empleado $empleado)
    {
        if ($empleado->foto && file_exists(public_path('assets/images/empleados/' . $empleado->foto))) {
            unlink(public_path('assets/images/empleados/' . $empleado->foto));
        }
        $empleado->delete();

        flash()->success('Empleado eliminado correctamente.');
        return redirect()->route('empleados.index');
    }
}