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
    <link href="{{ asset('css/'.$estilo.'') }} " rel="stylesheet">
    
    <meta name="google" content="notranslate"/>

    <!-- fav and touch icons -->
    <link rel="shortcut icon" href="{{asset('/img/favicon.png')}}">

    @yield('css')

    <style type="text/css">{{ $personalizacion }}</style>

</head>
<body>
<div id="app">
    
    @include($dominio_header)
    
        
    <div class="main-container container">
        @yield('content')
        {!! isset($content) ? $content : '' !!}
    </div>
    @include($dominio_footer)
    <!-- @ include( 'layouts.footer')-->
        
</div>

<!-- Scripts -->
<script src="{{ asset('js/app.js') }}"></script>
@yield('script')
</body>
</html>
