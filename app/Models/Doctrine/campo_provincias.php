<?php
require_once('campo.php');

use Illuminate\Http\Request;

class CampoProvincias extends Campo
{

    public $requiere_datos = false;

    protected function display($modo, $dato)
    {
        $valor_default = json_decode($this->valor_default);
        if (!$valor_default) {
            $valor_default = new stdClass();
            $valor_default->region = '';
            $valor_default->provincia = '';
            $valor_default->comuna = '';
        }

        $display = '<label class="control-label">' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display .= '<div class="controls">';
        $display .= '<select class="form-control" id="regiones_'.$this->id.'" data-id="' . $this->id . '" name="' . $this->nombre . '[region]" ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' style="width:100%">';
        $display .= '<option value="">Seleccione Regi&oacute;n</option>';
        $display .= '</select>';
        $display .= '<br />';
        $display .= '<select class="form-control" id="provincias_'.$this->id.'" data-id="' . $this->id . '" name="' . $this->nombre . '[provincia]" ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' style="width:100%">';
        $display .= '<option value="">Seleccione Provincia</option>';
        $display .= '</select>';
        $display .= '<br />';
        $display .= '<select class="form-control" id="comunas_'.$this->id.'" data-id="' . $this->id . '" name="' . $this->nombre . '[comuna]" ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' style="width:100%">';
        $display .= '<option value="">Seleccione Comuna</option>';
        $display .= '</select>';
        $display .= '<input type="hidden" id="pstateCode" name="pstateCode">';
        $display .= '<input type="hidden" id="pstateName" name="pstateName">';
        $display .= '<input type="hidden" id="pcityCode" name="pcityCode">';
        $display .= '<input type="hidden" id="pcityName" name="pcityName">';
        $display .= '<input type="hidden" id="provinciaCode" name="provinciaCode">';
        $display .= '<input type="hidden" id="provinciaName" name="provinciaName">';
        if ($this->ayuda)
            $display .= '<span class="help-block">' . $this->ayuda . '</span>';
        $display .= '</div>';

        $display .= '
            <script>
                $(document).ready(function(){
                    var justLoadedRegion=true;
                    var justLoadedProvincia=true;
                    var justLoadedComuna=true;
                    var defaultRegion="' . ($dato && $dato->valor ? $dato->valor->region : $valor_default->region) . '";
                    var defaultProvincia="' . ($dato && $dato->valor ? $dato->valor->provincia : $valor_default->provincia) . '";
                    var defaultComuna="' . ($dato && $dato->valor ? $dato->valor->comuna : $valor_default->comuna) . '";
                    
                    $("#regiones_'.$this->id.'").chosen({placeholder_text: "Seleccione Regi\u00F3n"});
                    $("#provincias_'.$this->id.'").chosen({placeholder_text: "Seleccione Provincia"});
                    $("#comunas_'.$this->id.'").chosen({placeholder_text: "Selecciona Comuna"});
                    var opcion = "'. (isset($this->extra->codigo) && $this->extra->codigo ? "codigo" : "nombre") .'";

                    function updateRegiones(){
                        var regiones_obj = $("#regiones_'.$this->id.'");
                        $.getJSON("https://apis.digital.gob.cl/dpa/regiones?callback=?",function(data){
                            $.each(data, function(i,el){
                                regiones_obj.append("<option data-id=\""+el.codigo+"\" value=\""+el.nombre+"\">"+el.nombre+"</option>");
                            });
                            
                            if(justLoadedRegion){
                                regiones_obj.val(defaultRegion).change();
                                justLoadedRegion=false;
                            }
                            regiones_obj.trigger("chosen:updated");
                        });
                    }
                    
                    function updateProvincias(regionId){
                        var provincias_obj = $("#provincias_'.$this->id.'");
                        provincias_obj.empty();
                        provincias_obj.append("<option value=\'\'>Seleccione Provincia</option>");
                        if(!regionId)
                            return;
                        
                        $.getJSON("https://apis.digital.gob.cl/dpa/regiones/"+regionId+"/provincias?callback=?",function(data){
                            $.each(data, function(i,el){
                                provincias_obj.append("<option data-id=\""+el.codigo+"\" value=\""+el.nombre+"\">"+el.nombre+"</option>");
                            });

                            if(justLoadedProvincia){
                                provincias_obj.val(defaultProvincia).change();
                                justLoadedProvincia=false;
                            }
                            provincias_obj.trigger("chosen:updated");

                            $("#provinciaCode").val($(provincias_obj).find("option:selected").attr("data-id"));
                            $("#provinciaName").val($(provincias_obj).find("option:selected").text());
                        });
                        
                    }

                    function updateComunas(provinciaId){
                        var comunas_obj = $("#comunas_'.$this->id.'");
                        comunas_obj.empty();
                        comunas_obj.append("<option value=\'\'>Seleccione Comuna</option>");
                        if(!provinciaId){
                            comunas_obj.trigger("chosen:updated");
                            return;
                        }
                        $.getJSON("https://apis.digital.gob.cl/dpa/provincias/"+provinciaId+"/comunas?callback=?",function(data){
                            if(data){
                                $.each(data, function(idx, el){
                                    var op = el[opcion];
                                    comunas_obj.append("<option data-id=\""+el.codigo+"\" value=\""+op+"\" >"+el.nombre+"</option>"); 
                                });
                            }
                            
                            if(justLoadedComuna){
                                comunas_obj.val(defaultComuna).change();
                                justLoadedComuna=false;
                            }
                            comunas_obj.trigger("chosen:updated");

                            $("#pcityCode").val($(comunas_obj).find("option:selected").val());
                            $("#pcityName").val($(comunas_obj).find("option:selected").text());

                            comunas_obj.change(function(event){
                                $("#pcityCode").val($(this).find("option:selected").attr("data-id"));
                                $("#pcityName").val($(this).find("option:selected").text());
                            });
                        });
                    }

                    
                    $("#regiones_'.$this->id.'").change(function(event){
                        var regiones_obj = $("#regiones_'.$this->id.'");
                        var selectedId = $("#regiones_'.$this->id.'").find("option:selected").attr("data-id");
                        $("#pstateCode").val($(this).find("option:selected").attr("data-id"));
                        $("#pstateName").val(regiones_obj.val());
                        updateProvincias(selectedId);
                        updateComunas(0);
                    });
                    
                    $("#provincias_'.$this->id.'").change(function(event){
                        var selectedId = $("#provincias_'.$this->id.'").find("option:selected").attr("data-id");
                        $("#provinciaCode").val($(this).find("option:selected").attr("data-id"));    
                        $("#provinciaName").val($(this).find("option:selected").text());
                        updateComunas(selectedId);
                    });
                    
                    updateRegiones();
                });
                
            </script>';

        return $display;
    }

    public function formValidate(Request $request, $etapa_id = null)
    {

        $request->validate([
            $this->nombre . '.region' => implode('|', $this->validacion),
            $this->nombre . '.provincia' => implode('|', $this->validacion),
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