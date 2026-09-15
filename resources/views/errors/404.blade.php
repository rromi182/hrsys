@extends('layouts.error')
@section('content')
    <div class="mb-0 border-none shadow-none lg:w-[500px] card bg-white/70 dark:bg-zink-500/70">
        <div class="!px-10 !py-12 card-body">
            <a href="{{ route('home') }}">
                <img src="{{ URL::to('assets/images/logo-tit-2.jpg') }}" alt="" class="hidden h-6 mx-auto dark:block">
                <img src="{{ URL::to('assets/images/logo-tit-2.jpg') }}" alt="" class="block h-6 mx-auto dark:hidden">
            </a>
            <div class="mt-10">
                <img src="{{ URL::to('assets/images/error-404.png') }}" alt="" class="h-64 mx-auto">
            </div>
            <div class="mt-8 text-center">
                <h4 class="mb-2 text-custom-500">OPPS, PÁGINA NO ENCONTRADA</h4>
                <p class="mb-6 text-slate-500 dark:text-zink-200">Lo sentimos, pero la página que estás buscando no existe.</p>
                <a href="{{ route('home') }}" class="text-white transition-all duration-200 ease-linear btn bg-custom-500 border-custom-500 hover:text-white hover:bg-custom-600 hover:border-custom-600 focus:text-white focus:bg-custom-600 focus:border-custom-600 focus:ring focus:ring-custom-100 active:text-white active:bg-custom-600 active:border-custom-600 active:ring active:ring-custom-100 dark:ring-custom-400/20">
                    <i data-lucide="home" class="inline-block size-3 ltr:mr-1 rtl:ml-1"></i> 
                    <span class="align-middle">Volver a la Página Principal</span>
                </a>
            </div>
        </div>
    </div>
@endsection
