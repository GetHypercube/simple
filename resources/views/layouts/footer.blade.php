<footer class="footer mt-auto py-3" >
    <div class="container">
        <div class="row">
            <div class="col-8">
                <a href="http://digital.gob.cl/" target="_blank">
                    Iniciativa de la División de Gobierno Digital
                </a>
                <br>
                <a href="http://www.minsegpres.gob.cl/" target="_blank">
                    Ministerio Secretaría General de la Presidencia
                </a>
                <br>
                <a href="">Powered by SIMPLE</a>
            </div>
            <div class="col-4 mt-4 text-right">
                @if (isset($metadata->contacto_email))
                    <span>Email de soporte: {{ $metadata->contacto_email }}</span><br>
                    <a href="{{ $metadata->contacto_link }}" target="_blank">
                        Sitio de soporte
                    </a>
                @endif
            </div>
        </div>

        <div class="bicolor">
            <span class="azul"></span>
            <span class="rojo"></span>
        </div>
    </div>
</footer>