<div class="anuncios {{(new \App\Helpers\Utils())->get_anuncio_activo()->tipo}}">
    <p class="text-center" >
        <img src="{{ asset('img/anuncios/alert-warning.svg') }}">
        {{(new \App\Helpers\Utils())->get_anuncio_activo()->texto}}
    </p>
</div>