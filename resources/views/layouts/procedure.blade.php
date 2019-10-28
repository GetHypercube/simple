<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('layouts.ga')
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{  \Cuenta::seo_tags()->title }}</title>
    <meta name="description" content="{{ \Cuenta::seo_tags()->description }}">
    <meta name="keywords" content="{{ \Cuenta::seo_tags()->keywords }}">

    <!-- Styles -->
    <link href="{{ asset('css/'.$estilo.'') }} " rel="stylesheet">

    <meta name="google" content="notranslate"/>

    <link rel="shortcut icon" href="{{ asset(\Cuenta::getAccountFavicon()) }}">
    <link href="{{ asset('css/component-chosen.css') }}" rel="stylesheet">

    @yield('css')

    <script src="https://maps.googleapis.com/maps/api/js?key=<?= env('MAP_KEY') ?>&libraries=places&language=ES"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script type="text/javascript">
        var site_url = "";
        var base_url = "";

        var onloadCallback = function () {
            if ($('#form_captcha').length) {
                grecaptcha.render("form_captcha", {
                    sitekey: "{{env('RECAPTCHA_SITE_KEY')}}"
                });
            }
        };

        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });
    </script>
     <style type="text/css">{{ $personalizacion }}</style>
</head>
<body class="h-100">
<div id="app" class="h-100 d-flex flex-column" >
    @include($dominio_header)

    <!-- <div class="alert alert-warning" role="alert">
        Estamos realizando labores de mantenimiento en el sitio, presentará intermitencia en su funcionamiento.
    </div> -->

    <div class="main-container container pb-5">
        <div class="row">
            <div class="col-xs-12 col-md-3">

                <ul class="simple-list-menu list-group d-none d-sm-block">
                    <a class="list-group-item list-group-item-action  {{isset($sidebar) && $sidebar == 'disponibles' ? 'active' : ''}}"
                       href="{{route('home')}}">
                        <i class="material-icons">insert_drive_file</i> Iniciar trámite
                    </a>

                    @if(Auth::user()->registrado)
                        @php
                            $npendientes = \App\Helpers\Doctrine::getTable('Etapa')
                                ->findPendientes(Auth::user()->id, Cuenta::cuentaSegunDominio())->count();
                                //dd($npendientes);
                            $nsinasignar =count(\App\Helpers\Doctrine::getTable('Etapa')->findSinAsignar(Auth::user()->id, Cuenta::cuentaSegunDominio()));
                          //  dd($nsinasignar);
                           //  echo "<script>console.log(".json_encode($nsinasignar).")</script>";
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
                       <!--  <a class="list-group-item list-group-item-action {{isset($sidebar) && strstr($sidebar, 'miagenda') ? 'active' : ''}}"
                           href="{{route('agenda.miagenda')}}">
                            <i class="material-icons">date_range</i> Mi Agenda
                        </a> -->
                    @endif
                </ul>
            </div>

            <div class="col-xs-12 col-md-9">
                @include('components.messages')
                @yield('content')
                {!! isset($content) ? $content : '' !!}
            </div>

        </div>
    </div>
    @include($dominio_footer, ['metadata' => $metadata_footer])
</div>

@stack('script')

<!-- Scripts -->
<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit&hl=es"></script>
<script src="{{ asset('js/helpers/grilla_datos_externos.js') }}"></script>

<script>
$(function () {
    $(document).ready(function(){
        $('#cierreSesion').click(function (){
            $.ajax({ url: 'https://api.claveunica.gob.cl/api/v1/accounts/app/logout', dataType: 'script' }) .always(function() {
                window.location.href = '/logout';
            });
        });
    });
});
</script>
</body>
</html>
