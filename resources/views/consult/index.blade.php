@section('title', $title)
@section('content')
    <div class="container" id="main">
        <div class="row">
            <div class="col-md-9 offset-md-3">
                <h1>{{$titulo}}</h1>
                <h2><i class="material-icons">home</i> <?= Cuenta::cuentaSegunDominio()->nombre_largo ?></h2>

                <p>
                    <i class="material-icons">help</i> A través de esta pequeña y simple aplicación puedes dar
                    seguimiento a cualquier trámite que se ha ingresado
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <form method="POST" action="">
                    {{csrf_field()}}
                    @if($errors->any())
                        @foreach ($errors->all() as $error)
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {!! $error !!}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endforeach
                    @endif
                    <div class="campo control-group">
                        <label class="control-label" style="color:#465f6e;" for="nrotramite">
                            <i class="icon icon-chevron-right"></i>Nro. de Trámite
                        </label>
                        <div class="form-group">
                            <input name="nrotramite" id="nrotramite" type="text" value="{{$nrotramite}}"
                                   class="form-control col-3"
                                   data-step="1"
                                   data-intro="Ingresá el Nro. de la Mesa de Entrada  <img src='{{asset('/js/helpdoc/ayu1.png')}}/>"
                                   data-position='center'>
                        </div>


                        <div class="form-group">

                            <label class="control-label" style="color:#465f6e;" for="datetimepicker1">
                                <i class="icon icon-chevron-right"></i>Ingrese el año que fue ingresado el
                                trámite
                            </label>
                            <div class="input-group date" data-date=""
                                 data-date-format="yyyy"
                                 data-link-field="fecha1" data-link-format="yyyy">
                                <input type="text" placeholder="aaaa" value="<?=$fecha?>" name="fecha"
                                       class="form-control col-3"
                                       id="datetimepicker1"
                                       onkeypress="validarDatos(event,'#fecha','#buscar');"
                                       data-step="2"
                                       data-intro="Ingresá la Fecha de Entrada <img src='{{asset('js/helpdoc/ayu2.png')}}/>"
                                       data-position='center'/>
                                <div class="input-group-append">
                                <span class="input-group-text" id="inputGroupPrepend3">
                                    <i class="material-icons">access_time</i>
                                </span>
                                </div>
                            </div>


                        </div>
                        <input size="16" type="hidden" value="<?=$fecha?>" name="fecha1" id="fecha1">
                        <div>
                            <button class="btn btn-primary" type="submit" id="buscar" name="buscar"
                                    data-step="3"
                                    data-intro="Presioná el botón, para dar seguimiento a su documento"
                                    data-position='right'>Buscar
                            </button>
                        </div>
                    </div>
                </form>

                <?php $indice = 1; if (count($tareas) > 0 && $tareas > 0):  ?>
                <div id="diagramContainer">
                    <div id="dibujo"></div>
                </div>
                <div class="responsive">
                    <div class="panel panel-default">
                        <div class="panel-body" style="margin-left: 100px;">
                            <div class="row-fluid">
                                <div class="col-2">
                                    <div class="info" style="background: green; float: left;"></div>
                                    <div style="margin-left: 30px;"><b>Completados</b></div>
                                </div>
                                <div class="col-2">
                                    <div class="info" style="background: goldenrod; float: left;"></div>
                                    <div style="margin-left: 30px;"><b>Pendientes</b></div>
                                </div>
                            </div>
                            <div class="row-fluid">
                                <b>Observación:</b> Para ver más detalles; realizar click en cada gráfico.
                            </div>
                        </div>
                    </div>
                    <br/>
                    <table class="mt-3 table table-striped table-condensed table-bordered table-hover">
                        <thead>
                        <th>Nro.</th>
                        <th>Pasos del Trámite</th>
                        <th>Finalizado en Fecha</th>
                        <th>Responsable</th>
                        <th>Estado</th>
                        </thead>
                        <tbody>
                        <?php foreach ($tareas as $d): ?>
                        <tr>
                            <td style="text-align:center;"><?= $indice++ ?></td>
                            <td><?= $d['tarea_nombre'] ?></td>
                            <td><?= $d['termino'] ?></td>
                            <td><?= $d['usuario'] ?></td>
                            <td style="text-align:center;"><?= $d['estado'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <?= $vacio ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{asset('js/helpers/grafico-consulta.js')}}"></script>
    <script type="text/javascript">
        $(function () {
            $('#datetimepicker1').datetimepicker({
                viewMode: 'years',
                format: 'YYYY'
            });
        });

        $(document).ready(function () {
            tareas =<?= json_encode($tareas); ?>;
            graficar(tareas);
        });

        function doSearchEnter(e) {
            var key = e.keyCode || e.which;
            if (key === 13) {
                return true;
            } else {
                return false;
            }
        }

        function vacio(q) {
            for (i = 0; i < q.length; i++) {
                if (q.charAt(i) !== " ") {
                    return true;
                }
            }
            return false;
        }

        function validarDatos(event, dat_origen, dat_destino) {
            if (doSearchEnter(event) === true) {
                if (!vacio($(dat_origen).val())) {
                    $(dat_origen).focus();
                } else {
                    $(dat_destino).focus();
                }

            }
        }


        $(document).ready(function () {

            $('#nrotramite').on("keypress", function (e) {
                if (e.keyCode == 13) {
                    if (!vacio($('#nrotramite').val())) {
                        $('#nrotramite').focus();
                    } else {
                        var inputs = $(this).parents("form").eq(0).find(":input");
                        var idx = inputs.index(this);
                        if (idx == inputs.length - 1) {
                            inputs[0].select();
                        } else {
                            inputs[idx + 1].focus();
                            inputs[idx + 1].select();
                        }
                    }
                    return false;
                }
            });
        });
    </script>
@endsection