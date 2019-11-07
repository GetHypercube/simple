<?php

namespace App\Http\Controllers\Manager;

use App\Helpers\Doctrine;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Anuncio;
use Doctrine_Manager;

class AnuncioController extends Controller
{
    public function index(){
        $data['anuncios'] = Anuncio::get();

        $data['title'] = 'Anuncios';
        $data['content'] = view('manager.anuncios.index', $data);

        return view('layouts.manager.app', $data);
    }

    public function edit($anuncio_id = null){

        if ($anuncio_id) {
            $anuncio = Anuncio::find($anuncio_id);
            $data['anuncios'] = $anuncio;
        } else {
            $anuncio = new Anuncio();
        }
        
        // $data['seo_tags'] = \Cuenta::seo_tags($cuenta->id);
        $data['anuncio'] = $anuncio;
        // $data['calendar'] = $calendar;
        $data['title'] = $anuncio->id ? 'Editar' : 'Crear';
        $data['content'] = view('manager.anuncios.edit', $data);

        return view('layouts.manager.app', $data);
    }

    public function edit_form(Request $request, $anuncio_id = null){
        Doctrine_Manager::connection()->beginTransaction();

        try {

            if ($anuncio_id)
                $anuncio = Anuncio::find($anuncio_id);
            else
                $anuncio = new Anuncio();

            $validations = [
                'texto' => 'required',
                'tipo' => 'required',
             ];

            $messages = [
                'texto.required' => 'El campo Texto es obligatorio',
                'tipo.required' => 'El campo tipo es obligatorio',
            ];
            $request->validate($validations, $messages);

            $respuesta = new \stdClass();
            $anuncio->tipo = $request->input('tipo');
            $anuncio->texto = $request->has('texto') && !is_null($request->input('texto')) ? $request->input('texto') : '';
            $anuncio->save();

            Doctrine_Manager::connection()->commit();

            $request->session()->flash('success', 'Anuncio guardado con éxito.');
            $respuesta->validacion = true;
            $respuesta->redirect = url('manager/anuncios');

        } catch (Exception $ex) {
            $respuesta->validacion = false;
            $respuesta->errores = '<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>' . $ex->getMessage() . '</div>';
            Doctrine_Manager::connection()->rollback();
        }

        return response()->json($respuesta);
    }

    public function delete(Request $request, $anuncio_id){
        $anuncio = Anuncio::find($anuncio_id);
        $anuncio->delete();

        $request->session()->flash('success', 'anuncio eliminado con éxito.');
        return redirect('manager/anuncios');
    }

    

}