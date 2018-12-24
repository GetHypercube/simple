<?php
require_once('campo.php');

use App\Helpers\Doctrine;
use Illuminate\Http\Request;

class CampoSelect extends Campo
{

    protected function display($modo, $dato, $etapa_id = false)
    {
        if ($etapa_id) {
            $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
            $regla = new Regla($this->valor_default);
            $valor_default = $regla->getExpresionParaOutput($etapa->id);
        } else {
            $valor_default = json_decode($this->valor_default);
        }

        $display = '<div class="form-group">';
        $display .= '<label for="' . $this->id . '">' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display .= '<select id="' . $this->id . '" class="form-control '.$this->id.'" name="' . $this->nombre . '" ' . ($modo == 'visualizacion' ? 'disabled' : '') . ' data-modo="' . $modo . '">';
        $display .= '<option value="">Seleccionar</option>';
        if ($this->datos) foreach ($this->datos as $d) {
            if ($dato) {
                $display .= '<option value="' . $d->valor . '" ' . ($dato && $d->valor == $dato->valor ? 'selected' : '') . '>' . $d->etiqueta . '</option>';
            } else {
                $display .= '<option value="' . $d->valor . '" ' . ($d->valor == $valor_default ? 'selected' : '') . '>' . $d->etiqueta . '</option>';
            }
        }
        $display .= '</select>';
        if ($this->ayuda)
            $display .= '<span class="help-block">' . $this->ayuda . '</span>';
        $display .= '</div>';

        $display .= '
                    <script>
                        $(document).ready(function(){
                            $(".form-control-chosen").chosen();

                                $("#'.$this->id.'").chosen().change(
                                function (evt) {
                                var label = $(this.options[this.selectedIndex]).closest("optgroup").prop("label");
                
                                });
                        });
                    </script>
    
                ';

        //Para la carga masiva  en select mediante web service
        if ($this->extra && $this->extra->ws) {
            $display .= '
            <script>
                $(document).ready(function(){
                    var defaultValue = "' . ($dato && $dato->valor ? $dato->valor : $this->valor_default) . '";
                    $.ajax({
                        url: "' . $this->extra->ws . '",
                        dataType: "jsonp",
                        jsonpCallback: "callback",
                        success: function(data){
                            var html = "";
                            $("#' . $this->id . '").html("");
                            $.each(data, function (idx, obj) {
                                html = "<option value=\""+obj.valor+"\">"+obj.etiqueta+"</option>";
                                console.log(html);
                                $("#' . $this->id . '").append(html);
                            });
                            $("#' . $this->id . '").trigger("chosen:updated");
                            $("#' . $this->id . '").chosen();
                        },
                        error: function (data) {
                            alert("Error no hay datos");
                        }
                    });
                });

            </script>';
        }

        return $display;
    }

    public function backendExtraFields()
    {
        $ws = isset($this->extra->ws) ? $this->extra->ws : null;

        $html = '<label>URL para cargar opciones desde webservice (Opcional)</label>';
        $html .= '<input class="input-xxlarge" name="extra[ws]" value="' . $ws . '" />';
        $html .= '<div class="help-block">
                El WS debe ser REST JSONP con el siguiente formato: <a href="#" onclick="$(this).siblings(\'pre\').show()">Ver formato</a><br />
                <pre style="display:none">
callback([
    {
        "etiqueta": "Etiqueta 1",
        "valor": "Valor 1"
    },
    {
        "etiqueta": "Etiqueta 2",
        "valor": "Valor 2"
    },
])
                </pre>
                </div>';

        return $html;
    }

    public function backendExtraValidate(Request $request)
    {
        //$CI =& get_instance();
        //$CI->form_validation->set_rules('datos','Datos','required');
    }

}