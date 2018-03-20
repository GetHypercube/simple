<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <meta name="google" content="notranslate"/>

    @yield('css')

    <script src="https://maps.googleapis.com/maps/api/js?key=<?= env('MAP_KEY') ?>&libraries=places&language=ES"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script type="text/javascript">
        var site_url = "";
        var base_url = "";

        var onloadCallback = function () {
            if ($('#form_captcha').length) {
                grecaptcha.render("form_captcha", {
                    sitekey: "6Le7zycUAAAAAKrvp-ndTrKRni3yeuCZQyrkJRfH"
                });
            }
        };
    </script>

</head>
<body>
<div id="app">

    @include('layouts.header')

    <div class="main-container container">
        <div class="row">
            <div class="col-xs-12 col-md-3">

                <ul class="simple-list-menu list-group">
                    <a class="list-group-item list-group-item-action  {{isset($sidebar) && $sidebar == 'disponibles' ? 'active' : ''}}"
                       href="{{route('home')}}">
                        <i class="material-icons">insert_drive_file</i> Iniciar trámite
                    </a>

                    @if(Auth::user()->registrado)
                        @php
                            $npendientes = \App\Helpers\Doctrine::getTable('Etapa')
                                ->findPendientes(Auth::user()->id, Cuenta::cuentaSegunDominio())->count();
                            $nsinasignar = \App\Helpers\Doctrine::getTable('Etapa')->findSinAsignar(Auth::user()->id, Cuenta::cuentaSegunDominio())->count();
                            $nparticipados = \App\Helpers\Doctrine::getTable('Tramite')->findParticipadosALL(Auth::user()->id, Cuenta::cuentaSegunDominio())->count();
                        @endphp
                        <a class="list-group-item list-group-item-action {{isset($sidebar) && $sidebar == 'inbox' ? 'active' : ''}}"
                           href="{{route('stage.inbox')}}">
                            <i class="material-icons">inbox</i> Bandeja de Entrada ({{$npendientes}})
                        </a>
                        @if ($nsinasignar)
                            <a class="list-group-item list-group-item-action {{ isset($sidebar) && $sidebar == 'sinasignar' ? 'active' : '' }}"
                               href="{{route('stage.unassigned')}}">
                                <i class="material-icons">assignment</i> Sin asignar ({{$nsinasignar}})
                            </a>
                        @endif
                        <a class="list-group-item list-group-item-action {{isset($sidebar) && $sidebar == 'participados' ? 'active' : ''}}"
                           href="{{route('tramites.participados')}}">
                            <i class="material-icons">history</i> Historial de Trámites ({{$nparticipados}})
                        </a>
                        <a class="list-group-item list-group-item-action {{isset($sidebar) && $sidebar == 'miagenda' ? 'active' : ''}}"
                           href="{{route('agenda.miagenda')}}">
                            <i class="material-icons">date_range</i> Mi Agenda
                        </a>
                    @endif
                </ul>
            </div>

            @include('components.messages')

            <div class="col-xs-12 col-md-9">
                @yield('content')
            </div>

        </div>
    </div>

    @include('layouts.footer')
</div>

@stack('script')

<!-- Scripts -->
<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit&hl=es"></script>
</body>
</html>
