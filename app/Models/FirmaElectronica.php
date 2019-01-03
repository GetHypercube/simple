<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Illuminate\Http\Request;
use App\Helpers\Doctrine;

class FirmaElectronica extends Model {

    protected $table = 'hsm_configuracion';
    
    public function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('nombre');
        $this->hasColumn('cuenta_id');
        $this->hasColumn('entidad');
        $this->hasColumn('proposito');
    }

    
}
