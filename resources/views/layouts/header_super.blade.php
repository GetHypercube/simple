<!-- nav superior -->
<nav class="navbar navbar-expand-lg navbar-light bg-dark">
    <div class="container">
        <a class="" href="{{ url('/') }}">
            <div class="media">
                <img class="align-self-center mr-3 logo"
                     src="{{Cuenta::cuentaSegunDominio() != 'localhost' ? Cuenta::cuentaSegunDominio()->logoADesplegar : asset('assets/img/logo.png') }}"
                     alt="Ministerio de Bienes Nacionales"/>
                <!--<div class="media-body align-self-center name-institution">
                    <h5 class="mt-1">{{Cuenta::cuentaSegunDominio() != 'localhost' ? Cuenta::cuentaSegunDominio()->nombre_largo : ''}}</h5>
                    <p>{{Cuenta::cuentaSegunDominio() != 'localhost' ? Cuenta::cuentaSegunDominio()->mensaje : ''}}</p>
                </div>-->
            </div>
        </a>

        <div class="nav">
            <a href="{{route('login.claveunica')}}" class="nav-item margin-right">
                ¿Que és?
            </a>
            <div class="border-top"></div>
            <a href="{{route('login')}}" class="nav-item margin">
                Contáctanos
            </a>
        </div>

    </div>
</nav>
<!-- fin nav superior -->
<!-- barra colores --->
<footer class="footer mt-6 bg-light">
    <div class="container">
        <div class="bicolor">
            <span class="azul"></span>
            <span class="rojo"></span>
        </div>
    </div>
</footer>
<!-- fin barra colores -->
<!-- segundo nav -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <h5 class="h5">Ministerio de<br>Bienes Nacionales</h5>
        <h5 class="h5-2">Nombre del Trámite</h5>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
            <ul class="navbar-nav">
                @if (Auth::guest() || !Auth::user()->registrado)
                    <li class="nav-item login">
                        <a href="{{route('login.claveunica')}}" class="nav-link">
                            <span class="icon-claveunica"></span> {{__('auth.login_claveunica')}}
                        </a>
                    </li>
                    <li class="nav-item login btn-white ml-3">
                        <a href="{{route('login')}}" class="nav-link">
                            <i class="material-icons">person</i> Iniciar Sesión
                        </a>
                    </li>
                @else
                    <li class="nav-item dropdown login">
                        <a href="#" class="nav-link dropdown-toggle" id="navbarDropdownMenuLink"
                           data-toggle="dropdown"
                           aria-haspopup="true" aria-expanded="false">
                            <span class="icon-claveunica"></span> Bienvenido/a, {{ Auth::user()->nombres }}
                        </a>
                        <div class="dropdown-menu dropdown-menu-right login" aria-labelledby="navbarDropdownMenuLink">
                            <a href="{{ route('logout') }}" class="dropdown-item"
                               onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                <i class="material-icons">exit_to_app</i> {{__('auth.close_session')}}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                  style="display: none;">
                                {{ csrf_field() }}
                            </form>
                        </div>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</nav>
<!-- fin segundo nav -->