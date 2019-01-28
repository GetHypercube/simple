<?php
require_once('campo.php');

use Illuminate\Http\Request;
use App\Helpers\Doctrine;

class CampoGridDatosExternos extends Campo
{
    private $javascript;

    public $requiere_datos = false;
    private $cell_text_max_length_default = 50;

    protected function display($modo, $dato, $etapa_id = false)
    {
        $columns = $this->extra->columns;
        
        $botones = [];

        if(isset($this->extra->agregable) && $this->extra->agregable == 'true'){
            $botones[] = '<button type="button" class="btn btn-outline-secondary" onclick="open_add_modal('.$this->id.')">Agregar</button>';
        }
        if(isset($this->extra->eliminable) && $this->extra->eliminable == 'true'){
            $botones[] = '<button type="button" class="btn btn-outline-secondary" style="" onclick="grilla_datos_externos_eliminar('.$this->id.')">Eliminar</button>';
        }

        if( isset($this->extra->buttons_position) && $this->extra->buttons_position === 'bottom' ){
            $botones_position = $this->extra->buttons_position;
        }else{
            $botones_position = 'right_side';
        }

        $editable = false;
        if((isset($this->extra->editable) && $this->extra->editable == 'true')){
            $editable = true;
        }

        $eliminable = false;
        if((isset($this->extra->eliminable) && $this->extra->eliminable == 'true')){
            $eliminable = true;
        }

        $tiene_acciones = false;
        if( $eliminable || $editable ){
            $tiene_acciones = true;
        }

        $display_modal = '
        <div class="modal fade modalgrid" id="table_alter_modal_'.$this->id.'" tabindex="-1" role="dialog" aria-labelledby="add_to_table_modal_label_'.$this->id.'" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="add_to_table_modal_label_'.$this->id.'">Nuevo registro</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="modal-body-'.$this->id.'">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" id="modal_accept_button_'.$this->id.'" class="btn btn-outline-secondary">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
        ';

        $display = '<label class="control-label" for="' . $this->id . '">' . $this->etiqueta . (!in_array('required', $this->validacion) ? ' (Opcional)' : '') . '</label>';
        $display .= '<input type="hidden" name="'.$this->nombre.'" id="'.$this->id.'">';
        $display .= '<div class="controls grid-Cls">
                        <div data-id="' . $this->id . '" >
                            <div class="container">
                                <div class="row">
                                    <div class="">
                                    <table class="table table-hover table-bordered" id="grilla-'.$this->id.'" data-grilla_id="'.$this->id.'">

                                    </table>
                                    </div>
                                    <div class="col-auto colautogrid" style="transform:translateY(+50%);">
                                    <!-- Al lado -->
                                        '.($botones_position == "right_side" ? implode("<br /><br />", $botones) : '').'
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="">
                                        '.($botones_position == "bottom" ? implode("\n", $botones) : '').'
                                    </div>
                                    <div class="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>';
        $grilla_con_datos = '';

        // $display .= '<input type="hidden" name="' . $this->nombre . '" value=\'' . ($dato ? json_encode($dato->valor) : $valor_default) . '\' />';
        if ($this->ayuda)
            $display .= '<span class="help-block">' . $this->ayuda . '</span>';

        $data = [];
        if($dato && count($dato->valor) > 0 ){
            if(is_string($dato->valor))
                $data = json_decode($dato->valor, true);
            else
                $data = $dato->valor;
            if( ! $this->is_array_associative($data) ){
                // hay que corregir llenando con vacios cuando la columna no sea exportable
                $data_temp = [];
                for($i=0; $i<count($data);$i++){
                    for( $j=0; $j < count($columns); $j++){
                        
                        if( $columns[$j]->is_exportable == 'false'){
                            array_splice($data[$i], $j, 0, '');
                            
                        }
                    }
                }
            }
        }else if ($etapa_id ){
            $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
            $regla = new Regla($this->valor_default);
            $data = $regla->getExpresionParaOutput($etapa->id);
        }
        if( is_string($data))
            $data = json_decode($data, true);
        $cell_max_length = (isset($this->extra->cell_text_max_length) ? $this->extra->cell_text_max_length: $this->cell_text_max_length_default);

        $display .='
        <script>
                $(document).ready(function(){
                    var data = '.json_encode($data).';
                    var is_array = '.(count($data) > 0 ? "Array.isArray(data[0])" : "false" ).';
                    var columns = '.json_encode($columns).';
                    grillas_datatable['.$this->id.'] = {};
                    grillas_datatable['.$this->id.'].tiene_acciones = '.($tiene_acciones ? 'true': 'false').';
                    grillas_datatable['.$this->id.'].editable = '.($editable ? 'true': 'false').';
                    grillas_datatable['.$this->id.'].cantidad_columnas = columns.length;
                    if('.($tiene_acciones ? 'true': 'false').'){
                        grillas_datatable['.$this->id.'].cantidad_columnas++;
                    }
                    
                    init_tables('.$this->id.', "'.$modo.'",columns,'.$cell_max_length.',is_array, '.json_encode($editable).','.json_encode($eliminable).');
                    grillas_datatable['.$this->id.'].table.draw(true);
                    
                    if(data.length > 0){    
                        if(is_array){
                            grilla_populate_arrays('.$this->id.', data);
                        }else{
                            grilla_populate_objects('.$this->id.', data);
                        }
                    }else{
                        grillas_datatable['.$this->id.'].table.draw(true);
                    }
                });
            </script>
        ';
        $display .= $display_modal;
        return $display;
    }

