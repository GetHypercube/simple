<?php
require_once('campo.php');

use Illuminate\Http\Request;

class CampoCheckbox extends Campo
{

    protected function display($modo, $dato)
    {
        $display = '<label class="control-label">' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display .= '<div class="controls">';
        foreach ($this->datos as $d) {
            $display .= '<label class="checkbox">';
            $display .= '<input ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' type="checkbox" name="' . $this->nombre . '[]" value="' . $d->valor . '" ' . ($dato && $dato->valor && in_array($d->valor, $dato->valor) ? 'checked' : '') . ' /> ';
            $display .= $d->etiqueta . '</label>';
        }
        if ($this->ayuda)
            $display .= '<span class="text-muted form-text">' . $this->ayuda . '</span>';
        $display .= '</div>';

        return $display;
    }

    public function backendExtraValidate(Request $request)
    {
      /*  $request->validate(['datos' => 'required']);
        $CI =& get_instance();
        $CI->form_validation->set_rules('datos', 'Datos', 'required');*/
    }

}