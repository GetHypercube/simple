<?php

namespace App\Http\Controllers;

use App\Helpers\Doctrine;
use App\Rules\CheckDocument;
use Illuminate\Http\Request;
use Doctrine_Query;

class ValidatorController extends Controller
{
    public function index()
    {
        return view('validator.document');
    }

    public function documento(Request $request)
    {
        $request->validate([
            'id' => ['required', new CheckDocument($request)],
            'key' => 'required'
        ], [
            'id.required' => 'El campo Folio es obligatorio.',
            'key.required' => 'El campo Código de verificación es obligatorio.'
        ]);

        $file = Doctrine::getTable('File')->find($request->input('id'));
        $path = 'uploads/documentos/' . $file->filename;

        if (!file_exists(public_path($path))) {
            return view('validator.document');
        }

        //$friendlyName = str_replace(' ', '-', str_slug(mb_convert_case($file->Tramite->Proceso->Cuenta->nombre . ' ' . $file->Tramite->Proceso->nombre, MB_CASE_LOWER) . '-' . $file->id)) . '.' . pathinfo($path, PATHINFO_EXTENSION);

        return response()->file($path);
    }

}