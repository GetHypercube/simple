<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:usuario_backend');
    }

    public function index()
    {
        return (new ManagementController)->index();
    }

}
