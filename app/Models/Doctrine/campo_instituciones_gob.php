<?php
require_once('campo.php');

use App\Helpers\Doctrine;
use Illuminate\Http\Request;

class CampoInstitucionesGob extends Campo
{
    public $requiere_datos = false;

    protected function display($modo, $dato)
    {
        $display = '<label class="control-label">' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display .= '<div class="controls">';
        $display .= '<select class="entidades form-control" data-id="' . $this->id . '" name="' . $this->nombre . '[entidad]" ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' style="width: 100%">';
        $display .= '<option></option>';
        $display .= '</select>';
        $display .= '<br />';
        $display .= '<select class="instituciones form-control" data-id="' . $this->id . '" name="' . $this->nombre . '[servicio]" ' . ($modo == 'visualizacion' ? 'readonly' : '') . '>';
        $display .= '<option></option>';
        $display .= '</select>';
        if ($this->ayuda)
            $display .= '<span class="help-block">' . $this->ayuda . '</span>';
        $display .= '</div>';

        $display .= '
            <script>
                $(document).ready(function(){

                    $(".entidades").select2({
                        placeholder:"Por favor Seleccione el Ministerio u Organismo Principal "    
                    });


                    var justLoadedEntidad=true;
                    var justLoadedInstitucion=true;
                    var defaultEntidad="' . ($dato && $dato->valor ? $dato->valor->entidad : '') . '";
                    var defaultInstitucion="' . ($dato && $dato->valor ? $dato->valor->servicio : '') . '";
                        
                    updateEntidades();
                    
                    function updateEntidades(){

                        $(".instituciones").select2({     
                            placeholder:"Por favor Seleccione la Instituci\u00F3n ",
                            allowClear:"true",    
                            width:"100%"     
                        });

                        $.getJSON("https://apis.digital.gob.cl/instituciones/api/entidades?callback=?",function(data){
                            var html="<option></option>";
                            $(data.items).each(function(i,el){
                                html+="<option value=\""+el.nombre+"\" data-id=\""+el.codigo+"\">"+el.nombre+"</option>";
                            });
                            $("select.entidades[data-id=' . $this->id . ']").html(html).change(function(event){
                                var selectedId=$(this).find("option:selected").data("id");
                                updateInstituciones(selectedId);
                            });
                            
                            if(justLoadedEntidad){
                                $("select.entidades[data-id=' . $this->id . ']").val(defaultEntidad).change();
                                justLoadedEntidad=false;
                            }
                        });
                    }
                    
                    function updateInstituciones(entidadId){
                        
                        $.getJSON("https://apis.digital.gob.cl/instituciones/api/entidades/"+entidadId+"/instituciones?callback=?",function(data){
                            var html="<option></option>";
                            if(data){
                                $(data.items).each(function(i,el){
                                    html+="<option value=\""+el.nombre+"\">"+el.nombre+"</option>";
                                });
                            }
                            $("select.instituciones[data-id=' . $this->id . ']").html(html);

                            if(justLoadedInstitucion){
                                $("select.instituciones[data-id=' . $this->id . ']").val(defaultInstitucion).change();
                                justLoadedInstitucion=false;
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
            $this->nombre . '.entidad' => implode('|', $this->validacion),
            $this->nombre . '.servicio' => implode('|', $this->validacion)
        ]);

        /*
        $CI =& get_instance();
        $CI->form_validation->set_rules($this->nombre . '[entidad]', $this->etiqueta . ' - Entidad', implode('|', $this->validacion));
        $CI->form_validation->set_rules($this->nombre . '[servicio]', $this->etiqueta . ' - Servicio', implode('|', $this->validacion));
        */
    }

}