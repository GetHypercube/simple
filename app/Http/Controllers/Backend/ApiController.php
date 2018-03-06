<?php

namespace App\Http\Controllers\Backend;

use App\Models\Cuenta;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ApiController extends Controller
{
    public function updateToken(Request $request)
    {
        $this->validate($request, [
            'api_token' => 'required|max:32'
        ]);

        Cuenta::whereId(Auth::user()->cuenta_id)->update(['api_token' => $request->input('api_token')]);

        $request->session()->flash('status', 'API Token editado con éxito');

        return redirect()->route('backend.api');
    }

}
