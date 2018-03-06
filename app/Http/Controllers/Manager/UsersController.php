<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Doctrine;
use UsuarioBackend;

class UsersController extends Controller
{

    public function index()
    {
        $data['usuarios'] = Doctrine::getTable('UsuarioBackend')->findAll();

        $data['title'] = 'Usuarios Backend';
        $data['content'] = view('manager.users.index', $data);

        return view('layouts.manager.app', $data);
    }

    public function edit($usuario_id = null)
    {
        if ($usuario_id)
            $usuario = Doctrine::getTable('UsuarioBackend')->find($usuario_id);
        else
            $usuario = new UsuarioBackend();

        $data['usuario'] = $usuario;
        $data['cuentas'] = Doctrine::getTable('Cuenta')->findAll();

        $data['title'] = $usuario->id ? 'Editar' : 'Crear';
        $data['content'] = view('manager.users.edit', $data);

        return view('layouts.manager.app', $data);
    }

    public function edit_form(Request $request, $usuario_id = null)
    {
        if ($usuario_id)
            $usuario = Doctrine::getTable('UsuarioBackend')->find($usuario_id);
        else
            $usuario = new UsuarioBackend();

        $validations = [
            'email' => 'required|email',
            'nombre' => 'required',
            'apellidos' => 'required',
            'cuenta_id' => 'required',
            'rol' => 'required',
        ];

        $messages = [
            'email.required' => 'El campo Correo Electrónico es obligatorio',
            'nombre.required' => 'El campo Nombre es obligatorio',
            'apellidos.required' => 'El campo Apellidos es obligatorio',
            'cuenta_id.required' => 'El campo Cuenta es obligatorio',
            'rol.required' => 'El campo Rol es obligatorio',
        ];

        if (!$usuario->id || $request->has('password')) {
            $validations['password'] = 'required|min:6|confirmed';
        }

        $respuesta = new \stdClass();
        $usuario->email = $request->input('email');
        $usuario->nombre = $request->input('nombre');
        $usuario->apellidos = $request->input('apellidos');
        $usuario->Cuenta = Doctrine::getTable('Cuenta')->find($request->input('cuenta_id'));
        $usuario->rol = implode(",", $request->input('rol'));
        if ($request->input('password'))
            $usuario->setPasswordWithSalt($request->input('password'));

        $usuario->save();

        $request->session()->flash('success', 'Usuario guardado con éxito.');
        $respuesta->validacion = true;
        $respuesta->redirect = url('manager/usuarios');

        return response()->json($respuesta);
    }

    public function delete(Request $request, $usuario_id)
    {
        $usuario = Doctrine::getTable('UsuarioBackend')->find($usuario_id);
        $usuario->delete();

        $request->session()->flash('success', 'Usuario eliminado con éxito.');
        redirect('manager/usuarios');
    }

}
