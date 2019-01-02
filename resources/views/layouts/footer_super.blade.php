<footer class="blog-footer footerntsp">
  <div class="row rowfooter justify-content-center">
    <div class="col-10">
      <div class="row">
        <div class="col-4">
          <ul>
            <li>
              <span class="float-left splogoinstfooter">
                <a class="logoinstfooter" href="#">
                  <img class="align-self-center mr-3 logo"
                       src="{{Cuenta::cuentaSegunDominio() != 'localhost' ? Cuenta::cuentaSegunDominio()->logofADesplegar : asset('assets/img/logo.png') }}"
                       alt="{{Cuenta::cuentaSegunDominio() != 'localhost' ? Cuenta::cuentaSegunDominio()->nombre_largo : env('APP_NAME') }}"/>
                </a>
              </span>
              <span class="float-left splogosuperfooter">
                <a class="logosuperfooter" href="#"><img src="{{ asset('img/logo_super.svg') }}"></a>
              </span>
            </li>
            <li><a href="http://www.bienesnacionales.cl/">MINISTERIO DE BIENES NACIONALES</a></li>
            <!--<li><a href="http://www.minsegpres.gob.cl/">Otros trámites de []</a></li>
            <li><a href="#">Políticas de privacidad</a></li>
            <li><a href="#">Término de uso</a></li>-->
          </ul>
        </div>
        <div class="col-8 txtfooter">
          <span class="spntxtfooter">
            <span>Super es una marca registrada por: </span><a href="#">Ministerio de Economía, Fomento y Turismo(MINECON)</a>
          </span>
        </div>
      </div>
    </div>
  </div>
</footer>