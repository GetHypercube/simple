<?php
require_once('campo.php');

use App\Helpers\Doctrine;
use Illuminate\Http\Request;

class CampoText extends Campo
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

        $display = '<div class="form-group">';
        $display .= '<label class="control-label" for="' . $this->id . '">' . $this->etiqueta . (!in_array('required', $this->validacion) ? ' (Opcional)' : '') . '</label>';
        $display .= '<input id="' . $this->id . '" ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' type="text" class="form-control has-error" name="' . $this->nombre . '" value="' . ($dato ? htmlspecialchars($dato->valor) : htmlspecialchars($valor_default)) . '" data-modo="' . $modo . '" />';
        if ($this->ayuda)
            $display .= '<span class="help-block">' . $this->ayuda . '</span>';
        $display .= '</div>';

        $searchword = 'max';
        $matches = array_filter($this->validacion, function($var) use ($searchword) { return preg_match("/\b$searchword\b/i", $var); });
        if(isset($matches[1])){
            $limite = str_replace("max:","",$matches[1]);
            $display .= '
            <script>
                $(document).ready(function(){
                    $("#' . $this->id . '").EnsureMaxLength({
                        limit: '.$limite.'
                    });
                });
            </script>';
        }
            

        return $display;
    }

}