<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Session;
use Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /** Show the login page */
    public function login()
    {
        return view('auth.login');
    }

    /** Authenticate the user */
    public function authenticate(Request $request)
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->input('email');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        try {
            if (Auth::attempt([$field => $login, 'password' => $request->password], $request->filled('remember'))) {
                $user = Auth::user();
                $todayDate = Carbon::now()->toDayDateTimeString();

                Session::put([
                    'name'       => $user->name,
                    'empleado_nombre' => $user->empleado ? $user->empleado->nombres . ' ' . $user->empleado->apellidos : $user->name,   // fallback por si el usuario no tiene empleado asociado
                    'email'      => $user->email,
                    'user_id'    => $user->id,
                    //'role_name'  => $user->role?->nombre,
                    'role_name' => null,
                    'last_login' => $todayDate,
                ]);

                $user->update(['last_login' => $todayDate]);

                flash()->success('Login exitoso :)');
                return redirect()->intended('home');
            }

            flash()->error('Error: Usuario o contraseña incorrectos');
            return redirect('login')->withInput($request->only('email'));
        } catch (\Exception $e) {
            \Log::error($e);
            flash()->error('An error occurred during login');
            return redirect()->back();
        }
    }

    /** Show logout page */
    public function logoutPage()
    {
        return view('auth.logout');
    }

    /** Logout and forget session */
    public function logout(Request $request)
    {
        $request->session()->flush();
        Auth::logout();
        flash()->success('Logout successful :)');
        return redirect('logout/page');
    }

    public function role()
    {
      //  return $this->belongsTo(\App\Models\Role::class, 'role_id');
    }

    public function empleado()
{
    return $this->belongsTo(\App\Models\Empleado::class, 'empleado_id');
}
}
