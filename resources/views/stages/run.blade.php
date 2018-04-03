@extends('layouts.procedure')

@section('css')
    <link rel="stylesheet" href="<?= asset('js/helpers/calendar/css/calendar.css') ?>">
@endsection

@section('content')
    <ul class="steps">
        @for ($i = 0; $i < $num_pasos; $i++)
            <li class="{{($i <= $secuencia) ? 'active' : '' }}" style="width: {{round(100 / $num_pasos)}}%;">
                <span>Paso {{$i + 1}}</span>
            </li>
        @endfor
    </ul>
    <div style="clear: both;"></div>
    @if ($etapa->Tarea->vencimiento)
        <div class="alert alert-warning">Atención. Esta etapa {{$etapa->getFechaVencimientoAsString()}}.</div>
    @endif
    <form method="POST" class="ajaxForm dynaForm form-horizontal"
          action="/etapas/ejecutar_form/{{$etapa->id}}/{{$secuencia . ($qs ? '?' . $qs : '')}}">
        {{csrf_field()}}
        <input type="hidden" name="_method" value="post">
        <div class="validacion"></div>

        <h1 class="title">{{$paso->Formulario->nombre}}</h1>
        <hr>

        @foreach($paso->Formulario->Campos as $c)
            <div class="campo control-group" data-id="<?=$c->id?>"
                 <?= $c->dependiente_campo ? 'data-dependiente-campo="' . $c->dependiente_campo . '" data-dependiente-valor="' . $c->dependiente_valor . '" data-dependiente-tipo="' . $c->dependiente_tipo . '" data-dependiente-relacion="' . $c->dependiente_relacion . '"' : '' ?> style="display: <?= $c->isCurrentlyVisible($etapa->id) ? 'block' : 'none'?>;"
                 data-readonly="{{$paso->modo == 'visualizacion' || $c->readonly}}">
                <?=$c->displayConDatoSeguimiento($etapa->id, $paso->modo)?>
            </div>
        @endforeach

        <div class="form-actions">
            @if ($secuencia > 0)
                <a class="btn btn-light"
                   href="{{url('etapas/ejecutar/' . $etapa->id . '/' . ($secuencia - 1) . ($qs ? '?' . $qs : ''))}}">
                    Volver
                </a>
            @endif
            <button class="btn btn-simple btn-danger" type="submit">Siguiente</button>
        </div>
    </form>
    <div id="modalcalendar" class="modal hide fade modalconfg modcalejec"></div>
    <input type="hidden" id="urlbase" value="<?= URL::to('/') ?>"/>
@endsection

@push('script')
    <script src="{{asset('js/helpers/fileuploader.js')}}"></script>

    <script type="text/javascript"
            src="<?= asset('js/helpers/calendar/components/underscore/underscore-min.js') ?>"></script>
    <script type="text/javascript"
            src="<?= asset('js/helpers/calendar/components/jstimezonedetect/jstz.min.js') ?>"></script>
    <script type="text/javascript" src="<?= asset('js/helpers/calendar/js/language/es-CO.js') ?>"></script>
    <script type="text/javascript" src="<?= asset('js/helpers/calendar/js/calendar.js?v=0.3') ?>"></script>
    <script src="{{asset('js/helpers/collapse.js')}}"></script>
    <script src="{{asset('js/helpers/transition.js')}}"></script>
    <script>
        $(function () {
            $.each($('.js-data-cita'), function () {
                if (jQuery.trim($(this).val()) != "") {
                    var id = $(this).attr('id');
                    var arrdat = $(this).val().split('_');
                    $('#codcita' + id).val(arrdat[0]);
                    var feho = arrdat[1].split(' ');
                    var fe = feho[0].split('-');
                    var d = new Date(fe[0] + '/' + fe[1] + '/' + fe[2] + ' ' + feho[1]);
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

    <script src="{{asset('js/helpers/common.js')}}"></script>
@endpush