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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EmpleadoController extends Controller
{
    /** Ruta física donde se guardan las fotos */
    private string $fotoPath = 'assets/images/empleados';

    /* =========================================================
     |  LISTADO
     * ========================================================= */
    public function index()
    {
        $empleados = Empleado::with(['empresa', 'sucursal', 'departamento', 'cargo'])
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        // Datos para los selects del modal
        $empresas      = Empresa::where('estado', 1)->orderBy('nombre')->get();
        $sucursales    = Sucursal::where('estado', 1)->orderBy('nombre')->get();
        $departamentos = Departamento::where('estado', 1)->orderBy('nombre')->get();
        $cargos        = Cargo::where('estado', 1)->orderBy('nombre')->get();
        $horarios      = HorarioLaboral::where('estado', 1)->orderBy('nombre')->get();
        $tiposContrato = TipoContrato::orderBy('nombre')->get();

        // Jefes inmediatos: empleados activos (para el select del modal)
        $jefes = Empleado::where('estado', 'activo')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get(['id', 'nombres', 'apellidos']);

        // Código autogenerado para el próximo empleado
        $siguienteCodigo = $this->generarSiguienteCodigo();

        return view('HR.empleados.index', compact(
            'empleados',
            'empresas',
            'sucursales',
            'departamentos',
            'cargos',
            'horarios',
            'tiposContrato',
            'jefes',
            'siguienteCodigo'
        ));
    }

    /* =========================================================
     |  GENERAR SIGUIENTE CÓDIGO (último número + 1)
     * ========================================================= */
    public function siguienteCodigo()
    {
        return response()->json(['codigo' => $this->generarSiguienteCodigo()]);
    }

    /**
     * Devuelve EMP + (último número + 1) con padding de 3 dígitos.
     * Ej: si el último es EMP048 => EMP049.
     * Toma solo los dígitos del código, ignora el prefijo.
     */
    private function generarSiguienteCodigo(): string
    {
        $ultimo = Empleado::whereNotNull('codigo_empleado')
            ->orderByDesc('id')
            ->value('codigo_empleado');

        $numero = 0;
        if ($ultimo && preg_match('/(\d+)/', $ultimo, $m)) {
            $numero = (int) $m[1];
        }
        // Buscar el máximo real por si el último insertado no es el mayor número
        $max = Empleado::selectRaw('MAX(CAST(REGEXP_REPLACE(codigo_empleado, "[^0-9]", "") AS UNSIGNED)) as maximo')
            ->value('maximo');
        if ($max && (int) $max > $numero) {
            $numero = (int) $max;
        }

        return 'EMP' . str_pad($numero + 1, 3, '0', STR_PAD_LEFT);
    }

    /* =========================================================
     |  GUARDAR
     * ========================================================= */
    public function store(Request $request)
    {
        $data = $this->validar($request);
        $data = $this->normalizarMayusculas($data);

        // Unicidad compuesta: (empresa_id, codigo_empleado)
        $existe = Empleado::where('empresa_id', $data['empresa_id'])
            ->where('codigo_empleado', $data['codigo_empleado'])
            ->exists();
        if ($existe) {
            return back()
                ->withErrors(['codigo_empleado' => 'Ya existe un colaborador con ese código en la empresa seleccionada.'])
                ->withInput()
                ->with('modo', 'crear');
        }

        $data['creado_por'] = auth()->id();

        if ($request->hasFile('foto')) {
            $data['foto'] = $this->guardarFoto($request, null);
        }

        Empleado::create($data);

        flash()->success('¡Colaborador registrado exitosamente! :)');
        return redirect()->route('empleados.index');
    }

    /* =========================================================
     |  VER (JSON para el modal de edición)
     * ========================================================= */
    public function show(Empleado $empleado)
    {
        return response()->json(
            $empleado->load(['empresa', 'sucursal', 'departamento', 'cargo', 'tipoContrato', 'horario'])
        );
    }

    /* =========================================================
     |  ACTUALIZAR
     * ========================================================= */
    public function update(Request $request, Empleado $empleado)
    {
        $data = $this->validar($request, $empleado->id);
        $data = $this->normalizarMayusculas($data);

        // Unicidad compuesta: (empresa_id, codigo_empleado) excluyendo el actual
        $existe = Empleado::where('empresa_id', $data['empresa_id'])
            ->where('codigo_empleado', $data['codigo_empleado'])
            ->where('id', '!=', $empleado->id)
            ->exists();
        if ($existe) {
            return back()
                ->withErrors(['codigo_empleado' => 'Ya existe otro colaborador con ese código en la empresa seleccionada.'])
                ->withInput()
                ->with('modo', 'editar')
                ->with('empleado_id', $empleado->id);
        }

        $data['actualizado_por'] = auth()->id();

        if ($request->hasFile('foto')) {
            $data['foto'] = $this->guardarFoto($request, $empleado->foto);
        }

        $empleado->update($data);

        flash()->success('¡Colaborador actualizado exitosamente! :)');
        return redirect()->route('empleados.index');
    }

    /* =========================================================
     |  ANULAR (cambia estado a inactivo, NO borra)
     * ========================================================= */
    public function anular(Empleado $empleado)
    {
        if ($empleado->estado === 'inactivo') {
            flash()->warning('El colaborador ya se encuentra inactivo.');
            return redirect()->route('empleados.index');
        }

        $empleado->update([
            'estado'          => 'inactivo',
            'actualizado_por' => auth()->id(),
        ]);

        flash()->success('¡Colaborador anulado exitosamente! :)');
        return redirect()->route('empleados.index');
    }

    /* =========================================================
     |  ELIMINAR FÍSICO (con protección de FK)
     * ========================================================= */
    public function destroy(Empleado $empleado)
    {
        try {
            if ($empleado->foto && file_exists(public_path($this->fotoPath . '/' . $empleado->foto))) {
                @unlink(public_path($this->fotoPath . '/' . $empleado->foto));
            }

            $empleado->delete();

            flash()->success('¡Colaborador eliminado exitosamente! :)');
        } catch (\Illuminate\Database\QueryException $e) {
            // Tiene registros asociados (asistencias, nómina, etc.) => anular
            $empleado->update([
                'estado'          => 'inactivo',
                'actualizado_por' => auth()->id(),
            ]);
            flash()->warning('El colaborador tiene registros asociados. Se anuló (estado: inactivo) en lugar de eliminarlo.');
        }

        return redirect()->route('empleados.index');
    }

    /* =========================================================
     |  IMPORTAR (JSON desde el navegador, xlsx/csv parseado con SheetJS)
     * ========================================================= */
    public function importar(Request $request)
    {
        $request->validate([
            'empleados'   => 'required|array|min:1',
            'empleados.*' => 'array',
        ]);

        $importados = 0;
        $fallidos   = 0;
        $errores    = [];

        DB::beginTransaction();
        try {
            foreach ($request->empleados as $i => $fila) {
                $filaNum = $i + 2; // fila real en Excel (1 = encabezado)

                try {
                    // Resolver relaciones por ID o por nombre
                    $empresaId    = $this->resolverId($fila['empresa']    ?? null, Empresa::class,      $fila['empresa_id']    ?? null);
                    $sucursalId   = $this->resolverId($fila['sucursal']   ?? null, Sucursal::class,     $fila['sucursal_id']   ?? null);
                    $deptoId      = $this->resolverId($fila['departamento'] ?? null, Departamento::class, $fila['departamento_id'] ?? null);
                    $cargoId      = $this->resolverId($fila['cargo']      ?? null, Cargo::class,        $fila['cargo_id']      ?? null);
                    $tipoContrato = $this->resolverId($fila['tipo_contrato'] ?? null, TipoContrato::class, $fila['tipo_contrato_id'] ?? null);
                    $horarioId    = $this->resolverId($fila['horario']    ?? null, HorarioLaboral::class, $fila['horario_id']    ?? null);

                    if (!$empresaId || !$sucursalId || !$cargoId) {
                        throw new \Exception('Falta empresa, sucursal o cargo (por nombre o ID).');
                    }

                    // Código: si viene vacío, se autogenera
                    $codigo = trim($fila['codigo_empleado'] ?? '');
                    if ($codigo === '') {
                        $codigo = $this->generarSiguienteCodigo();
                    }

                    $payload = [
                        'nombres'                 => $fila['nombres']    ?? '',
                        'apellidos'               => $fila['apellidos']  ?? '',
                        'tipo_documento'          => strtoupper($fila['tipo_documento'] ?? 'CI'),
                        'numero_documento'        => (string)($fila['numero_documento'] ?? ''),
                        'fecha_nacimiento'        => $this->fecha($fila['fecha_nacimiento'] ?? null),
                        'sexo'                    => strtoupper($fila['sexo'] ?? 'M'),
                        'estado_civil'            => strtolower($fila['estado_civil'] ?? 'soltero'),
                        'nacionalidad'            => $fila['nacionalidad'] ?? 'paraguaya',
                        'direccion'               => $fila['direccion'] ?? null,
                        'departamento_residencia' => $fila['departamento_residencia'] ?? null,
                        'ciudad_residencia'       => $fila['ciudad_residencia'] ?? null,
                        'telefono'                => $fila['telefono'] ?? null,
                        'correo'                  => $fila['correo'] ?? null,
                        'empresa_id'              => $empresaId,
                        'sucursal_id'             => $sucursalId,
                        'departamento_id'         => $deptoId,
                        'cargo_id'                => $cargoId,
                        'codigo_empleado'         => $codigo,
                        'tipo_contrato_id'        => $tipoContrato,
                        'horario_id'              => $horarioId,
                        'fecha_ingreso'           => $this->fecha($fila['fecha_ingreso'] ?? null) ?? now()->toDateString(),
                        'fecha_egreso'            => $this->fecha($fila['fecha_egreso'] ?? null),
                        'estado'                  => strtolower($fila['estado'] ?? 'activo'),
                        'salario_base'            => (int) preg_replace('/\D/', '', (string)($fila['salario_base'] ?? 0)),
                        'numero_ips'              => $fila['numero_ips'] ?? null,
                        'profesion'               => $fila['profesion'] ?? null,
                        'creado_por'              => auth()->id(),
                    ];

                    // Validación mínima
                    if ($payload['nombres'] === '' || $payload['apellidos'] === '' || $payload['numero_documento'] === '') {
                        throw new \Exception('Nombres, apellidos y número de documento son obligatorios.');
                    }

                    // Evitar duplicados por documento + empresa
                    $dup = Empleado::where('empresa_id', $empresaId)
                        ->where('numero_documento', $payload['numero_documento'])
                        ->exists();
                    if ($dup) {
                        throw new \Exception('El documento ' . $payload['numero_documento'] . ' ya existe en esa empresa.');
                    }

                    // Asegurar código único compuesto
                    $intento = 0;
                    while (Empleado::where('empresa_id', $empresaId)->where('codigo_empleado', $payload['codigo_empleado'])->exists()) {
                        $payload['codigo_empleado'] = $this->generarSiguienteCodigo();
                        if (++$intento > 5) throw new \Exception('No se pudo generar un código único.');
                    }

                    Empleado::create($payload);
                    $importados++;
                } catch (\Throwable $e) {
                    $fallidos++;
                    $errores[] = "Fila {$filaNum}: " . $e->getMessage();
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Importación empleados: ' . $e->getMessage());
            return response()->json(['ok' => false, 'mensaje' => 'Error general: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'ok'         => true,
            'importados' => $importados,
            'fallidos'   => $fallidos,
            'errores'    => array_slice($errores, 0, 15),
        ]);
    }

    /* =========================================================
     |  HELPERS
     * ========================================================= */

    /** Reglas de validación compartidas entre store y update */
    private function validar(Request $request, ?int $id = null): array
    {
        $reglas = [
            'nombres'                 => 'required|string|max:50',
            'apellidos'               => 'required|string|max:50',
            'tipo_documento'          => 'required|in:CI,RUC,PASAPORTE',
            'numero_documento'        => [
                'required',
                'string',
                'max:20',
                Rule::unique('empleados', 'numero_documento')->ignore($id),
            ],
            'fecha_nacimiento'        => 'nullable|date',
            'sexo'                    => 'nullable|in:M,F,OTRO',
            'estado_civil'            => 'nullable|in:soltero,casado,divorciado,viudo,concubino',
            'nacionalidad'            => 'nullable|string|max:50',
            'direccion'               => 'nullable|string|max:255',
            'departamento_residencia' => 'nullable|string|max:100',
            'ciudad_residencia'       => 'nullable|string|max:100',
            'telefono'                => 'nullable|string|max:20',
            'correo'                  => 'nullable|email|max:100',
            'empresa_id'              => 'required|exists:empresas,id',
            'sucursal_id'             => 'required|exists:sucursales,id',
            'departamento_id'         => 'nullable|exists:departamentos,id',
            'cargo_id'                => 'required|exists:cargos,id',
            'codigo_empleado'         => 'required|string|max:20',
            'tipo_contrato_id'        => 'nullable|exists:tipos_contrato,id',
            'horario_id'              => 'nullable|exists:horarios_laborales,id',
            'fecha_ingreso'           => 'required|date',
            'fecha_egreso'            => 'nullable|date|after_or_equal:fecha_ingreso',
            'estado'                  => 'required|in:activo,vacaciones,licencia,suspendido,inactivo',
            'jefe_inmediato_id'       => 'nullable|exists:empleados,id',
            'salario_base'            => 'required|integer|min:0',
            'numero_ips'              => 'nullable|string|max:20',
            'profesion'               => 'nullable|string|max:100',
            'foto'                    => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];

        return $request->validate($reglas);
    }

    /** Guarda la foto y devuelve el nombre; borra la anterior si existe */
    private function guardarFoto(Request $request, ?string $fotoAnterior): string
    {
        $carpeta = public_path($this->fotoPath);
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0755, true);
        }

        if ($fotoAnterior && file_exists($carpeta . '/' . $fotoAnterior)) {
            @unlink($carpeta . '/' . $fotoAnterior);
        }

        $file     = $request->file('foto');
        $nombre   = time() . '_' . Str::slug($request->nombres . ' ' . $request->apellidos) . '.' . $file->getClientOriginalExtension();
        $file->move($carpeta, $nombre);

        return $nombre;
    }

    /** Resolver ID por nombre o por ID directo */
    private function resolverId(?string $nombre, string $modelo, $idDirecto = null): ?int
    {
        if ($idDirecto && is_numeric($idDirecto)) {
            return (int) $idDirecto;
        }
        if (!$nombre) {
            return null;
        }

        $nombre = trim((string) $nombre);
        if ($nombre === '') {
            return null;
        }

        $registro = $modelo::whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])->first();
        return $registro?->id;
    }

    /** Normaliza fecha a Y-m-d o null */
    private function fecha($valor): ?string
    {
        if (!$valor) return null;
        try {
            return \Carbon\Carbon::parse($valor)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function normalizarMayusculas(array $data): array
    {
        $campos = [
            'nombres',
            'apellidos',
            'nacionalidad',
            'direccion',
            'departamento_residencia',
            'ciudad_residencia',
            'codigo_empleado',
            'profesion',
            'numero_ips',
            'numero_documento',
            //'correo',
            'telefono',
        ];
        foreach ($campos as $c) {
            if (isset($data[$c]) && is_string($data[$c])) {
                $data[$c] = mb_strtoupper(trim($data[$c]), 'UTF-8');
            }
        }
        return $data;
    }
}
