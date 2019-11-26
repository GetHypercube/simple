<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>Descargar</title>
</head>
<body>
  <p>
   <img src="{{ asset('img/reportes/presidential_bar.svg') }}">
   <h1 id="title">¡Tú reporte está listo! </h1>      
    <text id="name_person">Hola</text> <b><?php echo $user_name; ?></b>, el reporte <b><?php echo $reportname; ?></b> que has solicitado se encuentra listo para que<br> 
    puedas descargarlo.<br><p></p>

    Para poder acceder a él, haz click en el siguiente enlace:<p>

    <a href="{{ $link }}"><?php echo $link; ?> </a><p>
    
    Una vez que hagas la descarga del documento, no podrás<br>
    volver a acceder al mismo link. Si quieres volver a acceder<br>
    al mismo reporte, deberás generarlo nuevamente.<br><p>

    Saludos,<br><p>
    <b><?php echo $nombre_cuenta; ?></b><p>
  </p>
</body>
</html>
<style>
  .title
  {	
    height: 34px;	
    width: 397px;	
    color: #373737;	
    font-family: "Roboto Slab";	
    font-size: 25px;	
    font-weight: bold;	
    line-height: 31px;
  }
  .name_person
  {	
    height: 72px;	
    width: 427px;	
    color: #373737;	
    font-family: Roboto;	
    font-size: 16px;	
    line-height: 24px;
  }
</style>