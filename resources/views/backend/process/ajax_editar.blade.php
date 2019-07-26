<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Editar Proceso</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form id="formEditarProceso" class="ajaxForm" method="POST"
                  action="<?=url('backend/procesos/editar_form/' . $proceso->id)?>">
                {{csrf_field()}}
                <div class="validacion" style="padding: 10px;"></div>

                <div style="width: 45%;display: inline-block;">
                    <label>Nombre</label>
                    <input type="text" class="form-control" name="nombre" value="<?=$proceso->nombre?>"/><br>
                     <!--jp-->
                   
                    <!--finjp-->
                    <label>Tamaño de la Grilla</label>
                    <div class="form-group form-inline">
                        <input type="text" name="width" value="<?=$proceso->width?>" class="form-control col-4"/>
                        <input type="text" name="height" value="<?=$proceso->height?>" class="form-control col-4"/>
                    </div>
                </div>
             
                    
                
                <div style="width: 45%;float: right">
                    <label>Categoría</label>
                    <select name="categoria" id="categoria" class="form-control">
                        <option value="0">Todos los trámites</option>
                        <?php foreach($categorias as $c):?>
                        <?php if ($proceso->categoria_id == $c->id) { ?>
                        <option value="<?=$c->id?>" selected="true"><?=$c->nombre?></option>
                        <?php } else { ?>
                        <option value="<?=$c->id?>"><?=$c->nombre?></option>
                        <?php } ?>
                        <?php endforeach ?>
                    </select>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="destacado"
                               name="destacado" {{$proceso->destacado == 1 ? 'checked' : ''}}>
                        <label class="form-check-label" for="destacado">Destacado </label>
                    </div>
                </div>
                <div>
                    <label>Icono</label>
                    <input id="filenamelogo" type="hidden" name="logo" value="<?= $proceso->icon_ref ?>"/>
                    <a href="javascript:;" id="SelectIcon" class="btn btn-light">Seleccionar ícono</a>
                    @if($proceso->icon_ref)
                        <img id="icn-logo" class="logo icn-logo" src="{{asset('img/icon/' . $proceso->icon_ref)}}"
                             alt="logo"/>
                    @else
                        <img id="icn-logo" class="logo icn-logo" src="{{asset('img/icon/nologo.png')}}" alt="logo"/>
                    @endif
                </div>
                <div>
                    <label>Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="5" cols="10">{{$proceso->descripcion}}</textarea>
                </div>
                <div>
                    <label>Url informativa</label>
                    <input type="text" class="form-control" name="url_informativa" value="<?=$proceso->url_informativa?>"/>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="concurrente"
                            name="concurrente" {{$proceso->concurrente == 1 ? 'checked' : ''}}>
                    <label class="form-check-label" for="concurrente">Permitir la ejecución de varios trámites a la vez por usuario.</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="eliminar_tramites"
                            name="eliminar_tramites" {{$proceso->eliminar_tramites == 1 ? 'checked' : ''}}>
                    <label class="form-check-label" for="eliminar_tramites">Permitir la eliminación de trámites.</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="ocultar_front"
                            name="ocultar_front" {{$proceso->ocultar_front == 1 ? 'checked' : ''}}>
                    <label class="form-check-label" for="ocultar_front">Ocultar tarjeta de inicio de solicitud en frontend.</label>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <a href="#" data-dismiss="modal" class="btn btn-light">Cerrar</a>
            <a href="#" onclick="javascript:$('#formEditarProceso').submit();return false;" class="btn btn-primary">Guardar</a>
        </div>
    </div>
</div>
