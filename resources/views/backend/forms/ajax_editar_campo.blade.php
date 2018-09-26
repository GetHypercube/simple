@php
    $idagendaeditar = htmlspecialchars($campo->agenda_campo);
    if (!isset($idagendaeditar) || !is_numeric($idagendaeditar)) {
        $idagendaeditar = 0;
    }
@endphp
<style>
    .fa {
        float: left;
        position: relative;
        line-height: 20px;
    }
</style>
<script type="text/javascript">
    $(document).ready(function () {
        $('.validacion').typeahead({
            mode: "multiple",
            delimiter: "|",
            source: ["required", "rut", "min:num", "max:num", "digits:num", "alpha", "alpha_dash", "alpha_num", "numeric", "integer", "is_natural_no_zero", "email", "emails", "ip", "unique[exp]", "digits_between:min,max", "between:min,max", "nullable"]
        });

        // Funcionalidad del llenado de nombre usando el boton de asistencia
        $("#formEditarCampo .asistencia .dropdown-menu a").click(function () {
            var nombre = $(this).text();
            $("#formEditarCampo input[name=nombre]").val(nombre);
        });

        // Funcionalidad del llenado de dependiente usando el boton de asistencia
        $("#formEditarCampo .dependiente .dropdown-menu a").click(function () {
            var nombre = $(this).text();
            $("#formEditarCampo input[name=dependiente_campo]").val(nombre);
        });

        // Funcionalidad en campo dependientes para seleccionar entre tipo regex y string
        $buttonRegex = $("#formEditarCampo .campoDependientes .buttonRegex");
        $buttonString = $("#formEditarCampo .campoDependientes .buttonString");
        $inputDependienteTipo = $("#formEditarCampo input[name=dependiente_tipo]");
        $buttonString.attr("disabled", $inputDependienteTipo.val() == "string");
        $buttonRegex.attr("disabled", $inputDependienteTipo.val() == "regex");

        $buttonRegex.click(function () {
            $buttonString.prop("disabled", false);
            $buttonRegex.prop("disabled", true);
            $inputDependienteTipo.val("regex");
        });

        $buttonString.click(function () {
            $buttonString.prop("disabled", true);
            $buttonRegex.prop("disabled", false);
            $inputDependienteTipo.val("string");
        });

        // Funcionalidad en campo dependientes para seleccionar entre tipo igualdad y desigualdad
        $buttonDesigualdad = $("#formEditarCampo .campoDependientes .buttonDesigualdad");
        $buttonIgualdad = $("#formEditarCampo .campoDependientes .buttonIgualdad");
        $inputDependienteRelacion = $("#formEditarCampo input[name=dependiente_relacion]");
        $buttonIgualdad.attr("disabled", $inputDependienteRelacion.val() == "==");
        $buttonDesigualdad.attr("disabled", $inputDependienteRelacion.val() == "!=");

        $buttonDesigualdad.click(function () {
            $buttonIgualdad.prop("disabled", false);
            $buttonDesigualdad.prop("disabled", true);
            $inputDependienteRelacion.val("!=");
        });

        $buttonIgualdad.click(function () {
            $buttonIgualdad.prop("disabled", true);
            $buttonDesigualdad.prop("disabled", false);
            $inputDependienteRelacion.val("==");
        });

        // Llenado automatico del campo nombre
        $("#formEditarCampo input[name=etiqueta]").blur(function () {
            ellipsize($("#formEditarCampo input[name=etiqueta]"), $("#formEditarCampo input[name=nombre]"));
        });

        // Llenado automatico del campo valor
        $("#formEditarCampo").on("blur", "input[name$='[etiqueta]']", function () {
            var campoOrigen = $(this);
            var campoDestino = $(this).closest("tr").find("input[name$='[valor]']")
            ellipsize(campoOrigen, campoDestino);
        });

        function ellipsize(campoOrigen, campoDestino) {
            if ($(campoDestino).val() == "") {
                var string = $(campoOrigen).val().trim();
                string = string.toLowerCase();
                string = string.replace(/\s/g, "_");
                string = string.replace(/á/g, "a");
                string = string.replace(/é/g, "e");
                string = string.replace(/í/g, "i");
                string = string.replace(/ó/g, "o");
                string = string.replace(/ú/g, "u");
                string = string.replace(/\W/g, "");
                $(campoDestino).val(string);
            }
        }

        /* Prevenir espacios en campo nombre y que puedan pegar contenido en el mismo campo */
        $(document).on('keypress', '#nombre', function (e) {
            return !(e.keyCode == 32);
        });

        $('#nombre').bind('paste', function (e) {
            e.preventDefault();
        });
    });
