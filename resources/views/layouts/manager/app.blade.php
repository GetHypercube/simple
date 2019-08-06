<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
    ga('create', '<?= env('ANALYTICS') ?>', 'auto');
    ga('create', '{{ \Cuenta::seo_tags()->analytics }}', 'auto', 'secondary');
    ga('send', 'pageview');
    ga('secondary.send', 'pageview');
    </script>
    
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{  \Cuenta::seo_tags()->title . ' - Manager' }}</title>
    <meta name="description" content="{{ \Cuenta::seo_tags()->description }}">
    <meta name="keywords" content="{{ \Cuenta::seo_tags()->keywords }}">

    <!-- Styles -->
    <link href="{{ asset('css/manager.css') }}" rel="stylesheet">

    <meta name="google" content="notranslate"/>

    <!-- fav and touch icons -->
    <link rel="shortcut icon" href="{{ asset(\Cuenta::getAccountFavicon()) }}">

    @yield('css')
</head>
<body class="h-100">
<div id="app" class="h-100 d-flex flex-column">

    @include('layouts.manager.header')

    <div class="container-fluid">
        <div class="row">
            <div class="col-3 sidebar-menu">
                @include('layouts.manager.nav')
            </div>
            <div class="col-9 pt-3 mb-5">
                @include('components.messages')

                @yield('content')
                {!! isset($content) ? $content : '' !!}
            </div>
        </div>
    </div>

    @include('layouts.footer')

</div>

<!-- Scripts -->
<script src="{{ asset('js/manager.js') }}"></script>
@stack('scripts')
</body>
</html>
