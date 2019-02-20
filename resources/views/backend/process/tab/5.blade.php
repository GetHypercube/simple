<div class="tab-eventos tab-pane fade" id="tab5" role="tabpanel" aria-labelledby="tab5-tab">
    <table class="table">
        <thead>
        <tr class="form-agregar-evento">
            <td></td>
            <td>
                <select class="eventoAccion form-control">
                    <?php foreach ($acciones as $f): ?>
                    <option value="<?= $f->id ?>"><?= $f->nombre ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <input class="eventoRegla form-control reglas" type="text"
                       placeholder="Escribir regla condición"/>
                <p class="message" style="color: red; display: block;"></p>
            </td>
            <td>
                <select class="eventoInstante form-control">
                    <option value="antes">Antes</option>
                    <option value="durante">Durante</option>
                    <option value="despues">Después</option>
                    </select>
            </td>
            <td>
                <input class="eventoCampoAsociado col-md-10" 
                    type="text" placeholder="@@@campo">
                <p class="messageEventoAsociado" style="color: red; display: block;"></p>
            </td>
            <td>
                <select class="eventoPasoId form-control">
                    <option value="">Ejecutar Tarea</option>
                    <?php foreach ($tarea->Pasos as $p): ?>
                    <option value="<?=$p->id?>" title="<?=$p->Formulario->nombre?>">Ejecutar
                        Paso <?=$p->orden?></option>
                    <?php endforeach ?>
                    <?php foreach ($tarea->EventosExternos as $ee): ?>
                    <option value="<?=$ee->id?>" title="<?=$ee->nombre?>">Evento
                        Externo <?=$ee->nombre?></option>
                    <?php endforeach ?>
                </select>
            </td>
            <td>
                <button type="button" class="btn btn-light" title="Agregar">
                    <i class="material-icons">add</i>
                </button>
            </td>
        </tr>
        <tr>
            <th>#</th>
            <th>Acción</th>
            <th>Condición</th>
            <th>Instante</th>
            <th>Botón</th>
            <th>Momento</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($tarea->Eventos as $key => $p): ?>
        <tr>
            <td><?= $key + 1 ?></td>
            <td><a title="Editar" target="_blank"
                   href="<?= url('backend/acciones/editar/' . $p->Accion->id) ?>"><?= $p->Accion->nombre ?></a>
            </td>
            <td><?= $p->regla ?></td>
            <td><?= $p->instante ?></td>
            <td><?= (isset($p->campo_asociado) ? $p->campo_asociado : '' ) ?></td>
            <td><?=$p->paso_id ? '<abbr title="' . $p->Paso->Formulario->nombre . '">Ejecutar Paso ' . $p->Paso->orden . '</abbr>' : ($p->evento_externo_id ? '<abbr title="' . $p->EventoExterno->nombre . '">Evento Externo ' . $p->EventoExterno->nombre . '</abbr>' : 'Ejecutar Tarea')?></td>
            <td>
                <input type="hidden" name="eventos[<?= $key + 1 ?>][accion_id]"
                       value="<?= $p->accion_id ?>"/>
                <input type="hidden" name="eventos[<?= $key + 1 ?>][regla]"
                       value="<?= $p->regla ?>"/>
                <input type="hidden" name="eventos[<?= $key + 1 ?>][instante]"
                       value="<?= $p->instante ?>"/>
                <input type="hidden" name="eventos[<?= $key + 1 ?>][campo_asociado]"
                       value="<?=(isset($p->campo_asociado) ? $p->campo_asociado : '' ) ?>"/>
                <?php
                $paso_ee_id = !is_null($p->paso_id) ? $p->paso_id : $p->evento_externo_id;
                ?>
                <input type="hidden" name="eventos[<?= $key + 1 ?>][paso_id]"
                       value="<?= $paso_ee_id ?>"/>
                <a class="delete" title="Eliminar" href="#"><i class="material-icons">close</i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <label class="checkbox">Para mayor información puedes consultar en el siguiente enlace.
        <a href="/ayuda/simple/backend/modelamiento-del-proceso/disenador.html#pestana_eventos"
           target="_blank">
            <i class="material-icons">help</i>
        </a>
    </label>
</div>

<script>
    $(document).ready(function(){
        $('.eventoInstante').change(function(evt){
            if(evt.target.value === 'durante'){
                // habilitar campo
                $('.eventoCampoAsociado').prop('disabled', false);
            }else{
                // deshabilitar campo
                $('.eventoCampoAsociado').prop('disabled', true);
            }
        });

        $('.eventoPasoId.form-control').change(function(evt){
            var form_id = $('.eventoPasoId.form-control')[0].value;
            $('.eventoCampoAsociado').parent().find(".messageEventoAsociado").html('');
            $('.eventoCampoAsociado').parent().find(".messageEventoAsociado").hide();
            if( form_id == ''){
                $('.eventoCampoAsociado').prop('disabled', true);
                return;
            }
            $('.eventoCampoAsociado').prop('disabled', false);
            $('.eventoCampoAsociado').trigger('blur');
        });

        $('.eventoCampoAsociado').blur(function(evt){
            var form_id = $('.eventoPasoId.form-control')[0].value;
            if(form_id == '') return;  // es ejecutar tarea
            var campo = evt.target.value;
            if(campo == '' || typeof campo === 'undefined') return;
            $.ajax({
                url: '<?=url('backend/form/existe_campo_en_form')?>',
                data: {
                    campo_nombre: campo,
                    form_id: form_id
                },
                method: 'GET',
                dataType: "json",
                cache: false,
                success: function (data) {
                    if( ! data.resultado ) {
                        $('.eventoCampoAsociado').parent().find(".messageEventoAsociado").html(data.mensaje);
                        $('.eventoCampoAsociado').parent().find(".messageEventoAsociado").show();
                    }else{
                        $('.eventoCampoAsociado').parent().find(".messageEventoAsociado").html('');
                        $('.eventoCampoAsociado').parent().find(".messageEventoAsociado").hide();
                    }
                }
            });
            evt.stopPropagation();
        });

        $('.eventoCampoAsociado').focus(function (evt) {
            $(this).parent().find(".messageEventoAsociado").hide();
            evt.stopPropagation();
        });

        $('.eventoInstante').trigger('change');
    })
</script>