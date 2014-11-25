Aqui tes o FOL
==============
(o resto da gaita xa é cousa túa)

[![Build Status](https://travis-ci.org/oscarotero/fol.png?branch=master)](https://travis-ci.org/oscarotero/fol)

FOL é un (micro)framework escrito en PHP por Oscar Otero (http://oscarotero.com) como exercicio de deseño e como ferramenta para experimentar.
Como é algo persoal que non pretende ter moita repercusión (hai miles de frameworks en PHP), escribo a documentación en galego por comodidade e por se alguen máis daquí lle interesa o proxecto. De todos xeitos, os comentarios dentro do código están en inglés.

Requerimentos:

* PHP 5.5.
* Composer


Instalación
===========

Para instalalo precisas ter composer. Despois simplemente executa create-project do seguinte xeito:

```
$ composer create-project fol/fol o-meu-proxecto
```

Á hora de instalalo pediráseche configurar estas constantes básicas:

* ENVIRONMENT: O nome do entorno de desenvolvemento. Pode se calquera nome. Por defecto é "development".
* BASE_URL: A url onde está aloxado o sitio web (ruta http do navegador). Serve para xerar as urls. Por defecto é "http://localhost" pero se a instalación se fixo nun subdirectorio ou noutro host, debes modificalo para, por exemplo: http://localhost/o-meu-proxecto
* SECURE_KEY: Clave secreta que podes usar para cifrar datos, etc. Por defecto xa se xenera unha de xeito aleatorio.

En calquera momento podes cambiar manualmente esa configuración no arquivo constants.php

Unha vez feito isto, deberías poder ver algo no navegador (http://localhost/o-meu-proxecto).


Documentación rápida
====================

A parte do directorio "vendor" (usado por composer para gardar aí todos os paquetes e dependencias) hai dúas carpetas:

* app: onde se garda a aplicación por defecto (plantillas, controladores, modelos, tests, etc).
* public: todos os arquivos accesibles publicamente (css, imaxes, js, componentes de bower, etc) ademáis do "front controller" (index.php).


App
---

A aplicación está definida na clase App\App (no arquivo app/App.php) que ademáis ten dispoñibles os seguintes métodos:

* $app->getNamespace(): Devolve o namespace da aplicación (ou sexa "App"). Ademáis podes usalo para que che devolva outros namespaces ou clases relativas. Por exemplo ```$app->getNamespace('Controllers\\Index')``` devolve "App\Controllers\Index".
* $app->getPath(): Devolve o path onde está aloxada a aplicación. Podes usar argumentos para que che devolva rutas de arquivos ou subdirectorios. Por exemplo: ```$app->getPath('arquivos/123', '3.pdf')``` devolve algo parecido a "/var/www/o-meu-proxecto/app/arquivos/123/3.pdf"
* $app->getPublicUrl(): O mesmo que getPath pero para devolver rutas http do directorio público. Útil para acceder a arquivos css, javascript, etc. ```$app->getPublicUrl('assets/css', 'subdirectorio')``` devolvería algo parecido a "http://localhost/o-meu-proxecto/public/assets/css/subdirectorio"

A clase app tamén xestiona os "servizos" usados, ou sexa, clases que podes instanciar en calquera momento e que dependen da túa app. Por exemplo a conexión á base de datos, configuración, xestión de plantillas, etc. Para dar de alta un servizo, tes que usar o método register, co nome do servizo e un callback que devolva o resultado. Exemplo:

```php
//Rexistra a clase para cargar a configuración:
$app->register('config', function () {
	return new \Fol\Config($this->getPath('config'));
});

//Rexistra a clase para a conexión á base de datos
$app->register('db', function () use ($app) {
	$config = $app->config->get('db');

	return new \PDO($config['dns'], $config['username'], $config['password']);
});
```

E para usar os servizos:

```php
//Usa o método "get" para xerar unha nova instancia de cada vez:
$newConnection = $app->get('db');

//Get permite tamén pasarlle argumentos ao noso callback
$app->get('db', $arg1, $arg2);

//Tamén podes chamar directamente pola propiedade
$db = $app->db;

//Chamándoa como propiedade non xeras unha nova instancia, senón que usas sempre a mesma
$app->db->exec("DELETE FROM fruit WHERE colour = 'red'");
```

Outra función de "get" é a de instanciar clases relativas á nosa app aínda que non estean rexistradas como servizos. Por exemplo, imaxinemonos que temos a clase App\Controllers\Index. Podemos instanciala directamente:

```php
$indexController = $app->get('Controllers\\Index');
```

Tamén pode ter definida a función estática "run" que é a que se lanza en /public/index.php e que pon en marcha todo.

Resumindo, a nosa app sería algo asi:

```php
namespace App;

class App extends \Fol\App {

	//Lanza a nosa aplicación
	public static function run ()
    {
        //Podemos configurar aqui tamén como queremos rexistrar os erros
        Errors::register();
        Errors::displayErrors();
        Errors::setPhpLogFile(BASE_PATH.'/logs/php.log');

        //Executamos a aplicación e lanzamos o response:
        $app = new static();
        $app(Request::createFromGlobals())->send();
    }

    //Constructor da aplicación
	public function __construct ()
	{
		//Rexistra a clase para cargar a configuración:
		$this->register('config', function () {
			return new \Fol\Config($this->getPath('config'));
		});

		// instancia clases básicas, rexistra servizos, etc...
	}

	public function __invoke (Request $request) {
		// Devolve un response a partir dun request
	}
}

//E en public/index.php

App\App::run();
```

EXECUCIÓN POR LIÑA DE COMANDOS
==============================

Fol trae un arquivo executable na raíz para lanzar a nosa aplicación dende liña de comandos chamado `fol`, que á súa vez chama a app/console.php. É aí onde podes meter o código para executar os teus comandos personalizados. Recomendo usar symfony/console para crear comandos de xeito sinxelo.

```
$ php fol [command] [args]
```


CONFIGURACIÓN DO SERVIDOR
=========================

Server de php
-------------
Para usar o servidor que trae o propio php, lanza o seguinte comando:

```
$ sudo php -S localhost:80 -t public server.php
```

Agora en http://localhost deberías ver algo.


En Apache
---------
Ainda que funciona sen facer nada, o mellor é establecer como documentRoot o directorio public:

```
<Directory "/var/www/fol/public">
	Order allow,deny
	Allow from all
</Directory>
```

Se queres meter o teu proxecto nun subdirectorio (por exemplo http://localhost/fol) podes poñer o proxecto fora do documentRoot e crear un alias:

```
<IfModule alias_module>
	Alias /nome-proxecto /os-meus-proxectos/fol
</IfModule>

<Directory "/os-meus-proxectos/fol/public">
	Order allow,deny
	Allow from all
</Directory>
```


En Nginx
--------
Tes que editar o arquivo de configuración (nginx/sites-enabled/default):

```
server {
	root /var/www/fol/public;

	charset utf-8;

	location ~* \.php$ {
		fastcgi_split_path_info ^(.+\.php)(/.+)$;
		fastcgi_pass unix:/var/run/php5-fpm.sock;
		fastcgi_index index.php;
		include fastcgi_params;
	}

	# Deny access for hidden
	location ~ /\. {
		deny all;
	}

	# Manage all request
	location / {
		try_files $uri @public;
	}

	# Headers for assets
	location ~* .*\.(css|js|jpg|jpeg|gif|png|ico|cur|gz|svg|svgz|mp4|ogg|ogv|webm|htc|woff|eot|ttf)$ {
		expires 1M;
		access_log off;
		add_header Cache-Control "public";
		try_files $uri @public;
	}

	# This is the public location, called in each request
	location @public {
		if (!-f $request_filename) {
			rewrite ^(.*)$ /index.php last;
		}
	}
}
```
