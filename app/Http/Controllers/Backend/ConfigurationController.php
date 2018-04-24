<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\Doctrine;
use App\Helpers\FileUploader;
use App\Models\Cuenta;
use App\Models\GrupoUsuarios;
use App\Models\UsuarioBackend;
use App\User;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ConfigurationController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveMyAccount(Request $request)
    {
        if ($request->has('password') && !empty($request->input('password'))) {
            $this->validate($request, ['password' => 'required|confirmed|min:6']);

            UsuarioBackend::whereId(Auth::user()->id)
                ->update(['password' => Hash::make($request->input('password'))]);

            $request->session()->flash('status', 'Contraseña modificada con éxito');
        }


        return redirect()->route('backend.cuentas');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function mySite()
    {
        $data = Auth::user()->Cuenta;

        return view('backend.configuration.my_site.index', compact('data'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveMySite(Request $request)
    {
        $this->validate($request, [
            'name_large' => 'required|max:256'
        ]);

        $data = Cuenta::find(Auth::user()->cuenta_id);
        $data->nombre_largo = $request->input('name_large');
        $data->mensaje = $request->input('message');
        $data->descarga_masiva = $request->has('massive_download') ? 1 : 0;
        $data->logo = $request->input('logo');
        $data->save();

        $request->session()->flash('status', 'Cuenta modificada con éxito');

        return redirect()->route('backend.configuration.my_site');
    }

    /**
     * @param string $plantilla_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function templates($plantilla_id = '')
    {
        $cuenta_id = Auth::user()->cuenta_id;

        if ($plantilla_id != '') {
            if ($plantilla_id != 1) {
                $cuentahasconfig = Doctrine::getTable('CuentaHasConfig')->findOneByIdparAndCuentaId(1, $cuenta_id);
                if ($cuentahasconfig == FALSE) {
                    $ctahascfg = new \CuentaHasConfig();
                    $ctahascfg->idpar = 1;
                    $ctahascfg->config_id = $plantilla_id;
                    $ctahascfg->cuenta_id = $cuenta_id;
                    $ctahascfg->save();
                } else {
                    $cuentahasconfig->config_id = $plantilla_id;
                    $cuentahasconfig->cuenta_id = $cuenta_id;
                    $cuentahasconfig->save();
                }
            } else {
                $cuentahasconfig = Doctrine::getTable('CuentaHasConfig')->findByIdparAndCuentaId(1, $cuenta_id);
                $cuentahasconfig->delete();
            }

        }
        $data['config_id'] = $plantilla_id;
        $data['config'] = Doctrine::getTable('Config')->findByIdparAndCuentaIdOrCuentaId(1, $cuenta_id, 0);

        return view('backend.configuration.template.index', $data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function storeTemplate(Request $request)
    {
        $this->validate($request, [
            'nombre_visible' => 'required',
            'nombre_plantilla' => 'required',
        ]);

        $existe = Doctrine::getTable('Config')
            ->findOneByIdparAndCuentaIdAndNombre(1, Auth::user()->cuenta_id, $request->input('nombre_plantilla'));
        if (!$existe) {
            $plantilla = new \Config();
            $plantilla->idpar = 1;
            $plantilla->cuenta_id = Auth::user()->cuenta_id;
            $plantilla->endpoint = 'plantilla';
            $plantilla->nombre_visible = $request->input('nombre_visible');
            $plantilla->nombre = $request->input('nombre_plantilla');

            $plantilla->save();
        } else {
            $existe->nombre_visible = $request->input('nombre_visible');
            $existe->nombre = $request->input('nombre_plantilla');
            $existe->save();
        }

        return redirect()->route('backend.configuration.template');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addTemplates()
    {
        $data['form'] = Auth::user()->Cuenta;
        $data['edit'] = true;

        return view('backend.configuration.template.edit', $data);
    }

    /**
     * @param $plantilla_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteTemplate($plantilla_id)
    {
        $cuenta_id = Auth::user()->cuenta_id;

        //busco plantilla por defecto
        $config = Doctrine::getTable('Config')->findOneByIdparAndNombre(1, 'default');

        //Busco Id de Plantilla a eliminar, almaceno valores a eliminar
        $cuentahasconfig = Doctrine::getTable('CuentaHasConfig')
            ->findOneByIdparAndConfigIdAndCuentaId(1, $plantilla_id, $cuenta_id);

        if (!$cuentahasconfig === FALSE && $config !== False) {
            $id_default = $config->id;
            $idpar_default = $config->idpar;

            $cuentahasconfig->idpar = $idpar_default;
            $cuentahasconfig->config_id = $id_default;
            $cuentahasconfig->cuenta_id = Auth::user()->cuenta_id;
            $cuentahasconfig->save();
        }

        $config = Doctrine::getTable('Config')->findOneByIdAndIdpar($plantilla_id, 1);
        $nombre_eliminar = $config->nombre;
        $config->delete();

        $source = 'uploads/themes/' . $cuenta_id . '/' . $nombre_eliminar . '/';
        $filedestino = 'application/views/themes/' . $cuenta_id . '/' . $nombre_eliminar . '/';
        File::deleteDirectory($source);
        File::deleteDirectory($filedestino);

        $cuenta_id = Auth::user()->cuenta_id;
        $data['config'] = Doctrine::getTable('Config')
            ->findByIdparAndCuentaIdOrCuentaId(1, $cuenta_id, 0);
        $cuentahasconfig = Doctrine::getTable('CuentaHasConfig')
            ->findOneByIdparAndCuentaId(1, Auth::user()->cuenta_id);
        $data['config_id'] = 1;

        if ($cuentahasconfig) {
            $data['config_id'] = $cuentahasconfig->config_id;
        }

        return redirect()->route('backend.configuration.template');
    }

    /**
     * @param string $conector_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function modeler($conector_id = '')
    {
        if (!$conector_id == '') {
            $cuenta_id = Auth::user()->cuenta_id;
            $cuentahasconfig = Doctrine::getTable('CuentaHasConfig')->findOneByIdparAndCuentaId(2, $cuenta_id);
            if ($cuentahasconfig == FALSE) {
                $ctahascfg = new \CuentaHasConfig();
                $ctahascfg->idpar = 2;
                $ctahascfg->config_id = $conector_id;
                $ctahascfg->cuenta_id = $cuenta_id;
                $ctahascfg->save();

            } else {
                $cuentahasconfig->config_id = $conector_id;
                $cuentahasconfig->cuenta_id = Auth::user()->cuenta_id;
                $cuentahasconfig->save();
            }

            $data['config_id'] = $conector_id;
            $data['config'] = Doctrine::getTable('Config')->findByIdpar(2);
        } else {

            $data['config'] = Doctrine::getTable('Config')->findByIdpar(2);
            $cuentahasconfig = Doctrine::getTable('CuentaHasConfig')->findOneByIdparAndCuentaId(2, Auth::user()->cuenta_id);

            $data['config_id'] = 2;
            if ($cuentahasconfig) {
                $data['config_id'] = $cuentahasconfig->config_id;
            }

        }

        return view('backend.configuration.modeler.index', $data);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function backendUsers()
    {
        $users = UsuarioBackend::all();

        return view('backend.configuration.backend_users.index', compact('users'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addBackendUsers()
    {
        $data['form'] = new UsuarioBackend();
        $data['edit'] = false;

        return view('backend.configuration.backend_users.edit', $data);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editBackendUsers($id)
    {
        $data['form'] = UsuarioBackend::find($id);
        $data['edit'] = true;

        return view('backend.configuration.backend_users.edit', $data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeBackendUsers(Request $request)
    {
        $this->saveBackendUsers($request, new UsuarioBackend());

        $request->session()->flash('status', 'Usuario creado con éxito');

        return redirect()->route('backend.configuration.backend_users');
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateBackendUsers(Request $request, $id)
    {
        $this->saveBackendUsers($request, UsuarioBackend::find($id), true);

        $request->session()->flash('status', 'Usuario editado con éxito');

        return redirect()->route('backend.configuration.backend_users');
    }

    /**
     * @param Request $request
     * @param UsuarioBackend $user
     * @return UsuarioBackend
     */
    public function saveBackendUsers(Request $request, UsuarioBackend $user, $edit = false)
    {
        $this->validate($request, [
            'nombre' => 'required|max:128',
            'apellidos' => 'required|max:128',
            'rol' => 'required'
        ]);

        if ($request->has('password') && !empty($request->input('password'))) {
            $this->validate($request, ['password' => 'required|confirmed|min:6']);

            $user->password = Hash::make($request->input('password'));
        }

        if (!$edit) {
            $this->validate($request, ['email' => 'required|email']);

            $user->email = $request->input('email');
        }

        $user->nombre = $request->input('nombre');
        $user->apellidos = $request->input('apellidos');
        $user->rol = $request->input('rol');
        $user->cuenta_id = Auth::user()->cuenta_id;
        $user->save();

        return $user;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function frontendUsers()
    {
        $users = User::whereCuentaId(Auth::user()->cuenta_id)->get();

        return view('backend.configuration.frontend_users.index', compact('users'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addFrontendUsers()
    {
        $data['form'] = new User();
        $data['grupos'] = GrupoUsuarios::where('id', Auth::user()->cuenta_id)->get();
        $data['edit'] = false;

        return view('backend.configuration.frontend_users.edit', $data);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editFrontendUsers($id)
    {
        $data['form'] = User::find($id);
        $data['grupos'] = GrupoUsuarios::whereCuentaId(Auth::user()->cuenta_id)->get();
        $data['grupos_selected'] = $data['form']->grupo_usuarios()->get()->groupBy('id')->keys()->toArray();
        $data['edit'] = true;

        return view('backend.configuration.frontend_users.edit', $data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeFrontendUsers(Request $request)
    {
        $this->saveFrontendUsers($request, new User());

        $request->session()->flash('status', 'Usuario creado con éxito');

        return redirect()->route('backend.configuration.frontend_users');
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateFrontendUsers(Request $request, $id)
    {
        $this->saveFrontendUsers($request, User::find($id), true);

        $request->session()->flash('status', 'Usuario editado con éxito');

        return redirect()->route('backend.configuration.frontend_users');
    }

    /**
     * @param Request $request
     * @param User $user
     * @param bool $edit
     * @return User
     */
    public function saveFrontendUsers(Request $request, User $user, $edit = false)
    {
        $this->validate($request, [
            'nombres' => 'required',
            'email' => 'required'
        ]);

        if ($request->has('password') && !empty($request->input('password'))) {
            $this->validate($request, ['password' => 'required|confirmed|min:6']);

            $user->password = Hash::make($request->input('password'));
        }

        if (!$edit) {
            $this->validate($request, ['usuario' => 'required|unique:usuario']);

            $user->usuario = $request->input('usuario');
        }

        $user->nombres = $request->input('nombres');
        $user->apellido_paterno = $request->input('apellido_paterno');
        $user->apellido_materno = $request->input('apellido_materno');
        $user->vacaciones = $request->has('vacaciones') ? 1 : 0;
        $user->email = $request->input('email');
        $user->cuenta_id = Auth::user()->cuenta_id;
        $user->salt = '';
        $user->save();

        //Eliminamos todas las relaciones que tenga este usuario con grupos
        $user->grupo_usuarios()->detach();

        //Insertamos las nuevas relaciones
        if ($request->has('grupos_usuarios')) {
            foreach ($request->input('grupos_usuarios') as $id) {
                $user->grupo_usuarios()->attach($id);
            }
        }

        return $user;
    }


    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteFrontendUsers(Request $request, $id)
    {
        User::destroy($id);

        $request->session()->flash('status', 'Usuario eliminado con éxito');

        return redirect()->route('backend.configuration.frontend_users');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function groupUsers()
    {
        $group_users = GrupoUsuarios::whereCuentaId(Auth::user()->cuenta_id)->orderBy('id', 'asc')->get();

        return view('backend.configuration.group_users.index', compact('group_users'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addGroupUsers()
    {
        $data['form'] = new GrupoUsuarios();
        $data['usuarios'] = User::whereCuentaId(Auth::user()->cuenta_id)->whereNotNull('email')->get();
        $data['edit'] = false;

        return view('backend.configuration.group_users.edit', $data);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editGroupUsers($id)
    {
        $data['form'] = GrupoUsuarios::find($id);
        $data['usuarios'] = User::whereCuentaId(Auth::user()->cuenta_id)->whereNotNull('email')->get();
        $data['usuarios_selected'] = $data['form']->users()->get()->groupBy('id')->keys()->toArray();
        $data['edit'] = true;

        return view('backend.configuration.group_users.edit', $data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeGroupUsers(Request $request)
    {
        $this->saveGroupUsers($request, new GrupoUsuarios());

        $request->session()->flash('status', 'Grupo de Usuarios creado con éxito');

        return redirect()->route('backend.configuration.group_users');
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateGroupUsers(Request $request, $id)
    {
        $this->saveGroupUsers($request, GrupoUsuarios::find($id), true);

        $request->session()->flash('status', 'Grupo de Usuarios editado con éxito');

        return redirect()->route('backend.configuration.group_users');
    }

    /**
     * @param Request $request
     * @param GrupoUsuarios $grupo_usuarios
     * @param bool $edit
     * @return GrupoUsuarios
     */
    public function saveGroupUsers(Request $request, GrupoUsuarios $grupo_usuarios, $edit = false)
    {
        $this->validate($request, [
            'nombre' => 'required'
        ]);

        $grupo_usuarios->nombre = $request->input('nombre');
        $grupo_usuarios->cuenta_id = Auth::user()->cuenta_id;
        $grupo_usuarios->save();

        //Eliminamos todas las relaciones con usuarios que tenga este grupo
        $grupo_usuarios->users()->detach();

        //Insertamos las nuevas relaciones
        if ($request->has('usuarios')) {
            foreach ($request->input('usuarios') as $id_user) {
                $grupo_usuarios->users()->attach($id_user);
            }
        }


        return $grupo_usuarios;
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteGroupUsers(Request $request, $id)
    {
        GrupoUsuarios::destroy($id);

        $request->session()->flash('status', 'Grupo Usuario eliminado con éxito');

        return redirect()->route('backend.configuration.group_users');
    }

    /**
     * @param Request $request
     * @return array
     */
    public function mySiteUploadLogo(Request $request)
    {
        $allowedExtensions = ['jpg', 'png'];
        $pathLogos = public_path('logos/');
        $response = (new FileUploader($allowedExtensions))->handleUpload($pathLogos);

        return $response;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function mySiteUploadTheme(Request $request)
    {
        $cuenta = Auth::user()->cuenta_id;
        $ruta_uploads = public_path('themes/' . $cuenta . '/');
        $ruta_views = resource_path('views/themes/' . $cuenta . '/');

        is_dir(public_path('themes/')) ? TRUE : mkdir(public_path('themes/'));
        is_dir(resource_path('views/themes/')) ? TRUE : mkdir(resource_path('views/themes/'));
        is_dir($ruta_uploads) ? TRUE : mkdir($ruta_uploads);
        is_dir($ruta_views) ? TRUE : mkdir($ruta_views);

        $allowedExtensions = ['zip'];
        $sizeLimit = 20 * 1024 * 1024;

        $result = (new FileUploader($allowedExtensions, $sizeLimit))->handleUpload($ruta_uploads, true);

        if (isset($result['success'])) {
            $archivo = $result['full_path'];
            $partes_ruta = pathinfo($archivo);
            $directorio = $partes_ruta['dirname'];
            $filename = $partes_ruta['filename'];

            if ($filename == 'default') {
                $filename = 'default' . $cuenta;
            }

            $source = $ruta_uploads . $filename . '/';
            $zip = new \ZipArchive;

            if ($zip->open($archivo) === TRUE) {
                $zip->extractTo($source);
                $zip->close();
                unlink($archivo);
            }

            $fileorigen = $source . 'template.php';
            $filedestino = $ruta_views . $filename . '/template.php';
            if (file_exists($fileorigen)) {
                if (file_exists($filedestino)) {
                    unlink($filedestino);
                } else if (!is_dir(dirname($filedestino))) {
                    mkdir(dirname($filedestino));
                }

                rename($fileorigen, $filedestino);
            }

            $result['full_path'] = $source . 'preview.png';
            $result['file_name'] = 'preview.png';
            $result['folder'] = $filename;
        }

        return $result;
    }
}
