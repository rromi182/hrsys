<?php

namespace App\Http\Controllers;

use App\Models\Empleado; // Asegúrate de tener este modelo
use App\Models\Departamento;
use App\Models\Cargo;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Estadísticas reales
        $totalEmpleadosActivos = Empleado::where('estado', 'activo')->count();
        $totalEmpleados = Empleado::count();
        $totalDepartamentos = Departamento::count();
        $totalCargos = Cargo::count();
        
        // Salario promedio de empleados activos
        $salarioPromedio = Empleado::where('estado', 'activo')->avg('salario_base') ?? 0;
        
        // Nuevos ingresos del mes actual
        $nuevosIngresos = Empleado::whereMonth('fecha_ingreso', now()->month)
            ->whereYear('fecha_ingreso', now()->year)
            ->count();
        
        // Colaboradores recientes (últimos 5)
        $colaboradoresRecientes = Empleado::with(['cargo', 'departamento'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Datos para la tabla de colaboradores recientes (ejemplo con datos de prueba)
        // En producción, estos vendrían de tu base de datos
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
            'colaboradoresRecientes',
            'empleados'
        ));
    }
}