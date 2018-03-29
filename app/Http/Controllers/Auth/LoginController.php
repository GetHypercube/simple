<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\Captcha;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('guest')->except('logout');
    }


    /**
     * @return mixed
     */
    public function redirectToProvider(Request $request)
    {
        if ($request->has('redirect')) {
            $request->session()->put('claveunica_redirect', $request->input('redirect'));
        }
        //return Socialite::driver('claveunica')->scopes(['email', 'phone'])->redirect();
        //return Socialite::driver('claveunica')->scopes(['email'])->redirect();
        return Socialite::driver('claveunica')->redirect();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function handleProviderCallback(Request $request)
    {
        if ($request->has('error')) {
            return redirect(route('home'));
        }

        $user = Socialite::driver('claveunica')->user();
        $authUser = User::where('usuario', $user->run)->first();

        //Si no existe el usuario, se intenta crear,
        if (!$authUser) {
            $authUser = new User();
        }

        $authUser->rut = $user->run . '-' . $user->dv;
        //$authUser->dv = $user->dv;
        $authUser->nombres = $user->first_name;
        $authUser->apellido_paterno = $user->last_name;
        $authUser->usuario = $user->run;
        $authUser->email = is_null($user->email) ? '' : $user->email;
        $authUser->registrado = 1;
        $authUser->open_id = 1;
        $authUser->salt = '';
        //$authUser->phone = $user->phone;
        //$authUser->access_token = $user->token;
        //$authUser->refresh_token = $user->refreshToken;
        $authUser->save();

        Auth::login($authUser, true);

        // verificamos si existe un redirect en la session
        if ($request->session()->has('claveunica_redirect')) {

            // almacenamos en una variable auxiliar el redirect, para luego eliminarlo y realizar el redirect.
            $redirect = $request->session()->get('claveunica_redirect');
            $request->session()->forget('claveunica_redirect');

            return redirect($redirect);

        }

        return redirect('/');
    }

    /**
     * @param Request $request
     */
    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            $this->username() => 'required|string',
            'password' => 'required|string',
            'g-recaptcha-response' => ['required', new Captcha]
        ]);
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect('/');
    }
}
