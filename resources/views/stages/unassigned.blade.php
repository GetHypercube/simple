@extends('layouts.procedure')
@section('content')
    <div class="row">
        <div class="col-xs-12 col-md-8">
            <h2>Etapas sin asignar</h2> 
        </div>
        <div class="col-xs-12 col-md-4">
            <!--buscador-->
            <form class="form-search form-inline float-right" method="GET" action="">
                <div class="input-group mb-3">
                    <input name="query" class="form-control" placeholder="Escribe aquí lo que deseas buscar"
                        type="text"
                        value="{{$query}}">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="material-icons">search</i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-xs-12 col-md-12">
        @if (count($etapas) > 0)
            <table id="mainTable" class="table table-hover table-condesed">
                <thead>
                <tr>
                    <th></th>
                    <th><a href="{{ getUrlSortUnassigned($request, 'numero') }}">Número</a></th>
                    <th><a href="{{ getUrlSortUnassigned($request, 'nombre') }}">Nombre</a></th>
                    <th><a href="{{ getUrlSortUnassigned($request, 'etapa') }}">Etapa</a></th>
                    <th>Fecha úlitma tarea realizada</th>
                    <th><a href="{{ getUrlSortUnassigned($request, 'modificacion') }}">Modificación</a></th>
                    <th><a href="{{ getUrlSortUnassigned($request, 'vencimiento') }}">Vencimiento</a></th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($etapas as $e)      
                    <tr {!! getPrevisualization($e) ? 'data-toggle="popover" data-html="true" data-title="<h4>Previsualización</h4>" data-content="' . htmlspecialchars($previsualizacion) . '" data-trigger="hover" data-placement="bottom"' : '' !!}>
                        <td class="text-nowrap">
                            @if($cuenta->descarga_masiva && $e->tramite->files->count() > 0)
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" class="checkbox1" name="select[]" value="{{$e->id}}">
                                </label>
                            </div>
                            @endif
                        </td> 
                        <td class="text-nowrap">{{ $e->tramite->id }}</td>
                        <td>{{ $e->tramite->proceso->nombre }}</td>               
                        <td class="text-nowrap">{{$e->tarea->nombre }}</td>
                        <td class="time">
                            {{ getLastTask($e) }}
                        </td>
                        <td>{{ getDateFormat($e->updated_at)}}</td>
                        <td>{{ $e->vencimiento_at ? getDateFormat($e->vencimiento_at, 'vencimiento') : 'N/A'}}</td>
                        <td class="actions">
                            <a href="{{url('etapas/asignar/' . $e->id)}}" class="btn btn-link">
                                <i class="icon-check icon-white"></i> Asignármelo
                            </a>
                            @if($cuenta->descarga_masiva && $e->tramite->files->count() > 0)
                            <a href="#" onclick="return descargarDocumentos({{$e->id}});" class="btn btn-link">
                                <i class="icon-download icon-white"></i> Descargar
                            </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>    
            @if($cuenta->descarga_masiva && hasFiles($etapas))
                <div class="pull-right">
                    <div class="checkbox">
                        <input type="hidden" id="tramites" name="tramites"/>
                        <label>
                            <input type="checkbox" id="select_all" name="select_all"/> Seleccionar todos
                            <a href="#" onclick="return descargarSeleccionados();" class="btn btn-success preventDoubleRequest">
                                <i class="icon-download icon-white"></i> Descargar seleccionados
                            </a>
                        </label>
                    </div>
                </div>
            @endif
            <p>
            {{ $etapas->appends(Request::except('page'))->render("pagination::bootstrap-4")}}
            </p>
        @else
            <p>No hay trámites para ser asignados.</p>
        @endif
    </div>
    <div class="modal hide" id="modal"></div>
@endsection
@push('script')
    <script>
        function descargarDocumentos(tramiteId) {
            $("#modal").load("/etapas/descargar/" + tramiteId);
            $("#modal").modal();
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
        });

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
                return false;
            }
        }
    </script>
@endpush