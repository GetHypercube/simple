@extends('layouts.backend')

@section('title', 'Mi Sitio')

@section('content')
    <div class="container-fluid">
        <div class="row mt-3">

            @include('backend.configuration.nav')

            <div class="col-md-9">

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{route('backend.configuration.my_site')}}">Configuración</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Mi Sitio</li>
                    </ol>
                </nav>

                <form action="{{route('backend.configuration.my_site.save')}}" method="post">

                    {{csrf_field()}}

                    <h5>Editar información de mi sitio</h5>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="name">Nombre</label>
                                <input name="name" id="name" type="text" class="form-control" value="{{$data->nombre}}"
                                       disabled>
                            </div>
                            <div class="form-group">
                                <label for="name_large">Nombre largo</label>
                                <input name="name_large" id="name_large" type="text" class="form-control"
                                       value="{{$data->nombre_largo}}">
                            </div>
                            <div class="form-group">
                                <label for="message">Mensaje de bienvenida (Puede contener HTML)</label>
                                <textarea name="message" id="message" rows="2"
                                          class="form-control">{{$data->mensaje}}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="logo">Logo</label>
                                <div id="file-uploader"></div>
                                <input type="hidden" name="logo" value="{{$data->logo}}"/>

                                @if(!empty($data->logo) && file_exists(public_path("logos/{$data->logo}")))
                                    <img class="logo" src="{{asset("logos/{$data->logo}")}}" alt="logo"/>
                                @else
                                    <img class="logo" src="" alt=""/>
                                @endif
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="massive_download"
                                       value="{{$data->descarga_masiva}}" id="massive_download"
                                        {{$data->descarga_masiva ? 'checked' : 0}}>
                                <label class="form-check-label" for="massive_download">
                                    Habilitar descarga masiva
                                </label>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <a href="" class="btn btn-light">Cancelar</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{asset('js/helpers/fileuploader.js')}}"></script>
    <script>
        var uploader = new qq.FileUploader({
            element: document.getElementById('file-uploader'),
            params: {_token: '{{csrf_token()}}'},
            action: '/backend/uploader/logo',
            onComplete: function (id, filename, respuesta) {
                $("input[name=logo]").val(respuesta.file_name);
                $("img.logo").attr("src", "/logos/" + respuesta.file_name);
            }
        });
    </script>
@endsection
