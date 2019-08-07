@extends('layouts.procedure')

@section('content')
    <form method="POST" class="ajaxForm dynaForm"
          action="{{route('stage.ejecutar_fin_form', [$etapa->id])}}/{{$qs ? '?' . $qs : ''}}">
        {{csrf_field()}}
        <fieldset>
            <div class="validacion"></div>
            @if(!is_null($etapa->Tarea->paso_confirmacion_titulo))
                <?php
                    $r = new \Regla($etapa->Tarea->paso_confirmacion_titulo);
                    $paso_confirmacion_titulo = $r->getExpresionParaOutput($etapa->id);
                ?>
                <legend>{{$paso_confirmacion_titulo}}</legend>
            @else
                <legend>Paso final</legend>
            @endif
            <?php if ($tareas_proximas->estado == 'pendiente'): ?>
            <?php foreach ($tareas_proximas->tareas as $t): ?>
                @if(!is_null($etapa->Tarea->paso_confirmacion_contenido))
                    <?php
                        $r = new \Regla($etapa->Tarea->paso_confirmacion_contenido);
                        $paso_confirmacion_contenido = $r->getExpresionParaOutput($etapa->id);
                    ?>
                    <p>{{$paso_confirmacion_contenido}}</p>
                @else
                    <p><?= "Para confirmar y enviar el formulario a la siguiente etapa ($t->nombre) haga click en Finalizar." ?> </p>
                @endif
            <?php if ($t->asignacion == 'manual'): ?>
            <label>Asignar próxima etapa a</label>
            <select name="usuarios_a_asignar[<?= $t->id ?>]">
                <?php foreach ($t->getUsuarios($etapa->id) as $u): ?>
                <option value="<?= $u->id ?>"><?= $u->usuario ?> <?=$u->nombres ? '(' . $u->nombres . ' ' . $u->apellido_paterno . ')' : ''?></option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
            <?php endforeach; ?>
            <?php elseif($tareas_proximas->estado == 'standby'): ?>
                @if(!is_null($etapa->Tarea->paso_confirmacion_contenido))
                    <?php
                        $r = new \Regla($etapa->Tarea->paso_confirmacion_contenido);
                        $paso_confirmacion_contenido = $r->getExpresionParaOutput($etapa->id);
                    ?>
                    <p>{{$paso_confirmacion_contenido}}</p>
                @else
                    <p>Luego de hacer click en Finalizar esta etapa quedara detenida momentaneamente hasta que se completen el resto de etapas pendientes.</p>
                @endif                
            <?php elseif($tareas_proximas->estado == 'completado'):?>
                @if(!is_null($etapa->Tarea->paso_confirmacion_contenido))
                    <?php
                        $r = new \Regla($etapa->Tarea->paso_confirmacion_contenido);
                        $paso_confirmacion_contenido = $r->getExpresionParaOutput($etapa->id);
                    ?>
                    <p>{{$paso_confirmacion_contenido}}</p>
                @else
                    <p>Luego de hacer click en Finalizar este trámite quedará completado.</p>
                @endif
            <?php elseif($tareas_proximas->estado == 'sincontinuacion'):?>
                @if(!is_null($etapa->Tarea->paso_confirmacion_contenido))
                    <?php
                        $r = new \Regla($etapa->Tarea->paso_confirmacion_contenido);
                        $paso_confirmacion_contenido = $r->getExpresionParaOutput($etapa->id);
                    ?>
                    <p>{{$paso_confirmacion_contenido}}</p>
                @else
                    <p>Este trámite no tiene una etapa donde continuar.</p>
                @endif
            <?php endif; ?>
            <div class="form-actions">
                <a class="btn btn-light"
                   href="<?= url('etapas/ejecutar/' . $etapa->id . '/' . (count($etapa->getPasosEjecutables()) - 1) . ($qs ? '?' . $qs : '')) ?>">
                    Volver
                </a>
                @if($tareas_proximas->estado != 'sincontinuacion')
                    <button class="btn btn-success" type="submit" id="boton-termino">
                        @if(!is_null($etapa->Tarea->paso_confirmacion_texto_boton_final))
                            <?php
                                $r = new \Regla($etapa->Tarea->paso_confirmacion_texto_boton_final);
                                $paso_confirmacion_texto_boton_final = $r->getExpresionParaOutput($etapa->id);
                            ?>
                            {{$paso_confirmacion_texto_boton_final}}
                        @else
                            Finalizar
                        @endif
                    </button>
                @endif
            </div>
        </fieldset>
        <div class="ajaxLoader" style="position: fixed; left: 50%; top: 30%; display: none;">
            <img src="{{asset('img/loading.gif')}}">
        </div>
    </form>
@endsection
@push('script')
 <script>

    ga(function(tracker) {
    console.log(tracker.get('trackingId')); //ID Seguimiento
    console.log(tracker.get('clientId'));
    });
    </script>
@endpush

