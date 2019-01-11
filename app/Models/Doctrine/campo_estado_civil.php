<?php
require_once('campo.php');
class CampoEstadoCivil extends Campo{
    
    public $requiere_datos=false;
    
    protected function display($modo, $dato) {
        $display = '<label class="control-label">' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display.='<div class="controls">';
        $display.='<select class="select-semi-large form-control" id="civiles_'.$this->id.'" data-id="'.$this->id.'" name="' . $this->nombre . '" ' . ($modo == 'visualizacion' ? 'readonly' : '') . '>';
        $display.='</select>';
        if($this->ayuda)
            $display.='<span class="help-block">'.$this->ayuda.'</span>';
        $display.='</div>';

        //"AF":"Afganist\u00e1n",
        $display.='
            <script>
                $(document).ready(function(){
                    
                    $("#civiles_'.$this->id.'").chosen({"placeholder_text": "Seleccione Estado Civil"});
                    
                    var justLoadedEstadoCivil=true;
                    var defaultEstadoCivil="'.($dato && $dato->valor?$dato->valor:'').'";
                        
                    updateCiviles();
                    
                    function updateCiviles(){
                        var data={
                            "Ca":"Casado(a)",
                            "Dv":"Divorciado(a)",
                            "So":"Soltero(a)",
                            "Vi":"Viudo(a)"
                        };
                        
                        var civiles_obj = $("#civiles_'.$this->id.'");
                        civiles_obj.empty();
                        $.each(data,function(i,el){
                            civiles_obj.append("<option value=\""+el+"\">"+el+"</option>");
                        });

                        if(justLoadedEstadoCivil){
                            civiles_obj.val(defaultEstadoCivil).change();
                            justLoadedEstadoCivil=false;
                        }
                        civiles_obj.trigger("chosen:updated");
                        
                    }
                    
                });
            </script>';
        
        return $display;
    }
    
}