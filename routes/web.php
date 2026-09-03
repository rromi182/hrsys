<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HRController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\NominaController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('home', function () {
        return view('dashboard.home');
    });
    Route::get('home', function () {
        return view('dashboard.home');
    });
});

Auth::routes();

Route::group(['namespace' => 'App\Http\Controllers\Auth'], function () {
    // -----------------------------login----------------------------------------//
    Route::controller(LoginController::class)->group(function () {
        Route::get('/login', 'login')->name('login');
        Route::post('/login', 'authenticate');
        Route::get('/logout', 'logout')->name('logout');
        Route::get('logout/page', 'logoutPage')->name('logout/page');
    });

    // ------------------------------ register ----------------------------------//
    Route::controller(RegisterController::class)->group(function () {
        Route::get('/register', 'register')->name('register');
        Route::post('/register', 'storeUser')->name('register');
    });

    // ----------------------------- forget password ----------------------------//
    Route::controller(ForgotPasswordController::class)->group(function () {
        Route::get('forget-password', 'getEmail')->name('forget-password');
        Route::post('forget-password', 'postEmail')->name('forget-password');
    });

    // ----------------------------- reset password -----------------------------//
    Route::controller(ResetPasswordController::class)->group(function () {
        Route::get('reset-password/{token}', 'getPassword');
        Route::post('reset-password', 'updatePassword');
    });
});

Route::group(['namespace' => 'App\Http\Controllers'], function () {
    // -------------------------- main dashboard ----------------------//
    Route::controller(HomeController::class)->group(function () {
        Route::get('/home', 'index')->middleware('auth')->name('home');
    });

    // -------------------------- pages ----------------------//
    Route::controller(AccountController::class)->group(function () {
        Route::get('page/account/{user_id}', 'profileDetail')->middleware('auth');
    });

    // -------------------------- hr ----------------------//
    Route::middleware('auth')->prefix('hr/')->group(function () {
        Route::controller(HRController::class)->group(function () {
            Route::get('employee/list', 'employeeList')->name('hr/employee/list');
            Route::post('employee/save', 'employeeSaveRecord')->name('hr/employee/save');
            Route::post('employee/update', 'employeeUpdateRecord')->name('hr/employee/update');
            Route::post('employee/delete', 'employeeDeleteRecord')->name('hr/employee/delete');

            Route::get('holidays/page', 'holidayPage')->name('hr/holidays/page');
            Route::post('holidays/save', 'holidaySaveRecord')->name('hr/holidays/save');
            Route::post('holidays/delete', 'holidayDeleteRecord')->name('hr/holidays/delete');

            Route::get('leave/employee/page', 'leaveEmployee')->name('hr/leave/employee/page');
            Route::get('create/leave/employee/page', 'createLeaveEmployee')->name('hr/create/leave/employee/page');
            Route::post('create/leave/employee/save', 'saveRecordLeave')->name('hr/create/leave/employee/save');
            Route::get('view/detail/leave/employee/{staff_id}', 'viewDetailLeave');

            Route::get('leave/hr/page', 'leaveHR')->name('hr/leave/hr/page');
            Route::get('attendance/page', 'attendance')->name('hr/attendance/page');
            Route::get('create/leave/hr/page', 'createLeaveHR')->name('hr/create/leave/hr/page');

            Route::post('get/information/leave', 'getInformationLeave')->name('hr/get/information/leave');

            Route::get('attendance/main/page', 'attendanceMain')->name('hr/attendance/main/page');
            Route::get('department/page', 'department')->name('hr/department/page');
            Route::post('department/save', 'saveRecorddepartment')->name('hr/department/save');
            Route::post('department/delete', 'deleteRecorddepartment')->name('hr/department/delete');
        });
    });

    /* --- Nuevas rutas para laravel (empleados reales) --- */
    Route::middleware(['auth'])->prefix('hr')->group(function () {
        Route::get('/empleados/listado', [EmpleadoController::class, 'index'])->name('empleados.index');
        Route::post('/empleados/guardar', [EmpleadoController::class, 'store'])->name('empleados.store');
        Route::get('/empleados/{empleado}/ver', [EmpleadoController::class, 'show'])->name('empleados.show');
        Route::put('/empleados/{empleado}/actualizar', [EmpleadoController::class, 'update'])->name('empleados.update');
        Route::delete('/empleados/{empleado}/eliminar', [EmpleadoController::class, 'destroy'])->name('empleados.destroy');
    });

    // -------------------------- nomina salarial ----------------------//
    Route::middleware(['auth'])->group(function () {
        Route::get('/nomina', [NominaController::class, 'movimientos'])->name('nomina.movimientos');
        Route::get('/nomina/data', [NominaController::class, 'movimientosData'])->name('nomina.movimientos.data');
        Route::post('/nomina/store', [NominaController::class, 'store'])->name('nomina.movimientos.store');
        Route::post('/nomina/anular/{id}', [NominaController::class, 'anularMovimiento'])->name('nomina.movimientos.anular');
        Route::get('/nomina/empleado/{id}/salario', [NominaController::class, 'getEmpleadoSalario'])->name('nomina.empleado.salario');
        Route::get('/nomina/resumen', [NominaController::class, 'resumen'])->name('nomina.resumen');
        Route::get('/nomina/resumen/excel', [NominaController::class, 'exportarExcel'])->name('nomina.resumen.excel');
        Route::get('/nomina/resumen/csv', [NominaController::class, 'exportarCsv'])->name('nomina.resumen.csv');
    });
});
