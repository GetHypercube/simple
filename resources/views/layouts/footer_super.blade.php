<nav class="footer mt-5">
    <div class="container">
        <div class="bicolor">
            <span class="azul"></span>
            <span class="rojo"></span>
        </div>
        <div class="row sm-6 margen-columna">
            <div class="col-2 sm-6">
                <h5 class="titulo-footer">Ministerio de<br>bienes Nacionales</h5>
            </div>
            <div class="col-10 sm-6">
                <img class="align-self-center mr-3 logo-footer" src="{{Cuenta::cuentaSegunDominio() != 'localhost' ? Cuenta::cuentaSegunDominio()->logoADesplegar : asset('assets/img/logo.png') }}" alt="Ministerio de Bienes Nacionales"/>
            </div>
        </div>

        <!--<div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
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
        </div>-->
        <div class="row">
            <div class="col-3 margen-footer">
                <a href="http://digital.gob.cl/" target="_blank">
                    MINISTERIO DE BIENES NACIONALES
                </a>
                <br>
                <a href="http://www.minsegpres.gob.cl/" target="_blank">
                    Otros trámites de [Nombre Institución]
                </a>
                <br>
                <a href="">Política de Privacidad</a>
            </div>
            <div class="col-9 mt-9 text-right"></div>
        </div>
        <div class="row">
            <div class="col-6 margen-bottom"><a href="">Términos de uso</a></div>
            <div class="col-6 margen-bottom">Super es una marca registrada por: Ministerio de Economía, Fomento y Turismo (MINECON)</div>
        </div>
    </div>
</footer>