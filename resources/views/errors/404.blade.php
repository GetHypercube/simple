<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>404 Página no encontrada - {{ config('app.name', 'Laravel') }}</title>

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
            <h1 class="red">404</h1>
            <h2>Página no encontrada</h2>
            <p class="mt-3">Lo sentimos, la página que buscas no existe.</p>
            <p class="mt-2">Para resolver este error puedes realizar alguna de las siguientes acciones.</p>
        </div>
        <div class="col-12">
            <ul>
                <li>Comprobar que la dirección (URL) sea la correcta</li>
                <li>Realizar una nueva búsqueda</li>
            </ul>
        </div>
    </div>
    <div class="row">
        <div class="col-3 mt-2">
            <a href="{{route('home')}}" class="btn btn-danger btn-lg btn-block">
                <i class="material-icons">trending_flat</i> Volver al home
            </a>
        </div>
    </div>
</div>
</body>
</html>