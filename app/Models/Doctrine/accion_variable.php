<?php
require_once('accion.php');

use App\Helpers\Doctrine;
use Illuminate\Http\Request;

class AccionVariable extends Accion
{

    public function displayForm()
    {
        $display = '<label>Variable</label>';
        $display .= '<div class="input-prepend">';
        $display .= '<span class="add-on">@@</span>';
        $display .= '<input type="text" class="form-control col-2" name="extra[variable]" value="' . ($this->extra ? $this->extra->variable : '') . '" />';
        $display .= '</div>';
        $display .= '<label>Expresión a evaluar</label>';
        $display .= '<textarea class="form-control col-2" name="extra[expresion]" class="input-xxlarge">' . ($this->extra ? $this->extra->expresion : '') . '</textarea>';
        return $display;
    }

    public function validateForm(Request $request)
    {
        $request->validate($request, [
            'extra.variable' => 'required',
            'extra.expresion' => 'required',
        ], [
            'extra.variable.required' => 'El campo Variable es obligatorio',
            'extra.expresion.required' => 'El campo Expresión a evaluar es obligatorio',
        ]);
    }

    //public function ejecutar(Etapa $etapa)
    public function ejecutar($tramite_id)
    {
        $etapa = $tramite_id;

        $regla = new Regla($this->extra->expresion);
        $filewords = array("file_get_contents", "file_put_contents");
        $matchfound = preg_match_all("/\b(" . implode($filewords, "|") . ")\b/i", $this->extra->expresion, $matches);
        $ev = $matchfound ? TRUE : FALSE;
        $valor = $regla->evaluar($etapa->id, $ev);

        $dato = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId($this->extra->variable, $etapa->id);
        if (!$dato)
            $dato = new DatoSeguimiento();
        $dato->nombre = $this->extra->variable;
        $dato->valor = $valor;
        $dato->etapa_id = $etapa->id;
        $dato->save();
    }

}
