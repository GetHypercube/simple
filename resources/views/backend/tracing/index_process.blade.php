@extends('layouts.backend')

@section('title', 'Listado de Procesos')

@section('content')
    <div class="container-fluid">
        <div class="row mt-3">
            <div class="col-md-12">


                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.tracing.index')}}">Seguimiento de
                                Procesos</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{$proceso->nombre}}</li>
                    </ol>
                </nav>

            </div>
        </div>


        <div class="row-fluid">
            <div class='float-right'>
                <form class="form-search" method="GET" action="">
                    <div class="input-append form-inline">
                        <input name="query" value="<?= $query ?>" type="text" class="form-control search-query"/>
                        <button type="submit" class="btn btn-light">Buscar</button>
                    </div>
                </form>
                <div style='text-align: right;'><a href='#' onclick='toggleBusquedaAvanzada()'>Búsqueda avanzada</a>
                </div>
            </div>

            @if(in_array('super', explode(',', Auth::user()->rol)))
                <div class="btn-group float-left">
                    <a class="btn btn-light dropdown-toggle" data-toggle="dropdown" href="#">
                        Operaciones
                        <span class="caret"></span>
                    </a>

                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                        <a href="<?= url('backend/seguimiento/reset_proc_cont/' . $proceso->id) ?>"
                           class="dropdown-item"
                           onclick="return confirm('¿Esta seguro que desea reiniciar el contador de Proceso?');">
                            Reiniciar contador de Proceso
                        </a>


                        @if ($proceso->Cuenta->ambiente != 'prod')
                            <a href="<?= url('backend/seguimiento/borrar_proceso/' . $proceso->id) ?>"
                               class="dropdown-item"
                               onclick="return borrarProceso(<?=$proceso->id?>);">Borrar todo</a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <br>

        <div id='busquedaAvanzada' class='row mt-5' style='display: <?=$busqueda_avanzada ? 'block' : 'none'?>;'>
            <div class='col-12'>
                <div class='jumbotron'>
                    <form class='form-horizontal'>
                        <input type='hidden' name='busqueda_avanzada' value='1'/>
                        <div class='row'>
                            <div class='col-4'>
                                <div class='control-group'>
                                    <label class='col-form-label'>Término a buscar</label>
                                        <input name="query" value="<?= $query ?>" type="text" class="form-control search-query"/>
                                </div>
                            </div>
                            <div class='col-4'>
                                <div class='control-group'>
                                    <label class='col-form-label'>Estado del trámite</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type='radio' name='pendiente' id="cualquiera"
                                               value='-1' <?= $pendiente == -1 ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="cualquiera">
                                            Cualquiera
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type='radio' name='pendiente' id="encurso"
                                               value='1' <?= $pendiente == 1 ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="encurso">
                                            En Curso
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type='radio' name='pendiente' id="completado"
                                               value='0' <?= $pendiente == 0 ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="completado">
                                            Completado
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class='col-4'>
                                <div class='form-group row'>
                                    <label class='col-sm-5 col-form-label'>Fecha de creación</label>
                                    <div class='col-sm-6'>
                                        <input type='text' name='created_at_desde' placeholder='Desde'
                                               class='datepicker form-control' value='<?= $created_at_desde ?>'/>
                                        <input type='text' name='created_at_hasta' placeholder='Hasta'
                                               class='datepicker form-control' value='<?= $created_at_hasta ?>'/>
                                    </div>
                                </div>
                                <div class='form-group row'>
                                    <label class='col-sm-5 col-form-label'>Fecha de último cambio</label>
                                    <div class='col-sm-6'>
                                        <input type='text' name='updated_at_desde' placeholder='Desde'
                                               class='datepicker form-control' value='<?= $updated_at_desde ?>'/>
                                        <input type='text' name='updated_at_hasta' placeholder='Hasta'
                                               class='datepicker form-control' value='<?= $updated_at_hasta ?>'/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr/>
                        <div style='text-align: right;'>
                            <button type="submit" class="btn btn-primary">Buscar</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        {{$tramites->links('vendor.pagination.bootstrap-4')}}

        <table class="table mt-3">
            <thead>
            <tr>
                <th>
                    <a href="<?= url()->current() . '?query=' . $query . '&pendiente=' . $pendiente . '&created_at_desde=' . $created_at_desde . '&created_at_hasta=' . $created_at_hasta . '&updated_at_desde=' . $updated_at_desde . '&updated_at_hasta=' . $updated_at_hasta . '&order=id&direction=' . ($direction == 'asc' ? 'desc' : 'asc') ?>">Id <?= $order == 'id' ? $direction == 'asc' ? '<i class="icon-chevron-down"></i>' : '<i class="icon-chevron-up"></i>' : '' ?></a>
                </th>
                <th>Asignado a.</th>
                <th>Ref.</th>
                <th>Nombre</th>
                <th>
                    <a href="<?= url()->current() . '?query=' . $query . '&pendiente=' . $pendiente . '&created_at_desde=' . $created_at_desde . '&created_at_hasta=' . $created_at_hasta . '&updated_at_desde=' . $updated_at_desde . '&updated_at_hasta=' . $updated_at_hasta . '&order=pendiente&direction=' . ($direction == 'asc' ? 'desc' : 'asc') ?>">Estado <?= $order == 'pendiente' ? $direction == 'asc' ? '<i class="icon-chevron-down"></i>' : '<i class="icon-chevron-up"></i>' : '' ?></a>
                </th>
                <th>Etapa actual</th>
                <th>
                    <a href="<?= url()->current() . '?query=' . $query . '&pendiente=' . $pendiente . '&created_at_desde=' . $created_at_desde . '&created_at_hasta=' . $created_at_hasta . '&updated_at_desde=' . $updated_at_desde . '&updated_at_hasta=' . $updated_at_hasta . '&order=created_at&direction=' . ($direction == 'asc' ? 'desc' : 'asc') ?>">Fecha
                        de
                        creación <?= $order == 'created_at' ? $direction == 'asc' ? '<i class="icon-chevron-down"></i>' : '<i class="icon-chevron-up"></i>' : '' ?>
                </th>
                <th>
                    <a href="<?= url()->current() . '?query=' . $query . '&pendiente=' . $pendiente . '&created_at_desde=' . $created_at_desde . '&created_at_hasta=' . $created_at_hasta . '&updated_at_desde=' . $updated_at_desde . '&updated_at_hasta=' . $updated_at_hasta . '&order=updated_at&direction=' . ($direction == 'asc' ? 'desc' : 'asc') ?>">Fecha
                        de Último
                        cambio <?= $order == 'updated_at' ? $direction == 'asc' ? '<i class="icon-chevron-down"></i>' : '<i class="icon-chevron-up"></i>' : '' ?></a>
                </th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($tramites as $t): ?>
            <tr>
                <td><?= $t->id ?></td>
                <?php
                $etapa_id = $t->getUltimaEtapa()->id;
                $etapa = \App\Helpers\Doctrine::getTable('Etapa')->find($etapa_id);
                ?>
                <td><?= !$etapa->usuario_id ? 'Ninguno' : !$etapa->Usuario->registrado ? 'No registrado' : $etapa->Usuario->displayUsername() ?></td>
                <td class="name">
                    <?php
                    $tramite_nro = '';
                    foreach ($t->getValorDatoSeguimiento() as $tra_nro) {
                        if ($tra_nro->nombre == 'tramite_ref') {
                            $tramite_nro = $tra_nro->valor;
                        }
                    }
                    echo $tramite_nro != '' ? $tramite_nro : 'N/A';
                    ?>
                </td>
                <td class="name">
                    <?php
                    $tramite_descripcion = '';
                    foreach ($t->getValorDatoSeguimiento() as $tra) {
                        if ($tra->nombre == 'tramite_descripcion') {
                            $tramite_descripcion = $tra->valor;
                        }
                    }
                    echo $tramite_descripcion != '' ? $tramite_descripcion : 'N/A';
                    ?>
                </td>

                <td><?= $t->pendiente ? 'En curso' : 'Completado' ?></td>
                <td>
                    <?php
                    $etapas_array = array();
                    foreach ($t->getEtapasActuales() as $e)
                        $etapas_array[] = $e->Tarea->nombre . ($e->vencimiento_at ? ' <a href="#" onclick="return editarVencimiento(' . $e->id . ')" title="Cambiar fecha de vencimiento">(' . $e->getFechaVencimientoSinDiasAsString() . ')</a>' : '');
                    echo implode(', ', $etapas_array);
                    ?>
                </td>
                <td><?= \Carbon\Carbon::parse($t->created_at)->format('d-m-Y H:i:s') ?></td>
                <td><?= \Carbon\Carbon::parse($t->updated_at)->format('d-m-Y H:i:s') ?></td>
                <td style="text-align: right;">
                    <a class="btn btn-primary" href="<?= url('backend/seguimiento/ver/' . $t->id) ?>">
                        <i class="material-icons">remove_red_eye</i> Seguimiento</a>
                    @if(in_array('super', explode(',', Auth::user()->rol)))
                        <a class="btn btn-danger" href="#" onclick="return eliminarTramite(<?=$t->id?>);">
                            <i class="material-icons">delete</i> Borrar</a>
                    @endif
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        {{$tramites->links('vendor.pagination.bootstrap-4')}}

    </div>

    <div id="modal" class="modal hide"></div>

@endsection
@section('script')
    <script type="text/javascript">
        function editarVencimiento(etapaId) {
            $("#modal").load("/backend/seguimiento/ajax_editar_vencimiento/" + etapaId);
            $("#modal").modal();
            return false;
        }

        function eliminarTramite(tramiteId) {
            $("#modal").load("/backend/seguimiento/ajax_auditar_eliminar_tramite/" + tramiteId);
            $("#modal").modal();
            return false;

        }

        function borrarProceso(procesoId) {
            $("#modal").load("/backend/seguimiento/ajax_auditar_limpiar_proceso/" + procesoId);
            $("#modal").modal();
            return false;
        }

        function toggleBusquedaAvanzada() {
            $("#busquedaAvanzada").slideToggle();
            return false;
        }

    </script>
@endsection