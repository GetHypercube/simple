<?php
require_once('campo.php');

use App\Helpers\Doctrine;

class CampoFileS3 extends Campo
{
    public $requiere_datos = false;
    public $min_validations = [];
    public $block_size = 8388608; // 8 MB
    public $file_expire_minutes = 14400; // 24 horas

    protected function display($modo, $dato, $etapa_id = false)
    {
        if(isset($this->extra->block_size)&& is_numeric($this->extra->block_size)){
            $this->block_size = intval($this->extra->block_size);
        }

        if (!$etapa_id) {
            $display = '<label class="control-label">' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
            $display .= '<div class="controls">';
            $display .= '<input id="' . $this->id . '" type="hidden" name="' . $this->nombre . '" value="" />';
            $display .= '<button type="button" class="btn btn-light">Subir archivo</button>';

            if ($this->ayuda) {
                $display .= '<span class="form-text text-muted">' . $this->ayuda . '</span>';
            }

            $display .= '</div>';

            return $display;
        }

        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
        
        $display = '<label class="control-label">' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display .= '
        <div class="controls">
            <input id="' . $this->id . '" type="hidden" name="' . $this->nombre . '" value="" data-grizmio="grizmio" />
            <label for="file_input_'.$this->id.'">Archivo:</label>
            <input id="file_input_'.$this->id.'" type="file">
            <div id="parts_div_'.$this->id.'" style="display:none;">
                <progress id="progress_file_'.$this->id.'" value=0 max=1></progress>
                <label id="segments_sent_'.$this->id.'">0</label> de <label id="total_segments_'.$this->id.'">0</label> partes subidas.
            </div>
            <div>
                <button id="but_send_file_'.$this->id.'" type="button" disabled="true" 
                    class="btn btn-secondary"/>
                    <i class="material-icons">file_upload</i>
                    Subir archivo
                </button>
                <input id="but_stop_'.$this->id.'" type="button" value="Detener subida" disabled="true" 
                    class="btn btn-secondary"/>
            </div>
        
            <script>            
                $(document).ready(function() {
                    var token = $(\'meta[name="csrf-token"]\').attr(\'content\'); 
                    set_up('.$this->id.', "'.url("uploader/datos_s3/" . $this->id . "/" . $etapa->id) .'", token, '.$this->block_size.');
                });
            </script>
        ';

        if ($dato) {
            $display .= '<p class="link"><a href="' . $dato->valor . '" target="_blank">' . htmlspecialchars($dato->valor) . '</a>';
            if (!($modo == 'visualizacion'))    
                $display .= '(<a class="remove" href="#">X</a>)</p>';
        } else {
            $display .= '<p class="link"></p>';
        }

        if ($this->ayuda)
            $display .= '<span class="form-text text-muted">' . $this->ayuda . '</span>';
        
            $display .= '</div>';
        return $display;
    }

    public function extraForm()
    {
        $filetypes = array();
        if (isset($this->extra->filetypes)) {
            $filetypes = $this->extra->filetypes;
        }
        
        $info_s3 = '';
        if (isset($this->extra->info_s3)) {
            $info_s3 = ($this->extra->info_s3);
        }

        $file_expire_minutes = isset($this->extra->expire_time) ? $this->extra->expire_time : $this->file_expire_minutes;

        $output = <<<EOD
        <br/>
        <div class="controls s3_variables">
            <label class="control-label">Almacenar respuesta en</label>
            <input type="text" class="form-control" name="extra[info_s3]" value="{$info_s3}"/>
        </div>
        <br/>
        <div class="controls s3_upload_size">
            <label class="control-label">Tamaño de cada bloque</label>
            <select name="extra[block_size]" class="form-control">
                <option value="8388608" selected>8 MB</option>
            </select>
        </div>

        <div class="controls s3_expire_time">
            <label class="control-label">Tiempo de vencimiento de enlace en minutos</label>
            <input id="expire_time" type="number" name="extra[expire_time]" class="form-control" min=1 step=1 
            value='{$file_expire_minutes}'/>
        </div>
        <br/>
EOD;
        $output .= '<div class="controls s3_extra_files">';
        $output .= '<label class="control-label">Tipos de archivos por extension</label>';
        $output .= '<select name="extra[filetypes][]" class="form-control" multiple>';
        $output .= '<option name="jpg" ' . (in_array('jpg', $filetypes) ? 'selected' : '') . '>jpg</option>';
        $output .= '<option name="png" ' . (in_array('png', $filetypes) ? 'selected' : '') . '>png</option>';
        $output .= '<option name="gif" ' . (in_array('gif', $filetypes) ? 'selected' : '') . '>gif</option>';
        $output .= '<option name="pdf" ' . (in_array('pdf', $filetypes) ? 'selected' : '') . '>pdf</option>';
        $output .= '<option name="doc" ' . (in_array('doc', $filetypes) ? 'selected' : '') . '>doc</option>';
        $output .= '<option name="docx" ' . (in_array('docx', $filetypes) ? 'selected' : '') . '>docx</option>';
        $output .= '<option name="xls" ' . (in_array('xls', $filetypes) ? 'selected' : '') . '>xls</option>';
        $output .= '<option name="xlsx" ' . (in_array('xlsx', $filetypes) ? 'selected' : '') . '>xlsx</option>';
        $output .= '<option name="mpp" ' . (in_array('mpp', $filetypes) ? 'selected' : '') . '>mpp</option>';
        $output .= '<option name="vsd" ' . (in_array('vsd', $filetypes) ? 'selected' : '') . '>vsd</option>';
        $output .= '<option name="ppt" ' . (in_array('ppt', $filetypes) ? 'selected' : '') . '>ppt</option>';
        $output .= '<option name="pptx" ' . (in_array('pptx', $filetypes) ? 'selected' : '') . '>pptx</option>';
        $output .= '<option name="zip" ' . (in_array('zip', $filetypes) ? 'selected' : '') . '>zip</option>';
        $output .= '<option name="rar" ' . (in_array('rar', $filetypes) ? 'selected' : '') . '>rar</option>';
        $output .= '<option name="odt" ' . (in_array('odt', $filetypes) ? 'selected' : '') . '>odt</option>';
        $output .= '<option name="odp" ' . (in_array('odp', $filetypes) ? 'selected' : '') . '>odp</option>';
        $output .= '<option name="ods" ' . (in_array('ods', $filetypes) ? 'selected' : '') . '>ods</option>';
        $output .= '<option name="odg" ' . (in_array('odg', $filetypes) ? 'selected' : '') . '>odg</option>';
        $output .= '</select>';
        $output .= '</div>';
        $min_vals = json_encode($this->min_validations); // solo expande variables, no evalua
        $output .= "
        <script>

        $('#expire_time').keydown(function(evt){
            var key_code = evt.which;
            if( key_code != 13 && key_code != 9 && ( key_code < 48 || key_code > 57 ) ) {
                // 13 enter, 9 tab
                evt.preventDefault();
                evt.stopPropagation();
                return false;
            }

        });

        $('input.validacion[type=text]').keydown(function(evt){
            var min_vals = $min_vals;
            var old_vals = $(this).val().replace(/\s*/g, '').split('|');
            var pos = 0;
            
            if( evt.which == 13 || evt.which == 32) {
                // 13 enter, 32 espacio
                evt.preventDefault();
                evt.stopPropagation();
                return false;
            }

            for(i=0;i<min_vals.length;i++){
                if(old_vals.indexOf(min_vals[i]) === -1){
                    old_vals.splice(pos++, 0,min_vals[i]);
                }
            }
            console.log(old_vals.join('|'));
            $(this).val(old_vals.join('|'));
        });
        </script>
    ";

        return $output;
    }
}
