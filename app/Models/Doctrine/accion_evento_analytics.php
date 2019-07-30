<?php
require_once('accion.php');

use App\Helpers\Doctrine;
use Illuminate\Http\Request;

class AccionEventoAnalytics extends Accion
{

    public function displayForm()
    {
        $id_instancia = env('ANALYTICS');
        $display  = '<br><label>Selecciona el ID de Seguimiento a enviar &nbsp;</label>';
        $display .='Nota: El 1º Código es el de la Instancia el 2ª es el de tu cuenta';
        $display .= '<select name="extra[id_seguimiento]"  class="form-control col-2">
                      <option value="'.$id_instancia.'">'.$id_instancia.'</option>
                      <option background-color: red;  value="' .  Cuenta::seo_tags()->analytics  . '" >' .  Cuenta::seo_tags()->analytics  . ' </option>
                      </select>';
       
        $display .= '<br><label>Categoría</label>';
        $display .= '<input type="text" class="form-control col-2" id="categoria" name="extra[categoria]" value="' . (isset($this->extra->categoria) ? $this->extra->categoria : '') . '" />';
        $display .= '<br><label>Evento a Enviar (ID RNT válido para intituciones)</label>';
        $display .= '<input type="text" class="form-control col-2" name="extra[evento_enviante]" value="' . (isset($this->extra->evento_enviante) ? $this->extra->evento_enviante : '') . '" />';
        $display .= '<br><label>Formación de Hit Enviante a Google Analytics</label>';
      
        return $display;
    }

    public function validateForm(Request $request)
    {
        $request->validate([
            'extra.categoria' => 'required',
            'extra.evento_enviante' => 'required',
        ]);
    }

    //public function ejecutar(Etapa $etapa)
    public function ejecutar($tramite_id)
    {
        
        $etapa = $tramite_id;
        //$id_analytics = Cuenta::seo_tags()->analytics;
        $regla = new Regla($this->extra->id_seguimiento);
        $id_seguimiento = $regla->getExpresionParaOutput($etapa->id);

        $regla = new Regla($this->extra->categoria);
        $categoria = $regla->getExpresionParaOutput($etapa->id);
        $evento_enviante = null;
        
        if (isset($this->extra->evento_enviante)) {
            $regla = new Regla($this->extra->evento_enviante);
            $evento_enviante = $regla->getExpresionParaOutput($etapa->id);
        }
        
    }

}
