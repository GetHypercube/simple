# SIMPLE 2.0

## Requerimientos

* Servidor Web con PHP 7.1
* Librerías PHP necesarias:
    * OpenSSL
    * PDO
    * Mbstring
    * Tokenizer
    * curl
    * mcrypt
    * Ctype
    * XML
    * JSON
    * GD
    * SOAP

## Instalación

### Permisos de directorio

Es posible que deba configurar algunos permisos. Los directorios dentro de `storage` y `bootstrap/cache` deben ser editables por su servidor web o Laravel no se ejecutará.

### Variables de entorno

El siguiente paso es copiar el archivo .env.example a .env y editar las variables de configuración de acuerdo a tu servidor:

```
cp .env.example .envw
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

```

### Instalar las dependencias con composer

Laravel utiliza `Composer` para administrar sus dependencias. Entonces, antes de usar este proyecto desarrollado en Laravel, 
asegúrese de tener Composer instalado en su máquina. Y ejecute el siguiente comando.
 
```
composer install
```

Luego, Generar la llave de aplicación

```
php artisan key:generate
```

Luego, la instalación de las librerías JS necesarias:

```
npm install
```

Compilación de JS

```
npm run prod
```

Luego, Migración y Semillas de la base de datos:

```
php artisan migrate --seed
```

## Actualizaciones

Cada vez que se realice un pull del proyecto, este deberá ser acompañado de la siguiente lista de ejecución de comandos.

```
npm install
npm run production
composer install
php artisan migrate --force
vendor/bin/phpunit
```

## Elasticsearch

Para crear el índice:

```
php artisan elasticsearch:admin create
```

Para indexar todo (Realizar esto en instalación inicial):

```
php artisan elasticsearch:admin index
```

Para indexar solo páginas:

```
php artisan elasticsearch:admin index pages
```

## Creación de usuarios en Backend y Manager

Para crear un usuario perteneciente al Backend, basta con ejecutar este comando especificando email y contraseña:

```
php artisan simple:backend {email} {password}
php artisan simple:backend mail@example.com 123456
```

Y para crear un usuario perteneciente al Manager,

```
php artisan simple:manager {user} {password}
php artisan simple:manager siturra qwerty
```

## Tests con PHPUnit

Listado de Tests:

- Verificar que las librerías de PHP requeridas por SIMPLE, estan habilitadas (VerifyLibrariesAvailableTest)
- Validación de Reglas Customizadas (CustomValidationRulesTest)
- Creación de Usuarios (Front, Backend, Manager) (CreateUsersTest)
- Motor de Reglas SIMPLE BPM (RulesTest)

Para ejecutar los Tests solo debes ejecutar el siguiente comando:

```
vendor/bin/phpunit
```

## Adicionales, 

Si desea poder utilizar una acción de tipo Soap, debe tener habilitada la librería Soap en su php.ini