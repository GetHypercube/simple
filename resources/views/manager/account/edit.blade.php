<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?=url('manager/cuentas')?>">Cuentas</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?=$title?></li>
    </ol>
</nav>

<form class="ajaxForm" method="post" action="<?= url('manager/cuentas/editar_form/' . $cuenta->id) ?>">
    {{csrf_field()}}
    <fieldset>
        <legend><?= $title ?></legend>
        <hr>
        <div class="validacion"></div>
        <label>Nombre</label>
        <input type="text" name="nombre" value="<?= $cuenta->nombre ?>" class="form-control col-3"/>
        <div class="form-text text-muted">En minusculas y sin espacios.</div>
        <label>Nombre largo</label>
        <input class="form-control col-6" type="text" name="nombre_largo" value="<?= $cuenta->nombre_largo ?>"/>
        <label>Mensaje de bienvenida (Puede contener HTML)</label>
        <textarea name="mensaje" class="form-control col-6"><?= $cuenta->mensaje ?></textarea>
        <label>Logo</label>
        <div id="file-uploader"></div>
        <input type="hidden" name="logo" value="<?= $cuenta->logo ?>"/>
        <img class="logo"
             src="<?= $cuenta->logo ? asset('logos/' . $cuenta->logo) : asset('img/simple.png') ?>"
             alt="logo"/>
    </fieldset>
    </br>
    <fieldset>
        <legend><?= $title ?> configuración ambiente de desarrollo</legend>
        <hr>
        <label>¿Es ambiente Desarrollo?</label>
        <input name="desarrollo" id="toggle_ambiente_dev" type="checkbox"
               <?= ($cuenta->ambiente == 'dev') ? 'checked' : '' ?> data-toggle="toggle" data-size="normal" data-on="Si"
               data-off="No">
        <div id="vinculo_prod" name="ambiente" class="<?= ($cuenta->ambiente != 'dev') ? 'hide' : '' ?>">
            <label>Vincular Cuenta Productiva</label>
            <select id="ambiente-prod" name="vinculo_produccion" class="form-control col-3">
                <option value="">Seleccionar ...</option>
                <?php foreach($cuentas_productivas as $cp):?>
                <option value="<?=$cp['id']?>" <?= ($cp['id'] == $cuenta->vinculo_produccion) ? 'selected' : '' ?>><?=$cp['nombre']?></option>
                <?php endforeach ?>
            </select>
        </div>
    </fieldset>
    </br>
    <fieldset>
        <legend><?= $title ?> configuraci&oacute;n de agenda</legend>
        <hr>
        <label>Clave App</label>
        <input type="text" name="appkey" class="form-control col-3" readonly="true" disabled="true"
               value="<?= $calendar->getAppkey() ?>"/>
        <label>Dominio</label>
        <input type="text" name="domain" class="form-control col-3" value="<?= $calendar->getDomain() ?>"/>
    </fieldset>
    <script src="{{asset('js/helpers/fileuploader.js')}}"></script>
    <script>
        var uploader = new qq.FileUploader({
            element: document.getElementById('file-uploader'),
            action: '/manager/uploader/logo',
            onComplete: function (id, filename, respuesta) {
                $("input[name=logo]").val(respuesta.file_name);
                $("img.logo").attr("src", "logos/" + respuesta.file_name);
            }
        });
    </script>
    </br>
    <div class="form-actions">
        <button class="btn btn-primary" type="submit">Guardar</button>
        <a class="btn btn-light" href="<?= url('manager/cuentas') ?>">Cancelar</a>
    </div>
</form>