    public function backendExtraFields()
    {

        $columns = array();
        if (isset($this->extra->columns))
            $columns = $this->extra->columns;

        $agregable = false;
        if(isset($this->extra->agregable) && $this->extra->agregable == 'true'){
            $agregable = true;
        }

        $eliminable = false;
        if(isset($this->extra->eliminable) && $this->extra->eliminable == 'true'){
            $eliminable = true;
        }

        $editable = false;
        if(isset($this->extra->editable) && $this->extra->editable == 'true'){
            $editable = true;
        }

        $precarga = isset($this->extra->precarga) ? $this->extra->precarga : null;

        $hidden_arr[] = '<input type="hidden" name="extra[agregable]" value="'.($agregable ? 'true': 'false').'" />';
        $hidden_arr[] = '<input type="hidden" name="extra[eliminable]" value="'.($eliminable ? 'true': 'false').'"/>';
        $hidden_arr[] = '<input type="hidden" name="extra[editable]" value="'.($editable ? 'true': 'false').'"/>';
        $output = implode("\n", $hidden_arr);

        $column_template_html = "
                    <tr>
                        <td>
                            <input type='text' name='extra[columns][{{column_pos}}][header]' class='form-control' value='{{header}}' />
                        </td>
                        <td>
                            <input class='form-control' type='input' name='extra[columns][{{column_pos}}][modal_add_text]' value='{{modal_add_text}}'/>
                        </td>
                        <td>
                            <input class='form-control' type='input' name='extra[columns][{{column_pos}}][object_field_name]' value='{{object_field_name}}'/>
                        </td>
                        <td>
                            <input class='form-control' type='checkbox' {{is_input_checked}} onclick='return cambiar_estado_entrada(this, {{column_pos}});'>
                            <input type='hidden' name='extra[columns][{{column_pos}}][is_input]' value='{{is_input}}' />
                        </td>
                        <td>
                            <input class='form-control' type='checkbox' onclick='return cambiar_exportable(this,{{column_pos}});' {{is_exportable_checked}}>
                            <input type='hidden' name='extra[columns][{{column_pos}}][is_exportable]' value='{{is_exportable}}' />
                        </td>
                        <td>
                            <button type='button' class='btn btn-outline-secondary eliminar'><i class='material-icons'>close</i> Eliminar</button>
                        </td>
                    </tr>";

        $column_template_html = str_replace("\n", "", $column_template_html);

        if( isset($this->extra->cell_text_max_length) && ! is_null($this->extra->cell_text_max_length)){
            $cell_text_max_length = $this->extra->cell_text_max_length;
        }else{
            $cell_text_max_length = 50;
        }

        $buttons_position = (isset($this->extra->buttons_position)? $this->extra->buttons_position : 'bottom');

        $output .= '
            <br />
            <div class="input-group controls">
                <label class="controls-label-inputt" for="grilla_datos_externos_table_text_max_length">Largo m&aacute;ximo del texto en las celdas:&nbsp;</label>
                <input class="form-control col-1" type="text" name="extra[cell_text_max_length]" id="grilla_datos_externos_table_text_max_length" value="'.$cell_text_max_length.'"/>
            </div>
            <div class="input-group controls">
                <label for="grilla_agregable">Se puede Agregar</label>
                <input class="controls-inputchk" type="checkbox" id="grilla_agregable" onclick="toggle_checkbox(\'agregable\', this)" '.($agregable ? "checked": "").'/>
            </div>
            <div class="input-group controls">
                <label for="grilla_eliminable">Se puede Eliminar</label>
                <input class="controls-inputchk" type="checkbox" id="grilla_eliminable" onclick="toggle_checkbox(\'eliminable\', this)" '.($eliminable ? 'checked': "").'/>
            </div>
            <div class="input-group controls">
                <label for="grilla_eliminable">Se puede Editar</label>
                <input class="controls-inputchk" type="checkbox" id="grilla_editable" onclick="toggle_checkbox(\'editable\', this)" '.($editable ? 'checked': "").'/>
            </div>
            <div class="input-group controls">
                <label class="controls-label-inputt" for="grilla_datos_externos_posicion_botones">Posicion botones</label>
                <select class="form-control col-2" name="extra[buttons_position]">
                    <option value="bottom" '.($buttons_position == "bottom" ? 'selected=selected': '').'>Abajo</option>
                    <option value="right_side" '.($buttons_position == "right_side" ? 'selected=selected': '').'>Al lado</option>
                </select>
            </div>
            
            <div class="columnas">
                <script type="text/javascript">
                    var column_template = "'.$column_template_html.'";

                    $(document).ready(function(){
                        $("#formEditarCampo .columnas .nuevo").click(function(){
                            var pos=$("#formEditarCampo .columnas table tbody tr").length;
                            var new_col = column_template.replace(/{{column_pos}}/g, pos);
                            new_col = new_col.replace(/{{([^}]+)\}}/g, "");
                            $("#formEditarCampo .columnas table tbody").append(
                                new_col
                            );
                        });
                        $("#formEditarCampo .columnas").on("click",".eliminar",function(){
                            var table = $(this).closest("table");
                            $(this).closest("tr").remove();
                            reindex_columns(table);
                        });
                    });
                    $("#grilla_datos_externos_table_text_max_length").keydown(function(evt){
                        var key_code = evt.which;
                        // solo numeros
                        if( key_code != 13 && key_code != 9 && key_code != 8 && ( key_code < 48 || key_code > 57 ) ) {
                            // 13 enter, 9 tab, 8 backspace
                            evt.preventDefault();
                            evt.stopPropagation();
                            return false;
                        }

                    });
                </script>
                <h4>Columnas</h4>
                <button class="btn btn-light nuevo" type="button"><i class="material-icons">add</i> Nuevo</button>
                <table class="table mt-3 table-striped">
                    <thead>
                        <tr>
                            <th>Etiqueta</th>
                            <th>Texto al agregar</th>
                            <th>Nombre del campo</th>
                            <th>Es entrada</th>
                            <th>Exportable</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    ';

