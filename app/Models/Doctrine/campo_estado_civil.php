<?php
require_once('campo.php');
class CampoEstadoCivil extends Campo{
    
    public $requiere_datos=false;
    
    protected function display($modo, $dato) {

        //$display = '<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>';


        $display = '<label class="control-label">' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display.='<div class="controls">';
        $display.='<select class="select-semi-large civiles form-control" data-id="'.$this->id.'" name="' . $this->nombre . '" ' . ($modo == 'visualizacion' ? 'readonly' : '') . '>';
        $display.='<option value="">Seleccione Estado Civil</option>';
        $display.='</select>';
        if($this->ayuda)
            $display.='<span class="help-block">'.$this->ayuda.'</span>';
        $display.='</div>';

        //"AF":"Afganist\u00e1n",
        $display.='
            <script>
                $(document).ready(function(){

                    $(".civiles").select2({
                        placeholder:"Seleccione Estado Civil"    
                    });

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
                            
                            var html="<option value=\'\'></option>";
                            $.each(data,function(i,el){
                                html+="<option value=\""+el+"\">"+el+"</option>";
                            });
                            
                            $("select.civiles[data-id='.$this->id.']").html(html);
                            
                            if(justLoadedEstadoCivil){
                                $("select.civiles[data-id='.$this->id.']").val(defaultEstadoCivil).change();
                                justLoadedEstadoCivil=false;
                            }
                        
                    }
                    
                });
                

                
            </script>';
        

        return $display;
    }
    
    
}