</script>

<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">
                Edición de Campo
                <a href="/ayuda/simple/backend/modelamiento-del-proceso/diseno-de-formularios.html#btn_<?= $campo->tipo ?>"
                   target="_blank">
                    <i class="material-icons">help</i>
                </a>
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>

        </div>
        <div class="modal-body">
            <form id="formEditarCampo" class="ajaxForm" method="POST"
                  action="<?= route('backend.forms.editar_campo_form', ($edit ? [$campo->id] : '')) ?>">
                {{csrf_field()}}
                <div class="validacion"></div>
                @if (!$edit)
                    <input type="hidden" name="formulario_id" value="<?= $formulario->id ?>"/>
                    <input type="hidden" name="tipo" value="<?= $campo->tipo ?>"/>
                @endif
                <label>Etiqueta
                </label>
                @if ($campo->etiqueta_tamano == 'xxlarge')
                    <textarea class="form-control col-8" rows="5"
                              name="etiqueta"><?= htmlspecialchars($campo->etiqueta) ?></textarea>
                @else
                    <input type="text" class="form-control col-4" name="etiqueta"
                           value="<?= htmlspecialchars($campo->etiqueta) ?>"/>
                @endif

                <?php if ($campo->requiere_nombre): ?>
                <label>Nombre</label>
                <?php endif; ?>

                <div class="input-group">
                    <?php if ($campo->requiere_nombre): ?>
                    <input type="text" class="form-control col-4" id="nombre" name="nombre"
                           value="<?= $campo->nombre ?>"/>
                    <?php $campos_asistencia = $formulario->Proceso->getNombresDeCampos($campo->tipo, false) ?>

                    <?php if (count($campos_asistencia)): ?>
                    <div class="input-group-append asistencia">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                            <i class="material-icons">list</i><span class="caret"></span>
                        </button>
                        <div class="dropdown-menu">
                            @foreach ($campos_asistencia as $c)
                                <a class="dropdown-item" href="#"><?= $c ?></a>
                            @endforeach
                        </div>
                    </div>
                    <br>
                    <?php endif ?>
                    <?php else: ?>
                    <input type="hidden" name="nombre" value="<?=$campo->nombre ? $campo->nombre : uniqid();?>"/>
                    <?php endif; ?>
                </div>

                <?php if (!$campo->estatico):?>
                <label>Ayuda contextual (Opcional)</label>
                <input type="text" class="form-control col-4" name="ayuda" value="<?=$campo->ayuda?>"/>
                <?php endif ?>

                <?php if (!$campo->estatico): ?>
                <?php if (isset($campo->datos_agenda) && $campo->datos_agenda) { ?>
                <label style="display: none;" class="checkbox">
                    <input type="checkbox" name="readonly" value="1" <?=$campo->readonly ? 'checked' : ''?> />
                    Solo lectura
                </label>
                <?php } else { ?>
                <label class="checkbox"><input type="checkbox" name="readonly"
                                               value="1" <?=$campo->readonly ? 'checked' : ''?> /> Solo lectura</label>
                <?php } ?>
                <?php endif; ?>
                <?php if (!$campo->estatico): ?>
                <?php if (isset($campo->datos_agenda) && $campo->datos_agenda) { ?>
                <label style="display:none;">Reglas de validación
                    <a href="/ayuda/simple/backend/modelamiento-del-proceso/reglas-de-negocio-y-reglas-de-validacion.html"
                       target="_blank">
                        <i class="material-icons">help</i>
                    </a>
                </label>
                <input style="display: none;" class='validacion' type="text" name="validacion"
                       value="<?= $edit ? implode('|', $campo->validacion) : 'required' ?>"/>
                <?php } else { ?>
                <label>Reglas de validación
                    <a href="/ayuda/simple/backend/modelamiento-del-proceso/reglas-de-negocio-y-reglas-de-validacion.html#validacion_campos"
                       target="_blank">
                        <span class="glyphicon glyphicon-info-sign"></span>
                    </a>
                </label>
                <input class='validacion form-control' type="text" name="validacion"
                       value="<?= $edit ? implode('|', $campo->validacion) : 'required' ?>"/>
                <?php } ?>

                <?php endif; ?>
                <?php if (!$campo->estatico): ?>
                <?php if ((isset($campo->datos_agenda) && $campo->datos_agenda) || (isset($campo->datos_mapa) && $campo->datos_mapa)) { ?>
                <label style="display:none;">Valor por defecto</label>
                <input style="display:none;" class="form-control" type="text" name="valor_default"
                       value="<?=htmlspecialchars($campo->valor_default)?>"/>
                <?php } else { ?>
                <label>Valor por defecto</label>
                <input type="text" class="form-control" name="valor_default"
                       value="<?=htmlspecialchars($campo->valor_default)?>"/>
                <?php } ?>
                <?php endif ?>
                <label>Visible solo si</label>
                <div class="campoDependientes">
                    <div class="form-inline">
                        <input type="text" class="form-control col-4" name="dependiente_campo"
                               value="<?=$campo->dependiente_campo?>"/>
                        <div class="btn-group dependiente ml-1" style="display: inline-block; vertical-align: top;">
                            <a class="btn btn-light dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="material-icons">view_list</i> <span class="caret align-middle"></span>
                            </a>
                            <div class="dropdown-menu">
                                <button class="dropdown-item" type="button"><b>Campos</b></button>
                                @foreach ($formulario->Proceso->getCampos() as $c)
                                    <a class="dropdown-item" href="#"><?= $c->nombre ?></a>
                                @endforeach
                                <div class="dropdown-divider"></div>
                                <button class="dropdown-item" type="button"><b>Variables</b></button>
                                @foreach ($formulario->Proceso->getVariables() as $v)
                                    <a class="dropdown-item" href="#"><?= $v->extra->variable ?></a>
                                @endforeach
                            </div>
                        </div>

                        <!-- <select class="input-medium" name="dependiente_campo"> -->
                        <!-- </select> -->

                        <div class="btn-group ml-3 mr-3">
                            <button type="button" class="buttonIgualdad btn btn-secondary">=</button>
                            <button type="button" class="buttonDesigualdad btn btn-secondary">!=</button>
                        </div>

                        <input type="hidden" name="dependiente_relacion"
                               value="<?=isset($campo) && $campo->dependiente_relacion ? $campo->dependiente_relacion : '==' ?>"/>

                        <span class="input-append">
                        <input type="text" class="form-control" name="dependiente_valor"
                               value="<?= isset($campo) ? $campo->dependiente_valor : '' ?>"/>
                        <button type="button" class="buttonString btn btn-secondary">String</button>
                        <button type="button" class="buttonRegex btn btn-secondary">Regex</button>
                    </span>
                        <input type="hidden" name="dependiente_tipo"
                               value="<?=isset($campo) && $campo->dependiente_tipo ? $campo->dependiente_tipo : 'string' ?>"/>

                        @if (isset($campo->datos_mapa) && $campo->datos_mapa)
                            <script type="text/javascript">
                                $(function () {
                                    $("[name=readonly]").click(function () {
                                        if (this.checked) {
                                            $('.columnas').show();
                                        } else {
                                            $("#formEditarCampo .columnas table tbody tr").remove();
                                            $('.columnas').hide();
                                        }
                                    });
                                });
                            </script>
                        @endif
                    </div>

                    <?php if (isset($campo->datos_agenda) && $campo->datos_agenda): ?>
                    <div class="form-group">
                        <label>Pertenece a: </label>
                        <div class="input-group mb-3">
                            <select id="selectgrupo" class="form-control col-4" name="grupos_usuarios"></select>
                            <div class="input-group-append">
                                <button class="btn btn_filtrar_agenda vtop btn-light" type="button">Filtrar</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Agenda:</label>
                        <select id="miagenda" class="form-control col-4" name="agenda_campo">
                            <option value="1">Seleccione(Opcional)</option>
                        </select>
                    </div>
                    <script type="text/javascript">
                        $(function () {
                            $("#selectgrupo").select2({
                                placeholder: "Seleccione(Opcional)",
                                allowClear: true,
                                multiple: false,
                                templateSelection: selection,
                                templateResult: format
                            });

                            $("#selectgrupo").change(function () {
                                $("#miagenda").html('');
                                var idseleccionado = $(this).val();
                                $.ajax({
                                    url: '<?= route('backend.forms.ajax_mi_calendario') ?>',
                                    dataType: "json",
                                    data: {
                                        pertenece: idseleccionado
                                    },
                                    success: function (data) {
                                        if (data.code == 200) {
                                            var items = data.calendars;
                                            $('#miagenda').html('');
                                            if (items.length > 0) {
                                                $("#miagenda").removeAttr('disabled');
                                                $.each(items, function (index, element) {
                                                    $("#miagenda").append('<option value="' + element.id + '">' + element.name + '</option>');
                                                });
                                                var swedit = <?php echo (isset($edit) && $edit) ? 1 : 0; ?>;
                                                if (swedit == 1) {
                                                    var idagenda = <?= $idagendaeditar ?>;
                                                    $('#miagenda').val(idagenda);
                                                }
                                            }
                                        }
                                    }
                                });
                            });

                            $.ajax({
                                url: '<?= route('backend.forms.listarPertenece') ?>',
                                dataType: "json",
                                success: function (data) {
                                    if (data.code == 200) {
                                        var items = data.resultado.items;
                                        $.each(items, function (index, element) {
                                            console.log(element);
                                            var icon = 'person';
                                            if (element.tipo == 1) {
                                                icon = 'group';
                                            }
                                            $("#selectgrupo").append('<option value="' + element.id + '" data-icon="' + icon + '" >' + element.nombre + '</option>');
                                        });
                                    }
                                }
                            });
                            var swedit = <?php echo (isset($edit) && $edit) ? 1 : 0; ?>;
                            if (swedit == 1) {
                                var idagenda = <?= $idagendaeditar ?>;
                                cargar_service(idagenda);
                            }
                        });

                        function format(icon) {
                            var originalOption = icon.element;
                            return $('<span><i class="material-icons" style="top: 1px;">' + $(originalOption).data('icon') + '</i>&nbsp;&nbsp;' + icon.text + '</span>');
                        }

                        function selection(icon) {
                            var originalOption = icon.element;
                            return $('<span><i class="material-icons" style="top: 7px;">' + $(originalOption).data('icon') + '</i>&nbsp;&nbsp;' + icon.text + '</span>');
                        }

                        function cargar_service(idagenda) {
                            $.ajax({
                                url: '<?= route('backend.forms.obtener_agenda') ?>',
                                dataType: "json",
                                data: {
                                    idagenda: idagenda
                                },
                                success: function (data) {
                                    if (data.code == 200) {
                                        var options = $('#selectgrupo').find('option');
                                        var owner = data.calendario_owner;
                                        var indexpertenece = 0;
                                        var v = 0;
                                        $.each(options, function (index, value) {
                                            if ($(value).text().indexOf(owner) >= 0) {
                                                indexpertenece = index;
                                            }
                                        });
                                        $("#selectgrupo").select2("val", options[indexpertenece].value);
                                    }
                                }
                            });
                        }
                    </script>
                    <?php endif; ?>
                </div>

                <?=$campo->extraForm() ? $campo->extraForm() : '' ?>

                <?php if ($campo->requiere_datos): ?>
                <div class="datos">
                    <script type="text/javascript">
                        $(document).ready(function () {
                            $('#formEditarCampo .datos .nuevo').click(function () {
                                var pos = $('#formEditarCampo .datos table tbody tr').length;
                                var html = '<tr>';
                                html += '<td><input type="text" name="datos[' + pos + '][etiqueta]" class="form-control" /></td>';
                                html += '<td><input class="form-control" type="text" name="datos[' + pos + '][valor]" /></td>';
                                html += '<td><button type="button" class="btn btn-light eliminar"><i class="material-icons">close</i> Eliminar</button></td>';
                                html += '</tr>';

                                $('#formEditarCampo .datos table tbody').append(html);
                            });
                            $('#formEditarCampo .datos').on('click', '.eliminar', function () {
                                $(this).closest('tr').remove();
                            });
                        });
                    </script>
                    <h4>Datos</h4>
                    <button class="btn btn-light nuevo" type="button"><i class="material-icons">add</i> Nuevo
                    </button>
                    <table class="table mt-3">
                        <thead>
                        <tr>
                            <th>Etiqueta</th>
                            <th>Valor</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($campo->datos): ?>
                        <?php $i = 0 ?>
                        <?php foreach ($campo->datos as $key => $d): ?>
                        <tr>
                            <td>
                                <input type="text" name="datos[<?= $i ?>][etiqueta]" value="<?= $d->etiqueta ?>"
                                       class="form-control"/>
                            </td>
                            <td>
                                <input class="form-control" type="text" name="datos[<?= $i ?>][valor]"
                                       value="<?= $d->valor ?>"/>
                            </td>
                            <td>
                                <button type="button" class="btn btn-light eliminar">
                                    <i class="material-icons">close</i> Eliminar
                                </button>
                            </td>
                        </tr>
                        <?php $i++ ?>
                        <?php endforeach; ?>
                        <?php endif ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
                <?=$campo->backendExtraFields()?>
            </form>
        </div>
        <div class="modal-footer">
            <a href="#" data-dismiss="modal" class="btn btn-light">Cerrar</a>
            <a href="#" onclick="javascript:$('#formEditarCampo').submit();return false;" class="btn btn-primary">Guardar</a>
        </div>
    </div>
</div>
