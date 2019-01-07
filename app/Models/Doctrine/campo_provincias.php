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
        $display .= '<select class="regiones form-control" data-id="' . $this->id . '" name="' . $this->nombre . '[region]" ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' style="width:100%">';
        $display .= '<option value="">Seleccione región</option>';
        $display .= '</select>';
        $display .= '<br />';
        $display .= '<br />';
        $display .= '<select class="provincias form-control" data-id="' . $this->id . '" name="' . $this->nombre . '[provincia]" ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' style="width:100%">';
        $display .= '<option value="">Seleccione provincia</option>';
        $display .= '</select>';
        $display .= '<br />';
        $display .= '<br />';
        $display .= '<select class="comunas form-control" data-id="' . $this->id . '" name="' . $this->nombre . '[comuna]" ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' style="width:100%">';
        $display .= '<option value="">Seleccione comuna</option>';
        $display .= '</select>';
        if ($this->ayuda)
            $display .= '<span class="help-block">' . $this->ayuda . '</span>';
        $display .= '</div>';

        $display .= '
            <script>
                $(document).ready(function(){

					$(".regiones").select2({
                        placeholder:"Por favor Seleccione la Regi\u00F3n "    
                    });

                    var justLoadedRegion=true;
                    var justLoadedProvincia=true;
                    var justLoadedComuna=true;
                    var defaultRegion="' . ($dato && $dato->valor ? $dato->valor->region : $valor_default->region) . '";
                    var defaultProvincia="' . ($dato && $dato->valor ? $dato->valor->provincia : $valor_default->provincia) . '";
                    var defaultComuna="' . ($dato && $dato->valor ? $dato->valor->comuna : $valor_default->comuna) . '";
                    

                    updateRegiones();
                    
                    function updateRegiones(){
                        $.getJSON("https://apis.digital.gob.cl/dpa/regiones?callback=?",function(data){
                            var html="<option value=\'\'>Seleccione region</option>";
                            $(data).each(function(i,el){
                                html+="<option data-id=\""+el.codigo+"\" value=\""+el.nombre+"\">"+el.nombre+"</option>";
                            });
                            $("select.regiones[data-id=' . $this->id . ']").html(html).change(function(event){
                                var selectedId=$(this).find("option:selected").attr("data-id");
                                updateProvincias(selectedId);
                            });
                            
                            if(justLoadedRegion){
                                $("select.regiones[data-id=' . $this->id . ']").val(defaultRegion).change();
                                justLoadedRegion=false;
                            }
                        });
                    }
                    
                    function updateProvincias(regionId){

                        $(".provincias").select2({
                            placeholder:"Por favor Seleccione la Provincia! "    
                        });

                        if(!regionId)
                            return;
                        
                        $.getJSON("https://apis.digital.gob.cl/dpa/regiones/"+regionId+"/provincias?callback=?",function(data){
                            var html="<option value=\'\'>Seleccione provincia!</option>";
                            $(data).each(function(i,el){
                                html+="<option data-id=\""+el.codigo+"\" value=\""+el.nombre+"\">"+el.nombre+"</option>";
                            });
                            $("select.provincias[data-id=' . $this->id . ']").html(html).change(function(event){
                                var selectedId=$(this).find("option:selected").attr("data-id");
                                updateComunas(selectedId);
                            });

                            if(justLoadedProvincia){
                                $("select.provincias[data-id=' . $this->id . ']").val(defaultProvincia).change();
                                justLoadedProvincia=false;
                            }
                        });
                    }

                    function updateComunas(provinciaId){

                        $(".comunas").select2({
                            placeholder:"Por favor Seleccione la Comuna "    
                            });

                        if(!provinciaId)
                            return;
                        
                        $.getJSON("https://apis.digital.gob.cl/dpa/provincias/"+provinciaId+"/comunas?callback=?",function(data){
                            var html="<option value=\'\'>Seleccione comuna</option>";
                            if(data){
                                $(data).each(function(i,el){
                                    html+="<option value=\""+el.nombre+"\">"+el.nombre+"</option>";
                                });
                            }
                            $("select.comunas[data-id=' . $this->id . ']").html(html);

                            if(justLoadedComuna){
                                $("select.comunas[data-id=' . $this->id . ']").val(defaultComuna).change();
                                justLoadedComuna=false;
                            }
                        });
                    }
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

}