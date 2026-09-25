@extends('layouts.app')
@section('content')
<div class="mb-0 w-screen lg:mx-auto lg:w-[500px] card shadow-lg border-none shadow-slate-100 relative">
    <div class="!px-10 !py-12 card-body">
        <a href="#!">
            <img src="assets/images/logo-tit-2.jpg" alt="" class="hidden h-6 mx-auto dark:block">
            <img src="assets/images/logo-tit-2.jpg" alt="" class="block h-10 mx-auto dark:hidden">
        </a>

        <div class="mt-8 text-center">
            <h4 class="mb-1" style="color: #0bd09d;">¡Bienvenido!</h4>
            <p class="text-slate-500 dark:text-zink-200">Inicie sesión para continuar al sistema.</p>
        </div>

        <form action="{{ route('login') }}" class="mt-10" id="" method="POST">
            @csrf
            <div class="hidden px-4 py-3 mb-3 text-sm text-green-500 border border-green-200 rounded-md bg-green-50 dark:bg-green-400/20 dark:border-green-500/50" id="successAlert">
                You have <b>successfully</b> signed in.
            </div>
            <div class="mb-3">
                <label for="username" class="inline-block mb-2 text-base font-medium">Usuario / Email</label>
                <input type="text" id="email" name="email" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200" placeholder="Ingrese su usuario o correo electrónico">
                <div id="username-error" class="hidden mt-1 text-sm text-red-500">Por favor, ingrese un correo electrónico válido.</div>
            </div>
            <div class="mb-3">
                <label for="password" class="inline-block mb-2 text-base font-medium">Contraseña</label>
                <div class="relative">
                    <input type="password" id="password" name="password" class="form-input border-slate-200 dark:border-zink-500 focus:outline-none focus:border-custom-500 disabled:bg-slate-100 dark:disabled:bg-zink-600 disabled:border-slate-300 dark:disabled:border-zink-500 dark:disabled:text-zink-200 disabled:text-slate-500 dark:text-zink-100 dark:bg-zink-700 dark:focus:border-custom-800 placeholder:text-slate-400 dark:placeholder:text-zink-200" placeholder="Ingrese su contraseña">
                    <button type="button" onclick="togglePassword('password', this)"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600 dark:text-zink-300 dark:hover:text-zink-100">
                        <!-- Ojo abierto -->
                        <svg data-icon="eye" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <!-- Ojo tachado (oculto por defecto) -->
                        <svg data-icon="eye-off" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hidden">
                            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                            <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
                            <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61" />
                            <line x1="2" x2="22" y1="2" y2="22" />
                        </svg>
                    </button>
                    <div id="password-error" class="hidden mt-1 text-sm text-red-500">La contraseña debe tener al menos 8 caracteres y contener letras y números.</div>
                </div>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <input id="checkboxDefault1" name="remember" class="border rounded-sm appearance-none size-4 bg-slate-100 border-slate-200 dark:bg-zink-600 dark:border-zink-500 checked:bg-custom-500 checked:border-custom-500 dark:checked:bg-custom-500 dark:checked:border-custom-500 checked:disabled:bg-custom-400 checked:disabled:border-custom-400" type="checkbox" value="1">
                    <label for="checkboxDefault1" class="inline-block text-base font-medium align-middle cursor-pointer">Recordarme</label>
                </div>
                <div id="remember-error" class="hidden mt-1 text-sm text-red-500">Por favor, marque la casilla "Recordarme" antes de enviar el formulario.</div>
            </div>
            <div class="mt-10">
                <button type="submit"
                    class="w-full text-white transition-all duration-200 ease-linear btn"
                    style="background-color: #000000; border-color: #000000;"
                    onmouseover="this.style.backgroundColor='#05c48b'; this.style.borderColor='#05c48b';"
                    onmouseout="this.style.backgroundColor='#000000'; this.style.borderColor='#000000';">
                    Iniciar Sesión
                </button>
            </div>

            <div class="relative text-center my-9 before:absolute before:top-3 before:left-0 before:right-0 before:border-t before:border-t-slate-200 dark:before:border-t-zink-500" style="display:none">
                <h5 class="inline-block px-2 py-0.5 text-sm bg-white text-slate-500 dark:bg-zink-600 dark:text-zink-200 rounded relative">Sign In with</h5>
            </div>

            <div class="flex flex-wrap justify-center gap-2" style="display:none">
                <button type="button" class="flex items-center justify-center size-[37.5px] transition-all duration-200 ease-linear p-0 text-white btn bg-custom-500 border-custom-500 hover:text-white hover:bg-custom-600 hover:border-custom-600 focus:text-white focus:bg-custom-600 focus:border-custom-600 active:text-white active:bg-custom-600 active:border-custom-600"><i data-lucide="facebook" class="size-4"></i></button>
                <button type="button" class="flex items-center justify-center size-[37.5px] transition-all duration-200 ease-linear p-0 text-white btn bg-orange-500 border-orange-500 hover:text-white hover:bg-orange-600 hover:border-orange-600 focus:text-white focus:bg-orange-600 focus:border-orange-600 active:text-white active:bg-orange-600 active:border-orange-600"><i data-lucide="mail" class="size-4"></i></button>
                <button type="button" class="flex items-center justify-center size-[37.5px] transition-all duration-200 ease-linear p-0 text-white btn bg-sky-500 border-sky-500 hover:text-white hover:bg-sky-600 hover:border-sky-600 focus:text-white focus:bg-sky-600 focus:border-sky-600 active:text-white active:bg-sky-600 active:border-sky-600"><i data-lucide="twitter" class="size-4"></i></button>
                <button type="button" class="flex items-center justify-center size-[37.5px] transition-all duration-200 ease-linear p-0 text-white btn bg-slate-500 border-slate-500 hover:text-white hover:bg-slate-600 hover:border-slate-600 focus:text-white focus:bg-slate-600 focus:border-slate-600 active:text-white active:bg-slate-600 active:border-slate-600"><i data-lucide="github" class="size-4"></i></button>
            </div>

            <div class="mt-10 text-center" style="display:none">
                <p class="mb-0 text-slate-500 dark:text-zink-200">Don't have an account ?
                    <a href="{{ route('register') }}" class="font-semibold underline transition-all duration-150 ease-linear text-slate-500 dark:text-zink-200 hover:text-custom-500 dark:hover:text-custom-500"> SignUp</a>
                </p>
            </div>
        </form>
    </div>
</div>

@section('script')
<script src="{{ URL::to('assets/js/pages/auth-login.init.js') }}"></script>
<script>
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const eye = btn.querySelector('[data-icon="eye"]');
        const eyeOff = btn.querySelector('[data-icon="eye-off"]');
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        eye.classList.toggle('hidden', isHidden);
        eyeOff.classList.toggle('hidden', !isHidden);
    }
</script>
@endsection
@endsection