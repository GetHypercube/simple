<?php
require_once('campo.php');

use Illuminate\Http\Request;

class CampoComunas extends Campo
{

    public $requiere_datos = false;

    protected function display($modo, $dato)
    {
        $valor_default = json_decode($this->valor_default);
        if (!$valor_default) {
            $valor_default = new stdClass();
            $valor_default->region = '';
            $valor_default->comuna = '';
        }

        $display = '<label class="control-label">' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display .= '<div class="controls">';
        $display .= '<select class="form-control" id="regiones_'.$this->id.'" data-id="' . $this->id . '" name="' . $this->nombre . '[region]" ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' style="width:100%">';
        $display .= '<option value="">Seleccione Regi&oacute;n</option>';
        $display .= '</select>';
        $display .= '<br />';
        $display .= '<br />';
        $display .= '<select class="form-control" id="comunas_'.$this->id.'" data-id="' . $this->id . '" name="' . $this->nombre . '[comuna]" ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' style="width:100%">';
        $display .= '<option value="">Seleccione Comuna</option>';
        $display .= '</select>';
        if ($this->ayuda)
            $display .= '<span class="help-block">' . $this->ayuda . '</span>';
        $display .= '</div>';

        $display .= '
            <script>
                $(document).ready(function(){
                    var justLoadedRegion=true;
                    var justLoadedComuna=true;
                    var defaultRegion="' . ($dato && $dato->valor ? $dato->valor->region : $valor_default->region) . '";
                    var defaultComuna="' . ($dato && $dato->valor ? $dato->valor->comuna : $valor_default->comuna) . '";
                    var opcion = "'. (isset($this->extra->codigo) && $this->extra->codigo ? "codigo" : "nombre") .'";

                    updateRegiones();
                    
                    function updateRegiones(){
                        $.getJSON("https://apis.digital.gob.cl/dpa/regiones?callback=?",function(data){
                            
                            var regiones_obj = $("#regiones_'.$this->id.'");
                            regiones_obj.empty();
                            regiones_obj.append("<option value=\'\'>Seleccione Regi&oacute;n</option>");
                            $.each(data, function(idx, el){
                                regiones_obj.append("<option data-id=\""+el.codigo+"\" value=\""+el.nombre+"\">"+el.nombre+"</option>");
                            });
                            
                            regiones_obj.change(function(event){
                                var selectedId=$(this).find("option:selected").attr("data-id");
                                updateComunas(selectedId);
                            });
                            
                            if(justLoadedRegion){
                                regiones_obj.val(defaultRegion).change();
                                justLoadedRegion=false;
                            }
                            regiones_obj.trigger("chosen:updated");
                        });
                    }
                    
                    function updateComunas(regionId){
                        var comunas_obj = $("#comunas_'.$this->id.'");
                        comunas_obj.empty();
                        comunas_obj.append("<option value=\'\'>Seleccione Comuna</option>");
                        if(typeof regionId === "undefined")
                            return;
                        
                        $.getJSON("https://apis.digital.gob.cl/dpa/regiones/"+regionId+"/comunas?callback=?",function(data){
                            if(data){
                                $.each(data, function(idx, el){
                                    var op = el[opcion];
                                    console.log("op: " + op);
                                    comunas_obj.append("<option data-id=\""+el.codigo+"\" value=\""+op+"\" >"+el.nombre+"</option>"); 
                                });
                            }
                            comunas_obj.trigger("chosen:updated");
                            if(justLoadedComuna){
                                comunas_obj.val(defaultComuna).change();
                                justLoadedComuna=false;
                            }
                        });
                    }
                    $("#regiones_'.$this->id.'").chosen();
                    $("#comunas_'.$this->id.'").chosen();
                });
                
            </script>';

        return $display;
    }

    public function formValidate(Request $request, $etapa_id = null)
    {

        $request->validate([
            $this->nombre . '.region' => implode('|', $this->validacion),
            $this->nombre . '.comuna' => implode('|', $this->validacion),
        ]);
        /*
        $CI =& get_instance();
        $CI->form_validation->set_rules($this->nombre . '[region]', $this->etiqueta . ' - Región', implode('|', $this->validacion));
        $CI->form_validation->set_rules($this->nombre . '[comuna]', $this->etiqueta . ' - Comuna', implode('|', $this->validacion));
        */
    }

    public function backendExtraFields(){
        $codigo = isset($this->extra->codigo) ? $this->extra->codigo : null;
        $html = '<div class="form-check">
                    <input class="form-check-input" type="checkbox" name="extra[codigo]" id="checkbox_codigo"  ' . ($codigo ? 'checked' : '') . ' /> 
                    <label for="checkbox_codigo" class="form-check-label">Utilizar código en select comunas.</label>
                    </div>';
        $html .= ' </label>';
        
        return $html;
    }

}