        if ($columns) {
            foreach ($columns as $key => $c) {
                $text = isset($c->modal_add_text) ? $c->modal_add_text: "";

                $column = str_replace('{{column_pos}}', $key, $column_template_html);
                $column = str_replace('{{header}}', $c->header, $column);
                $column = str_replace('{{modal_add_text}}', $text, $column);
                $column = str_replace('{{is_input}}', $c->is_input, $column);
                $column = str_replace('{{object_field_name}}', (isset($c->object_field_name) ? $c->object_field_name : ''), $column);
                if(isset($c->is_input) && $c->is_input=="true"){
                    $column = str_replace('{{is_input}}', 'true', $column);
                    $column = str_replace('{{is_input_checked}}', 'checked', $column);
                }else{
                    $column = str_replace('{{is_input}}', 'false', $column);
                    $column = str_replace('{{is_input_checked}}', '', $column);
                }
                
                if(isset($c->is_exportable) && $c->is_exportable=="true"){
                    $column = str_replace('{{is_exportable}}', 'true', $column);
                    $column = str_replace('{{is_exportable_checked}}', 'checked', $column);
                }else{
                    $column = str_replace('{{is_exportable}}', 'false', $column);
                    $column = str_replace('{{is_exportable_checked}}', '', $column);
                }
                $output .= $column;
            }
        }

        $output .= '
        </tbody>
        </table>
        </div>

        ';

        return $output;
    }

    public function getJavascript()
    {
        return $this->javascript;
    }

    public function backendExtraValidate(Request $request)
    {
        $request->validate(['extra.columns' => 'required']);
    }

    private function is_array_associative($arr)
    {
        if ([] === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
