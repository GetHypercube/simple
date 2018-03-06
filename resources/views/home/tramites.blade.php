<?php if ($num_destacados > 0 || $sidebar == 'categorias'): ?>
<section id="simple-destacados">
    <div class="section-header">
        <?php if ($sidebar == 'disponibles'): ?>
        <h2>Trámites destacados</h2>
        <?php else: ?>
        <h2>Trámites - <?= $categoria->nombre ?></h2>
        <a href="<?=site_url('home/index/')?>" class="btn btn-primary preventDoubleRequest"
           style="float: right;">
            <i class="icon-file icon-white"></i> Volver
        </a>
        <?php endif ?>
    </div>
    <div class="row">
        <?php foreach ($procesos as $p): ?>
        <?php if ($p->destacado == 1 || $sidebar == 'categorias'): ?>
        <div class="{{$login ? 'col-md-6' : 'col-md-4' }} item">
            <div class="card text-center">
                @if ($p->estado == 'draft')
                    <div class="card-header draft">
                        Borrador
                    </div>
                @endif
                <div class="card-body {{($p->estado == 'draft') ? 'draft' : ''}}">
                    <div class="media">
                        @if($p->icon_ref)
                            <img src="<?= asset('img/icons/' . $p->icon_ref) ?>" class="img-service">
                        @else
                            <i class="icon-archivo"></i>
                        @endif
                        <div class="media-body">
                            <p class="card-text">
                                {{$p->nombre}}
                            </p>
                        </div>
                    </div>
                </div>

                <a href="{{
                             $p->canUsuarioIniciarlo(Auth::user()->id) ? route('tramites.iniciar',  [$p->id]) :
                            (
                                $p->getTareaInicial()->acceso_modo == 'claveunica' ? route('autenticacion.login_openid').'?redirect='.route('tramites.iniciar', [$p->id]) :
                                route('login').'?redirect='.route('tramites.iniciar/', $p->id)
                            )
                            }}"
                   class="card-footer {{$p->getTareaInicial()->acceso_modo == 'claveunica'? 'claveunica' : ''}}">
                    <div class="card-footer text-muted">
                        @if ($p->canUsuarioIniciarlo(Auth::user()->id))
                            Iniciar
                        @else
                            @if ($p->getTareaInicial()->acceso_modo == 'claveunica')
                                <i class="icon-claveunica"></i> Iniciar con Clave Única
                            @else
                                Autenticarse
                            @endif
                        @endif
                        <span>&#8594;</span>
                    </div>
                </a>
            </div>


        </div>
        <?php $count++ ?>
        <?php endif ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif ?>

<?php if (count($categorias) > 0): ?>
<section id="simple-categorias">
    <div class="section-header">
        <h2>Categorías</h2>
    </div>
    <div class="row">
        @foreach ($categorias as $c)
            <div class="col-md-3 item">
                <a href="<?=site_url('home/procesos/' . $c->id)?>">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="media">
                                @if($c->icon_ref)
                                    <img src="<?= asset('img/icons/' . $c->icon_ref) ?>" class="img-service">
                                @else
                                    <i class="icon-archivo"></i>
                                @endif
                                <div class="media-body">
                                    <p class="card-text">
                                        {{$c->nombre}}
                                        {{$c->descripcion}}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</section>
<?php endif ?>

@if ($num_otros > 0 && $sidebar != 'categorias')
    <section id="simple-destacados">
        <div class="section-header">
            @if (count($categorias) > 0 || $num_destacados > 0)
                <h2>Otros trámites</h2>
            @endif
        </div>
        <div class="row">
            @foreach ($procesos as $p)
                @if($p->destacado == 0 || $p->categoria_id == 0)
                    <div class="{{$login ? 'col-md-6' : 'col-md-4' }} item">

                        <div class="card text-center">
                            @if ($p->estado == 'draft')
                                <div class="card-header draft">
                                    Borrador
                                </div>
                            @endif
                            <div class="card-body {{($p->estado == 'draft') ? 'draft' : ''}}">
                                <div class="media">
                                    @if($p->icon_ref)
                                        <img src="<?= asset('img/icons/' . $p->icon_ref) ?>"
                                             class="img-service">
                                    @else
                                        <i class="icon-archivo"></i>
                                    @endif
                                    <div class="media-body">
                                        <p class="card-text">
                                            {{$p->nombre}}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <a href="{{
                                     $p->canUsuarioIniciarlo(Auth::user()->id) ? route('tramites.iniciar',  [$p->id]) :
                                    (
                                        $p->getTareaInicial()->acceso_modo == 'claveunica' ? route('login.claveunica').'?redirect='.route('tramites.iniciar', [$p->id]) :
                                        route('login').'?redirect='.route('tramites.iniciar/', $p->id)
                                    )
                                    }}"
                               class="card-footer {{$p->getTareaInicial()->acceso_modo == 'claveunica'? 'claveunica' : ''}}">
                                @if ($p->canUsuarioIniciarlo(Auth::user()->id))
                                    Iniciar trámite
                                @else
                                    @if ($p->getTareaInicial()->acceso_modo == 'claveunica')
                                        <i class="icon-claveunica"></i> Iniciar con Clave Única
                                    @else
                                        <i class="material-icons">person</i> Autenticarse
                                    @endif
                                @endif
                                <span class="float-right">&#8594;</span>
                            </a>

                        </div>

                    </div>
                @endif
            @endforeach
        </div>
    </section>
@endif