<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>403 No autorizado - {{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <meta name="google" content="notranslate"/>

    <!-- fav and touch icons -->
    <link rel="shortcut icon" href="{{asset('/img/favicon.png')}}">

</head>
<body class="page-404">
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="red">403</h1>
            <h2>No autorizado</h2>
            <p class="mt-3">Lo sentimos, no tienes permisos para acceder a esta página.</p>
        </div>
        <div class="col-12">
            <ul>
                <li>Comprobar que la dirección (URL) sea la correcta</li>
            </ul>
        </div>
    </div>
</div>
</body>
</html>