<?php
require_once('campo.php');

use App\Helpers\Doctrine;
use Illuminate\Http\Request;

class CampoBtnAsincrono extends Campo
{

    public $requiere_datos = false;

    protected function display($modo, $dato, $etapa_id = false)
    {
        if ($etapa_id) {
            $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
            $regla = new Regla($this->valor_default);
            $valor_default = $regla->getExpresionParaOutput($etapa->id);
        } else {
            $valor_default = $this->valor_default;
        }

        $display = '<div class="form-group float-right">';
        $display .= '<button id="' . $this->id . '" ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' type="button" class="form-control btn btn-success" name="' . $this->nombre . '" value="' . ($dato ? htmlspecialchars($dato->valor) : htmlspecialchars($valor_default)) . '" data-modo="' . $modo . '" > ' . $this->etiqueta . '</button>';
        if ($this->ayuda)
            $display .= '<span class="help-block">' . $this->ayuda . '</span>';
        $display .= '</div><br>';

        $display .= '<div id="ajax_loader" style="position: fixed; left: 50%; top: 50%; display: none;">
                        <img src="'.asset('img/loading.gif').'"></img>
                     </div>';

        if($etapa_id){
            $display .= '
                <script>
                    var table;
                    $(document).ready(function(){

                        $("#'.$this->id.'").click(function(){
                            var table = $("#grilla-2897").DataTable();
                            var sData = table.$("input").serialize();
                            console.log(sData);
                            $("#'.$this->id.'").prop("disabled",true);
                            $.event.trigger("ajaxStart");
                            var myData = $("form").serializeArray();
                            $.ajax({
                                url: "'.url("etapas/save/". $etapa->id ."") .'",
                                data: myData,
                                type: "POST",
                                success: function(data){
                                    $("#'.$this->id.'").prop("disabled",false);
                                    $.event.trigger("ajaxStop");
                                }
                            });
                        })

                        $(document).ajaxStop(function(){
                            $("#ajax_loader").hide();
                        });
                        $(document).ajaxStart(function(){
                             $("#ajax_loader").show();
                        });
                        
                    });
                    
                </script>';
        }

        return $display;
    }

}