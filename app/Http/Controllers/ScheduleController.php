<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct()
    {
        #if user not logged, create new user and auto login this new user.
        //$this->middleware('auth_user');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        \Artisan::call('simple:avanzar');
        \Artisan::call('simple:sendmails');
        \Artisan::call('simple:limpieza');
    }
}