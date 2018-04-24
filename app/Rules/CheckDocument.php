<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
use Doctrine_Query;

class CheckDocument implements Rule
{
    public $request;

    public $message;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $id = $this->request->input('id');
        $key = $this->request->input('key');
        $key = preg_replace('/\W/', '', $key);

        $file = Doctrine_Query::create()
            ->from('File f')
            ->where('f.id = ?', $id)
            ->fetchOne();

        if (!$file) {
            $this->message = 'Folio y/o código no válido.';
            return false;
        }


        if ($file->llave_copia != $key) {
            $this->message = 'Folio y/o código no válido.';
            return false;
        }

        if ($file->validez !== null) {
            if ($file->validez_habiles) {
                $fecha_expiracion = strtotime(add_working_days($file->created_at, $file->validez));
            } else {
                $fecha_expiracion = strtotime($file->created_at . ' + ' . $file->validez . ' days');
            }


            if (now() > $fecha_expiracion && $file->validez > 0) {
                $this->message = 'Documento expiró su periodo de validez.';
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
