# SIMPLE 2.0

El siguiente ejemplo esta enfocado para el el trabajo en un ambiente de desarrollo y levanta la aplicación sobre un 
conjunto de contenedores los cuales hacen referencia a cada uno de los diferentes servicios que necesita la aplicación 
SIMPLE.

* Sitio web - Laravel 5.5
* MySql 5.7
* Elastic Search 5.6
* Redis
* Rabbit
 

## Instalación
Como requerimiento excluyente se debe contar con docker instalado en tu equipo. En el siguiente 
[link](https://docs.docker.com/install/linux/docker-ce/ubuntu/) podrás encontrar un ejemplo de instalación para el 
sistema operativo Ubuntu, para otras distribuciones consultar la documentación oficial y seguir las 
instrucciones:


### (Consideración)
Para levantar el ambiente de desarrollo las variables o comandos a considerar son los definidos dentro del
directorio `setup/`

### Variables de entorno

El siguiente paso es, dentro del directorio `setup/` crear un archivo llamado `.env` y copiar el contenido del archivo
`env.example` dentro de el, luego ahí puedes editar las variables de configuración de  acuerdo a tu necesidad, algunas 
variables ya vienen predefinidas dentro del archivo docker-compose.yml, tales como las variables de host o ip referentes
a los demás servicios, como elasticsearch, base de datos, puertos, etc.

```
cd setup/

cp env.example .env
```

Descripción de variables de entorno a utilizar

```
APP_NAME: Nombre de la aplicación.
APP_ENV: Entorno de ejecución.
APP_KEY: llave de la aplicacion, se auto genera con php artisan key:generate.
APP_DEBUG: true o false.
APP_LOG_LEVEL: Nivel de log (EMERGENCY, ALERT, CRITICAL, ERROR, WARNING, NOTICE, INFO, DEBUG).
APP_URL: URL de tu aplicación incluir http.
APP_MAIN_DOMAIN: Dominio de tu aplicación, incluir http.
ANALYTICS: Código de Seguimiento de google analytics

DB_CONNECTION: Tipo de conexión de tu Base de datos, para este proyecto por defecto se usa mysql.
DB_HOST: host donde se aloja tu Base de Datos.
DB_PORT: puerto por donde se esta disponiendo tu Base De Datos en el Host.
DB_DATABASE: Nombre Base de datos (Debe estar previamente creada).
DB_USERNAME: Usuario Base de datos.
DB_PASSWORD: Contraseña Base de datos.

MAIL_DRIVER: soporta ("smtp", "sendmail", "mailgun", "mandrill", "ses", "sparkpost", "log", "array").
MAIL_HOST: Aquí puede proporcionar la dirección de host del servidor.
MAIL_PORT: Este es el puerto utilizado por su aplicación para entregar correos electrónicos a los usuarios de la aplicación.
MAIL_ENCRYPTION: Aquí puede especificar el protocolo de cifrado que se debe usar cuando la aplicación envía mensajes de correo electrónico.
MAIL_USERNAME: Si su servidor requiere un nombre de usuario para la autenticación, debe configurarlo aquí.
MAIL_PASSWORD: Si su servidor requiere una contraseña para la autenticación, debe configurarlo aquí.

ROLLBAR_TOKEN: Token de acceso proporcionado por Rollbar.

RECAPTCHA_SECRET_KEY: reCaptcha secret key, proporcionado por Google.
RECAPTCHA_SITE_KEY: reCaptcha site key, proporcionado por Google.

BASE_SERVICE: URL del microservicio de agendas.
CONTEXT_SERVICE: Contexto de aplicación del servicio de agendas. 
AGENDA_APP_KEY: Identificado de aplicación o cuenta para acceder al microservicio de agendas.
RECORDS: Cantidad de registros que se mostrarán por pagina.
TIEMPO_CONFIRMACION_CITA: Minutos para eliminar una cita si no ha sido confirmada.

JS_DIAGRAM: Libreria que se va a utilizar para hacer los diagramas de flujo, default: jsplumb (Gratuita y libre uso).

MAP_KEY: Key de acceso a Google Maps.

SCOUT_DRIVER: driver para agregar búsquedas de texto completo a sus modelos Eloquent.
ELASTICSEARCH_INDEX: Nombre lógico que interpretara elasticsearch como índice.
ELASTICSEARCH_HOST: Aquí puede proporcionar la dirección de host de elasticsearch.

AWS_S3_MAX_SINGLE_PART: Al superar este límite en bytes, los archivos se subirán a Amazon S3 usando multipartes.

DOWNLOADS_FILE_MAX_SIZE: Al momento de descargar trámites que no posean archivos subidos a Amazon S3, se compara el total a descargar con esta variable en Mega bytes, si es mayor que la variable, se usará un JOB para empaquetar y luego enviar el enlace de descarga por correo electrónico a la dirección registrada para ese nombre de usuario. Si es menor que esta variable, se descargará de forma directa sin un Job. Si no se especifica usa por omisión 500 MB.
DOWNLOADS_MAX_JOBS_PER_USER: Cantidad máxima de JOBS de archivos a descargar simultáneos permitidos por cada usuario.
DESTINATARIOS_CRON: Listado de correos separados por comas que serán destinatarios de recibir el estado de las tarea de cron
```


## Docker-compose

** Antes de instalar asegúrese de que los siguientes puertos se encuntran disponibles en su máquina:
* 8000 -> Sitio web
* 9200 -> Elasticsearch
* 3306 -> MySql
* 6379 -> Redis
* 5672 -> RabbitMq
* 15672 -> Manager de RabbitMq

Si no puedes disponibilizarlos, debes modificar los puertos en el archivo `.env`

Recuerda estar dentro del directorio `setup/`
```bash
$ cd setup/
```

Simplemente ejecutamos el bash `install.sh`
```
$ bash install.sh
```

Luego comenzaran a levantar la aplicación tomando como base el Dockerfile definido
dentro del directorio `setup/`

Y continuará descargando y levantando los diferentes servicios, elasticsearch, MySql, redis y rabbit

Esto tomará algunos minutos, ya que tendrá que descargar las diferentes imágenes de cada servicio 
(en el caso de que no las tengas instaladas). Cuando la instalación termine pudes ejecutar:
```bash
$ docker ps
```

Y se listaran los siguientes contenedores

```bash
- simple2_web
- Simple2_db
- simple2_elastic
- simple2_redis
- simple2_rabbit
```

Cada uno mapeado a sus respectivos puertos desde 127.0.0.1 (localhost) hacia cada contenedor.

Para acceder a un contenedor puedes ejecutar el siguiente comando:
```bash
$ docker exec -it <nombre_contenedot> bash

$ docker exec -it simple2_web bash
```

```bash
$ docker exec -it simple2_db bash

Y luego ya puedes entrar con: 

mysql -u root -p
```