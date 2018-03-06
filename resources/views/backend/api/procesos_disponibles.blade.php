@extends('layouts.backend')

@section('title', 'Trámites disponibles')

@section('content')
    <div class="container-fluid">
        <div class="row mt-3">

            @include('backend.api.nav')

            <div class="span9">
                <h2>Trámites disponibles como servicios</h2>
                <table class="table">
                    <tbody>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection