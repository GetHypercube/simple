@extends('layouts.procedure')

@section('content')
    <form class="form-horizontal dynaForm" onsubmit="return false;">
        <fieldset>
            <div class="validacion"></div>
            <legend><?= $paso->Formulario->nombre ?></legend>
            @foreach ($paso->Formulario->Campos as $c)
                <div class="control-group campo" data-id="<?= $c->id ?>"
                     <?= $c->dependiente_campo ? 'data-dependiente-campo="' . $c->dependiente_campo . '" data-dependiente-valor="' . $c->dependiente_valor . '" data-dependiente-tipo="' . $c->dependiente_tipo . '" data-dependiente-relacion="' . $c->dependiente_relacion . '"' : '' ?> style="display: <?= $c->isCurrentlyVisible($etapa->id) ? 'block' : 'none'?>;"
                     data-readonly="<?=$paso->modo == 'visualizacion' || $c->readonly?>">
                    <?= $c->displayConDatoSeguimiento($etapa->id, 'visualizacion') ?>
                </div>
            @endforeach
            <div class="form-actions">
                @if ($secuencia > 0)
                    <a class="btn btn-light" href="<?= url('etapas/ver/' . $etapa->id . '/' . ($secuencia - 1)) ?>">
                        <i class="material-icons">chevron_left</i> Volver
                    </a>
                @endif
                @if ($secuencia + 1 < count($etapa->getPasosEjecutables()))
                    <a class="btn btn-primary" href="<?= url('etapas/ver/' . $etapa->id . '/' . ($secuencia + 1)) ?>">
                        Siguiente
                    </a>
                @endif
            </div>
        </fieldset>
    </form>
@endsection
@section('script')
    <script src="<?= asset('/calendar/js/moment-2.2.1.js') ?>"></script>
    <script>
        $(function () {
            moment.lang('es');
            $.each($('.js-data-cita'), function () {
                if ($(this).is('[readonly]')) {
                    var id = $(this).attr('id');
                    var arrdat = $(this).val().split('_');
                    var d = new Date(arrdat[1]);
                    var h = '';
                    if (d.getHours() <= 9) {
                        h = '0' + d.getHours();
                    } else {
                        h = d.getHours();
                    }
                    var m = '';
                    if (d.getMinutes() <= 9) {
                        m = '0' + d.getMinutes();
                    } else {
                        m = d.getMinutes();
                    }
                    var fecha = d.getDate() + '/' + (d.getMonth() + 1) + '/' + d.getFullYear() + ' ' + h + ':' + m;

                    var lab = moment(d.getFullYear() + '/' + (d.getMonth() + 1) + '/' + d.getDate()).format("LL");
                    $('#txtresult' + id).html(lab + ' a las ' + h + ':' + m + " horas");
                }
            });
        });
    </script>
@endsection