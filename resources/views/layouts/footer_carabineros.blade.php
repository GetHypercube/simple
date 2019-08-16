<footer class="footer mt-auto py-3" style="background-color: #007328;" >
    <div class="container">
        <div class="row">
            <div class="col-8">
                <a href="http://www.carabineros.cl/" target="_blank">
                    Comisaría Virtual - Carabineros de Chile
                </a>
                <br>
                <a href="http://digital.gob.cl/" target="_blank">
                    Powered by División de Gobierno Digital
                </a>
                <br>
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