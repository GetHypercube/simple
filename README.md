# Simple 2.0

## Requerimientos

* Servidor Web con PHP 7.1
* Librerias PHP necesarias:
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
    * soap

## Instalación

### Permisos de directorio

Es posible que deba configurar algunos permisos. Los directorios dentro de `storage` y `bootstrap/cache` deben ser editables por su servidor web o Laravel no se ejecutará.

### Variables de entorno

El siguiente paso es copiar el archivo .env.example a .env y editar las variables de configuración de acuerdo a tu servidor:

```
cp .env.example .env
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

Luego, la instalación de las librerias JS necesarias:

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

Para crear el indice:

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

## Test Unitarios con PHPUnit

Listado de Test Unitarios:

- Verificar que las librerías de PHP requeridas por SIMPLE, estan habilitadas (VerifyLibrariesAvailableTest)
- Validación de Reglas Customizadas (CustomValidationRulesTest)
- Creación de Usuarios (Front, Backend, Manager) (CreateUsersTest)
- Motor de Reglas Simple BPM (RulesTest)

Para ejecutar los Test Unitarios solo debes ejecutar el siguiente comando:

```
vendor/bin/phpunit
```

## Adicionales, 

Si desea poder utilizar una acción de tipo Soap, debe tener habilitada la librería Soap en su php.ini