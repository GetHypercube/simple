<?php
require_once('campo.php');

use Illuminate\Http\Request;
use App\Helpers\Doctrine;


class CampoDate extends Campo
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
        $display .= '<input id="' . $this->id . '" class="datepicker form-control" ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' type="date" name="' . $this->nombre . '" value="' . ($dato && $dato->valor ? date('Y-m-d', strtotime($dato->valor)) : ($valor_default ? date('Y-m-d', strtotime($valor_default)) : $valor_default)) . '" placeholder="dd-mm-aaaa" />';

        if ($this->ayuda) {
            $display .= '<span class="help-block">' . $this->ayuda . '</span>';
        }
        $display .= '</div>';

        return $display;
    }

    public function formValidate(Request $request, $etapa_id = null)
    {
        $request->validate([
            $this->nombre => implode('|', array_merge(array('date_prep'), $this->validacion))
        ]);
        /*
        $CI = &get_instance();
        $CI->form_validation->set_rules($this->nombre, $this->etiqueta, implode('|', array_merge(array('date_prep'), $this->validacion)));
        */
    }
}