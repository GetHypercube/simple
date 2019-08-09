@extends('layouts.procedure')

@section('content')
    <div class="row">
        <div class="col-xs-12 col-md-8">
            <h2>Bandeja de Entrada</h2>
        </div>
        <div class="col-xs-12 col-md-4">
            <!--buscador-->
            <div class="float-right">
                <a href='#' onclick='toggleBusquedaAvanzada()'>Opciones de Búsqueda</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <form method="GET" action="">
                <div id="filters" class="jumbotron" style="padding: 2rem 2rem;display:{{ isset($params['option']) ? 'block' : 'none' }}">
                    <input type='hidden' name='busqueda_avanzada' value='1'/>
                    <div class="row">
                        <div class="col-12">
                            <label class='col-form-label'>Seleccione tipo de búsqueda:</label><br>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input search-selector" type="radio"
                                       name="params[option]" id="inlineRadio5" value="option5">
                                <label class="form-check-label" for="inlineRadio5">Sin cartgoría</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input search-selector" type="radio"
                                       name="params[option]" id="inlineRadio1" value="option1">
                                <label class="form-check-label" for="inlineRadio1">Buscar por Nro</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input search-selector" type="radio"
                                       name="params[option]" id="inlineRadio3" value="option3">
                                <label class="form-check-label" for="inlineRadio3">
                                    Buscar por Referencia
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input search-selector" type="radio"
                                       name="params[option]" id="inlineRadio4" value="option4">
                                <label class="form-check-label" for="inlineRadio4">Buscar por Nombre</label>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="search-inputs">
                                <div class='control-group seg-input-search' id="input1">
                                    <label class='col-form-label'>Ingrese Nro:</label>
                                    <input name="params[tramite_id]" value="{{ isset($params['tramite_id']) ? $params['tramite_id']:"" }}"
                                           type="text" class="form-control"/>
                                </div>
                                <div class='control-group seg-input-search' id="input3">
                                    <label class='col-form-label'>Ingrese Valor de referencia:</label>
                                    <input name="params[ref]" value="{{ isset($params['ref']) ? $params['ref']:"" }}"
                                           type="text" class="form-control"/>
                                </div>
                                <div class='control-group seg-input-search' id="input4">
                                    <label class='col-form-label'>Ingrese nombre:</label>
                                    <input name="params[name]" value="{{ isset($params['name']) ? $params['name']:"" }}"
                                           type="text" class="form-control"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <br><br>
                            <div class="row">
                                <div class="col-6">
                                    <div class="row">
                                        <div class="col-12">
                                            <label class='col-form-label'>Última modificación (opcional):</label>
                                        </div>
                                        <div class="col-6">
                                            <input type='text' name='params[updated_date_from]' placeholder='Desde'
                                                   class='datetimepicker form-control'
                                                   value='{{ isset($params['updated_date_from']) ? $params['updated_date_from'] : "" }}'/>
                                        </div>
                                        <div class="col-6">
                                            <input type='text' name='params[updated_date_to]' placeholder='Hasta'
                                                   class='datetimepicker form-control'
                                                   value='{{ isset($params['updated_date_to']) ? $params['updated_date_to']:"" }}'/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="row">
                                        <div class="col-12">
                                            <label class='col-form-label'>Fecha de vencimiento (opcional):</label>
                                        </div>
                                        <div class="col-6">
                                            <input type='text' name='params[deleted_date_from]' placeholder='Desde'
                                                   class='datetimepicker form-control'
                                                   value='{{ isset($params['deleted_date_from']) ? $params['deleted_date_from']:"" }}'/>
                                        </div>
                                        <div class="col-6">
                                            <input type='text' name='params[deleted_date_to]' placeholder='Hasta'
                                                   class='datetimepicker form-control'
                                                   value='{{ isset($params['deleted_date_to']) ? $params['deleted_date_to']:"" }}'/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <br>
                            ** Las fechas son filtros adicionales a las opciones de búsqueda en la parte superior.
                        </div>
                    </div>
                    <hr/>
                    <div style='text-align: right;'>
                        <button type="submit" class="btn btn-primary">Buscar</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-xs-12 col-md-12">
            <?php if (count($etapas) > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th></th>
                        <th>
                            <a href="<?=Request::url() . '?orderby=id&direction=' . ($direction == 'asc' ? 'desc' : 'asc')?>">Nro</a>
                        </th>
                        <th>Ref.</th>
                        <th>
                            <a href="<?=Request::url() . '?orderby=proceso_nombre&direction=' . ($direction == 'asc' ? 'desc' : 'asc')?>">Nombre</a>
                        </th>
                        <th>
                            <a href="<?=Request::url() . '?orderby=tarea_nombre&direction=' . ($direction == 'asc' ? 'desc' : 'asc')?>">Etapa</a>
                        </th>
                        <th>
                            <a href="<?=Request::url() . '?orderby=updated_at&direction=' . ($direction == 'asc' ? 'desc' : 'asc')?>">Modificación</a>
                        </th>
                        <th>
                            <a href="<?=Request::url() . '?orderby=vencimiento_at&direction=' . ($direction == 'asc' ? 'desc' : 'asc')?>">Vencimiento</a>
                        </th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $registros = false; ?>
                    <?php foreach ($etapas as $e): ?>
                    <?php
                    $file = false;
                    if (\App\Helpers\Doctrine::getTable('File')->findByTramiteId($e->Tramite->id)->count() > 0) {
                        $file = true;
                        $registros = true;
                    }
                    ?>
                    <tr <?=$e->getPrevisualizacion() ? 'data-toggle="popover" data-html="true" data-title="<h4>Previsualización</h4>" data-content="' . htmlspecialchars($e->getPrevisualizacion()) . '" data-trigger="hover" data-placement="bottom"' : ''?>>
                        <?php if (Cuenta::cuentaSegunDominio()->descarga_masiva): ?>
                        <?php if ($file): ?>
                        <td>
                            <div class="checkbox"><label><input type="checkbox" class="checkbox1" name="select[]"
                                                                value="<?=$e->Tramite->id?>"></label></div>
                        </td>
                        <?php else: ?>
                        <td></td>
                        <?php endif; ?>
                        <?php else: ?>
                        <td></td>
                        <?php endif; ?>
                        <td><?=$e->Tramite->id?></td>
                        <td class="name">
                            <?php
                            $t = \App\Helpers\Doctrine::getTable('Tramite')->find($e->Tramite->id);
                            $tramite_nro = '';
                            foreach ($t->getValorDatoSeguimiento() as $tra_nro) {
                                if ($tra_nro->nombre == 'tramite_ref') {
                                    $tramite_nro = $tra_nro->valor;
                                }
                            }
                            echo $tramite_nro != '' ? $tramite_nro : $e->Tramite->Proceso->nombre;
                            ?>
                        </td><!--Nro. tramites-->
                    <!--<td class="name"><a class="preventDoubleRequest" href="<?//=url('etapas/ejecutar/'.$e->id)?>"><?//= $e->Tramite->Proceso->nombre ?></a></td> Nombre-->
                        <td class="name"><a class="preventDoubleRequest" href="<?=url('etapas/ejecutar/' . $e->id)?>">
                                <?php
                                $tramite_descripcion = '';
                                foreach ($t->getValorDatoSeguimiento() as $tra) {
                                    if ($tra->nombre == 'tramite_descripcion') {
                                        $tramite_descripcion = $tra->valor;
                                    }
                                }
                                echo $tramite_descripcion != '' ? $tramite_descripcion : $e->Tramite->Proceso->nombre;
                                ?>
                            </a></td><!--Tramites-->
                        <td><?=$e->Tarea->nombre?></td>
                        <td class="time">{{\Carbon\Carbon::parse($e->updated_at)->format('d-m-Y')}}</td>
                        <td><?=$e->vencimiento_at ? \Carbon\Carbon::parse($e->vencimiento_at)->format('d-m-Y') : 'N/A'?></td>
                        <td class="actions">
                                <a href="<?=url('etapas/ejecutar/' . $e->id)?>"
                                class="btn btn-sm btn-primary preventDoubleRequest"><i class="icon-edit icon-white"></i>
                                    Realizar</a>
                        <?php if (Cuenta::cuentaSegunDominio()->descarga_masiva): ?>
                            <?php if ($file): ?>
                            <a href="#" onclick="return descargarDocumentos(<?=$e->Tramite->id?>);"
                               class="btn btn btn-sm btn-success"><i
                                        class="icon-download icon-white"></i> Descargar</a>
                        <?php endif; ?>
                        <?php endif; ?>
                            @if(Auth::check() && Auth::user()->open_id && !is_null($e->Tarea->Proceso->eliminar_tramites) && $e->Tarea->Proceso->eliminar_tramites) 
                                <a href="#" onclick="return eliminarTramite(<?=$e->Tramite->id?>);"
                                class="btn btn-sm btn-danger preventDoubleRequest"><i class="icon-edit icon-red"></i>
                                    Borrar</a>
                            @endif
                        <!--<?php if($e->netapas == 1):?><a href="<?=url('tramites/eliminar/' . $e->tramite_id)?>" class="btn" onclick="return confirm('¿Esta seguro que desea eliminar este tramite?')"><i class="icon-trash"></i></a><?php endif ?>-->
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (Cuenta::cuentaSegunDominio()->descarga_masiva): ?>
            <?php if ($registros): ?>
            <div class="pull-right">
                <div class="checkbox">
                    <input type="hidden" id="tramites" name="tramites"/>
                    <label>
                        <input type="checkbox" id="select_all" name="select_all"/> Seleccionar todos
                        <a href="#" onclick="return descargarSeleccionados();" class="button preventDoubleRequest">Descargar
                            seleccionados</a>
                    </label>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
             <p><?= $etapas->links('vendor.pagination.bootstrap-4') ?></p>
            <?php else: ?>
            <p>No hay trámites pendientes en su bandeja de entrada.</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="modal hide in" id="modal"></div>

@endsection

@push('script')
    <script>
        let SEARCH_OPT = {!! isset($params['option']) ? json_encode($params['option']) : "'option5'" !!};
        function checkSearchInputs(val) {
            $('.seg-input-search').hide();
            switch (val) {
                case 'option1':
                    $('#input1').show();
                    break;
                case 'option3':
                    $('#input3').show();
                    break;
                case 'option4':
                    $('#input4').show();
                    break;
            }
        }
        function descargarDocumentos(tramiteId) {
            $("#modal").load("/etapas/descargar/" + tramiteId);
            $("#modal").modal();
            $("#modal").css('display', 'block');

            $(".closeModal").click(function () {
                closeModal();
                console.log("test1");
            });

            $(".modal-backdrop").click(function () {
                closeModal();
                console.log("test2");
            });

            $(".modal-backdrop").click(function () {
                closeModal();
                console.log("test3");
            });

            return false;
        }

        $(document).ready(function () {
            $('#select_all').click(function (event) {
                var checked = [];
                $('#tramites').val();
                if (this.checked) {
                    $('.checkbox1').each(function () {
                        this.checked = true;
                    });
                } else {
                    $('.checkbox1').each(function () {
                        this.checked = false;
                    });
                }
                $('#tramites').val(checked);
            });

            checkSearchInputs(SEARCH_OPT);

            switch (SEARCH_OPT) {
                case 'option1':
                    $('#inlineRadio1').prop('checked', true);
                    break;
                case 'option3':
                    $('#inlineRadio3').prop('checked', true);
                    break;
                case 'option4':
                    $('#inlineRadio4').prop('checked', true);
                    break;
                case undefined:
                    $('#inlineRadio5').prop('checked', true);
                    break;
                default:
                    $('#inlineRadio5').prop('checked', true);
            }

            $('.datetimepicker').datetimepicker({
                format: 'DD-MM-YYYY',
                icons: {
                    previous: "glyphicon glyphicon-chevron-left",
                    next: "glyphicon glyphicon-chevron-right"
                },
                locale: 'es'
            });

            $('.search-selector').on('click', function() {
                checkSearchInputs($(this).val())
            });
        });

        function toggleBusquedaAvanzada() {
            $("#filters").slideToggle();
        }

        function closeModal() {
            $("#modal").removeClass("in");
            $(".modal-backdrop").remove();
            $('body').removeClass('modal-open');
            $('body').css('padding-right', '');
            $("#modal").hide();
        }

        function descargarSeleccionados() {
            var numberOfChecked = $('.checkbox1:checked').length;
            if (numberOfChecked == 0) {
                alert('Debe seleccionar al menos un trámite');
                return false;
            } else {
                var checked = [];
                $('.checkbox1').each(function () {
                    if ($(this).is(':checked')) {
                        checked.push(parseInt($(this).val()));
                    }
                });
                $('#tramites').val(checked);
                var tramites = $('#tramites').val();
                $("#modal").load("/etapas/descargar/" + tramites);
                $("#modal").modal();
                console.log("descargarSeleccionados.modal");
                return false;
            }
        }

        function eliminarTramite(tramiteId) {
            $("#modal").load("/tramites/eliminar/" + tramiteId);
            $("#modal").modal();
            $("#modal").css('display', 'block');

            $(".closeModal").click(function () {
                closeModal();
                console.log("test1");
            });

            $(".modal-backdrop").click(function () {
                closeModal();
                console.log("test2");
            });

            $(".modal-backdrop").click(function () {
                closeModal();
                console.log("test3");
            });

            return false;
        }
    </script>
@endpush