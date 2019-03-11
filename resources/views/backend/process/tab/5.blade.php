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
                    <option value="despues">Después</option>
                </select>
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
            <th>Accion</th>
            <th>Condición</th>
            <th>Instante</th>
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
            <td><input type="text" class="form-control" name="eventos[<?= $key + 1 ?>][regla]" value="<?= $p->regla ?>"/></td>
            <td><select class="eventoInstante form-control" name="eventos[<?= $key + 1 ?>][instante]">
                    <option value="antes" <?= $p->instante=='antes' ? 'selected' : '' ?> >Antes</option>
                    <option value="despues" <?= $p->instante=='despues' ? 'selected' : '' ?>>Después</option>
                </select>
            </td>
            <td>
                <select class="eventoPasoId form-control" name="eventos[<?= $key + 1 ?>][paso_id]">
                    <?php if($p->paso_id): ?>
                        <?php foreach ($tarea->Pasos as $paso): ?>
                            <?php if($paso->id===$p->paso_id): ?>
                                <option selected value="<?=$paso->id?>" title="<?=$paso->Formulario->nombre?>">Ejecutar Paso <?=$paso->orden?></option>
                            <?php else: ?>
                                <option value="<?=$paso->id?>" title="<?=$paso->Formulario->nombre?>">Ejecutar Paso <?=$paso->orden?></option>
                            <?php endif; ?>
                        <?php endforeach ?>

                        <?php foreach ($tarea->EventosExternos as $ee): ?>
                        <?php if($ee->id===$p->evento_externo_id): ?>
                            <option selected value="<?=$ee->id?>" title="<?=$ee->nombre?>">Evento Externo <?=$ee->nombre?></option>
                        <?php else: ?>
                            <option value="<?=$ee->id?>" title="<?=$ee->nombre?>">Evento Externo <?=$ee->nombre?></option>
                        <?php endif; ?>
                        <?php endforeach ?>
                        <option value="">Ejecutar Tarea</option>
                    <?php endif; ?>

                    <?php if($p->evento_externo_id): ?>
                        <?php foreach ($tarea->Pasos as $paso): ?>
                            <?php if($paso->id===$p->paso_id): ?>
                                <option selected value="<?=$paso->id?>" title="<?=$paso->Formulario->nombre?>">Ejecutar Paso <?=$paso->orden?></option>
                            <?php else: ?>
                                <option value="<?=$paso->id?>" title="<?=$paso->Formulario->nombre?>">Ejecutar Paso <?=$paso->orden?></option>
                            <?php endif; ?>
                        <?php endforeach ?>

                        <?php foreach ($tarea->EventosExternos as $ee): ?>
                        <?php if($ee->id===$p->evento_externo_id): ?>
                            <option selected value="<?=$ee->id?>" title="<?=$ee->nombre?>">Evento Externo <?=$ee->nombre?></option>
                        <?php else: ?>
                            <option value="<?=$ee->id?>" title="<?=$ee->nombre?>">Evento Externo <?=$ee->nombre?></option>
                        <?php endif; ?>
                        <?php endforeach ?>
                        <option value="">Ejecutar Tarea</option>
                    <?php endif; ?>

                    <?php if(is_null($p->paso_id) && is_null($p->evento_externo_id)): ?>
                        <?php foreach ($tarea->Pasos as $paso): ?>
                            <?php if($paso->id===$p->paso_id): ?>
                                <option selected value="<?=$paso->id?>" title="<?=$paso->Formulario->nombre?>">Ejecutar Paso <?=$paso->orden?></option>
                            <?php else: ?>
                                <option value="<?=$paso->id?>" title="<?=$paso->Formulario->nombre?>">Ejecutar Paso <?=$paso->orden?></option>
                            <?php endif; ?>
                        <?php endforeach ?>

                        <?php foreach ($tarea->EventosExternos as $ee): ?>
                        <?php if($ee->id===$p->evento_externo_id): ?>
                            <option selected value="<?=$ee->id?>" title="<?=$ee->nombre?>">Evento Externo <?=$ee->nombre?></option>
                        <?php else: ?>
                            <option value="<?=$ee->id?>" title="<?=$ee->nombre?>">Evento Externo <?=$ee->nombre?></option>
                        <?php endif; ?>
                        <?php endforeach ?>
                        <?php if(is_null($p->paso_id) && is_null($p->evento_externo_id)): ?>
                            <option value="" selected>Ejecutar Tarea</option>
                        <?php else: ?>
                            <option value="">Ejecutar Tarea</option>
                        <?php endif; ?>
                    <?php endif; ?>
                </select>
            </td>

            <td>
                <input type="hidden" name="eventos[<?= $key + 1 ?>][accion_id]"
                       value="<?= $p->accion_id ?>"/>
                
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