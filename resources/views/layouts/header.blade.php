<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="" href="{{ url('/') }}">
            <div class="media">
                <img class="align-self-end mr-3 logo"
                     src="{{Cuenta::cuentaSegunDominio() != 'localhost' ? Cuenta::cuentaSegunDominio()->logoADesplegar : asset('assets/img/logo.png') }}"
                     alt="{{Cuenta::cuentaSegunDominio() != 'localhost' ? Cuenta::cuentaSegunDominio()->nombre_largo : env('APP_NAME') }}"/>
                <div class="media-body align-self-center name-institution">
                    <h5 class="mt-0">{{Cuenta::cuentaSegunDominio() != 'localhost' ? Cuenta::cuentaSegunDominio()->nombre_largo : ''}}</h5>
                    <p>{{Cuenta::cuentaSegunDominio() != 'localhost' ? Cuenta::cuentaSegunDominio()->mensaje : ''}}</p>
                </div>
            </div>
        </a>

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