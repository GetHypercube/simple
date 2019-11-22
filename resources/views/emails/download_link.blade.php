<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>Descargar</title>
</head>
<body>
  <p>

    Hola <?php echo $name_user; ?>, el reporte <b><?php echo $reportname; ?></b> que has solicitado se encuentra listo para que<br> 
    puedas descargarlo.<br><p>

    Para poder acceder a él, <a href="{{ $link }}"> haz click en el siguiente enlace</a><p>
    
    Una vez que hagas la descarga del documento, no podrás<br>
    volver a acceder al mismo link. Si quieres volver a acceder<br>
    al mismo reporte, deberás generarlo nuevamente.<br><p>

    Saludos,<br><p>
    <b><?php echo $nombre_cuenta; ?></b><p>
  </p>
</body>
</html>