<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>Descargar</title>
</head>
<body>
<div class="container-fluid"  id="container" > 
<div class="row">
    <div class="col">
       <div id="svg" style="text-align:center;">
          <svg width="620px" height="575px" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
            <rect width="620" height="575" style="fill:rgb(0,0,0,0);stroke-width:;stroke:rgb(0,0,0); margin: 0 auto;padding-top: 50px;" />
      <image xlink:href="{{ asset('img/reportes/presidential_bar.svg') }}" x=20 height="5px" width="118px"/>
      <text x=20 y=90 stroke="#373737" font-family="Roboto Slab" font-size="25px" > ¡Tú reporte esta listo!</text>
      <text x=20 y=140 font-family="Roboto, Sans Serif" font-size="16px">Hola, el reporte <?php echo $reportname; ?> que has solicitado se encuentra listo</text>
      <text x=20 y=160 font-family="Roboto, Sans Serif" font-size="16px">para que puedas descargarlo.</text>
      <text x=20 y=220 font-family="Roboto, Sans Serif" font-size="16px">Para poder acceder a él, haz click en el siguiente enlace:</text>
      <text x=20 y=250 xlink:href="{{ $link }}" font-family="Roboto Slab" font-size="16px"><a font-family="Roboto, Sans Serif" font-size="16px" fill="#0F69B4" href="{{ $link }}">Descargar</a></text>
      <text x=20 y=290 font-family="Roboto, Sans Serif" font-size="16px"> Una vez que hagas la descarga del documento, no podrás </text>
      <text x=20 y=310 font-family="Roboto, Sans Serif" font-size="16px">volver a acceder al mismo link. Si quieres volver a acceder</text> 
      <text x=20 y=330 font-family="Roboto, Sans Serif" font-size="16px">al mismo reporte, deberás generarlo nuevamente.</text>
      <text x=20 y=370 font-family="Roboto, Sans Serif" font-size="16px">Saludos,</text>
      <text x=20 y=410 font-family="Roboto, Sans Serif" font-size="16px" font-weight="bold"><?php echo $nombre_cuenta; ?></text>
           </svg>
       </div>
     </div>
   </div>
</div>
</body>
</html>
<style>

.container-fluid {
  background-color: #EEEEEE;
  padding-top: 50px;

}
svg {
  background-color: white;
}
</style>
