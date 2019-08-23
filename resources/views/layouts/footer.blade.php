<footer class="footer mt-auto py-3" >
    <div class="container">
        <div class="row">
            <div class="col-6 mt-1">
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
            <div class="col-6 mt-1 text-right">
                @if ( (isset($metadata->contacto_email) && $metadata->contacto_email!='') &&
                (isset($metadata->contacto_link) && $metadata->contacto_link!=''))
                    <p style="font-size: 14px;line-height: 26px;">
                        Si el sistema presenta problemas comuníquese con nosotros escribiendo al siguiente correo
                        {{ $metadata->contacto_email }}, o bien ingresando en el siguiente
                        <a href="{{ $metadata->contacto_link }}" target="_blank">
                            link
                        </a>
                    </p>
                @elseif (isset($metadata->contacto_email) && $metadata->contacto_email!='')
                    <p style="font-size: 14px;line-height: 26px;">
                        Si el sistema presenta problemas comuníquese con nosotros escribiendo al siguiente correo
                        {{ $metadata->contacto_email }}
                    </p>
                @elseif(isset($metadata->contacto_link) && $metadata->contacto_link!='')
                    <p style="font-size: 14px;line-height: 26px;">
                        Si el sistema presenta problemas comuníquese con nosotros ingresando en el siguiente
                        <a href="{{ $metadata->contacto_link }}">link</a>
                    </p>
                @endif
            </div>
        </div>

        <div class="bicolor">
            <span class="azul"></span>
            <span class="rojo"></span>
        </div>
    </div>
</footer>