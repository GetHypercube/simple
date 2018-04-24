<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Backend API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Api
Route::namespace('Backend')->group(function () {
    //Tramites
    Route::get('/tramites/{tramite_id?}', 'ApiController@tramites');
    //Procesos
    Route::get('/procesos/{proceso_id?}/{recurso?}', 'ApiController@procesos');
});