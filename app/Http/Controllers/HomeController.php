<?php
// app/Http/Controllers/HomeController.php
namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Departamento;
use App\Models\Cargo;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // ============ KPIs principales ============
        $totalEmpleadosActivos = Empleado::where('estado', 'activo')->count();
        $totalEmpleados        = Empleado::count();
        $totalDepartamentos    = Departamento::count();
        $totalCargos           = Cargo::count();

        $salarioPromedio = Empleado::where('estado', 'activo')->avg('salario_base') ?? 0;

        // Nuevos ingresos del mes actual
        $nuevosIngresos = Empleado::whereMonth('fecha_ingreso', now()->month)
            ->whereYear('fecha_ingreso', now()->year)
            ->count();

        // ============ Distribución por estado (para gráfico) ============
        $porEstado = Empleado::select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();

        // ============ Distribución por departamento (para gráfico) ============
        $porDepartamento = Empleado::where('empleados.estado', 'activo')
            ->join('departamentos', 'departamentos.id', '=', 'empleados.departamento_id')
            ->select('departamentos.nombre', DB::raw('COUNT(*) as total'))
            ->groupBy('departamentos.nombre')
            ->orderByDesc('total')
            ->limit(6)
            ->pluck('total', 'nombre')
            ->toArray();

        // ============ Colaboradores recientes ============
        $empleados = Empleado::with(['cargo', 'departamento'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.home', compact(
            'totalEmpleadosActivos',
            'totalEmpleados',
            'totalDepartamentos',
            'totalCargos',
            'salarioPromedio',
            'nuevosIngresos',
            'porEstado',
            'porDepartamento',
            'empleados'
        ));
    }
}