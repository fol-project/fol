Aqui tes o FOL
==============
(o resto da gaita xa é cousa túa)

[![Build Status](https://travis-ci.org/oscarotero/fol.png?branch=master)](https://travis-ci.org/oscarotero/fol)

FOL é un (micro)framework escrito en PHP por Oscar Otero (http://oscarotero.com) como exercicio de deseño e como ferramenta para experimentar.
Como é algo persoal que non pretende ter moita repercusión (hai miles de frameworks en PHP), escribo a documentación en galego por comodidade e por se alguen máis daquí lle interesa o proxecto. De todos xeitos, os comentarios dentro do código están en inglés.

Características:

* Rápido e lixeiro.
* Escrito en PHP 5.5.
* Pensado para funcionar con composer
* Tamén está preparado para usar bower para instalar os seus componentes
* Compatible con PSR-0/1/2/3/4


Instalación
===========

Para instalalo precisas ter composer. Despois simplemente executa create-project do seguinte xeito:

```
$ composer create-project fol/fol o-meu-proxecto
```

Á hora de instalalo pediráseche configurar estas constantes básicas:

* ENVIRONMENT: O nome do entorno de desenvolvemento. Pode se calquera nome. Por defecto é "development".
* BASE_URL: A url onde está aloxado o sitio web (ruta http do navegador). Serve para xerar as urls. Por defecto é "http://localhost" pero se a instalación se fixo nun subdirectorio ou noutro host, debes modificalo para, por exemplo: http://localhost/o-meu-proxecto
* PUBLIC_DIR: Ruta para acceder ao directorio "public", onde está index.php. Por defecto está vacio, o que significa que a raiz do servidor apunta directamente ao directorio public (o que é preferible).

En calquera momento podes cambiar manualmente esa configuración no arquivo constants.php

Unha vez feito isto, deberías poder ver algo no navegador (http://localhost/o-meu-proxecto).


Documentación rápida
====================

A parte do directorio "vendor" (usado por composer para gardar aí todos os paquetes e dependencias) hai dúas carpetas:

* app: onde se garda a aplicación por defecto (plantillas, controladores, modelos, tests, etc).
* public: todos os arquivos accesibles publicamente (css, imaxes, js, componentes de bower, etc) ademáis do "front controller" (index.php).

Fol usa os estándares psr-0 e psr-4, implementados no loader de Composer, para cargar todas as clases necesarias. O arquivo public/index.php fai de controlador inicial, ou sexa, todas as peticións que non sexan de assets (css, js, imaxes, etc) se redirixen a este arquivo e é o que se encarga de iniciar todo (carga o bootstrap.php, configura os erros, inicializa a aplicación e execútaa).


Errors
------

A clase Errors rexistra os erros que se poidan producir na execución dos scripts e lanza callbacks.

#### Exemplo

```php
Errors::register(); //Inicia o rexistro de erros

//Rexistra unha función que se execute cando hai un erro. (podes rexistrar cantas funcións queiras)
Errors::pushHandler(function ($exception) {
	var_dump($exception);
	die();
});

//Fai que cando haxa erros os imprima (util na fase de desenvolvemento):
Errors::displayErrors();

//Garda os logs propios de php neste arquivo
Errors::setPhpLogFile('log/php.err');
```


App
---

A aplicación está definida na clase App\App (no arquivo app/App.php) que ademáis ten dispoñibles os seguintes métodos:

* $app->getNamespace(): Devolve o namespace da aplicación (ou sexa "App"). Ademáis podes usalo para que che devolva outros namespaces ou clases relativas. Por exemplo ```$app->getNamespace('Controllers\\Index')``` devolve "App\Controllers\Index".
* $app->getPath(): Devolve o path onde está aloxada a aplicación. Podes usar argumentos para qu che devolva rutas de arquivos ou subdirectorios. Por exemplo: ```$app->getPath('arquivos/123', '3.pdf')``` devolve algo parecido a "/var/www/o-meu-proxecto/app/arquivos/123/3.pdf"
* $app->getPublicUrl(): O mesmo que getPath pero para devolver rutas http do directorio público. Útil para acceder a arquivos css, javascript, etc. ```$app->getPublicUrl('assets/css', 'subdirectorio')``` devolvería algo parecido a "http://localhost/o-meu-proxecto/public/assets/css/subdirectorio"

A clase app tamén xestiona os "servizos" usados, ou sexa, clases que podes instanciar en calquera momento e que dependen da túa app. Por exemplo a conexión á base de datos, configuración, xestión de plantillas, etc. Para dar de alta un servizo, tes que usar o método register, co nome do servizo e un callback que devolva o resultado. Exemplo:

```php
// App/App::__construct()

//Rexistra a clase para cargar a configuración:
$this->register('config', function () {
	return new \Fol\Config($this->getPath('config'));
});

//Rexistra a clase para a conexión á base de datos
$this->register('db', function () {
	$config = $this->config->get('db');

	return new \PDO($config['dns'], $config['username'], $config['password']);
});
```

E para usar os servizos:

```php
//Usa o método "get" para xerar unha nova instancia de cada vez:
$newConnection = $this->get('db');

//Get permite tamén pasarlle argumentos ao noso callback
$this->get('db', $arg1, $arg2);

//Tamén podes chamar directamente pola propiedade
$db = $this->db;

//Chamándoa como propiedade non xeras unha nova instancia, senón que usas sempre a mesma
$this->db->exec("DELETE FROM fruit WHERE colour = 'red'");
```

Outra función de "get" é a de instanciar clases relativas á nosa app aínda que non estean rexistradas como servizos. Por exemplo, imaxinemonos que temos a clase App\Controllers\Index. Podemos instanciala directamente:

```php
$indexController = $this->get('Controllers\\Index');
```

Por último, a app debe ter definido o magic method handleRequest, que é o que se utiliza para executar as peticións http (colle un request e devolve un obxecto response).
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
        $request = Request::createFromGlobals();

        $app($request)->send();
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

	public function handleRequest (Request $request) {
		// Devolve un response a partir dun request
	}
}
```

Fol proporciona un mínimo de utilidades para comezar a traballar pero permite instalar moitras bibliotecas extra usando composer. As utilidades básicas son clases que permiten crear un sistema MVC, xestionar "requests" e "responses", manexo de sesións, plantillas de php e carga de arquivos de configuración.

* App: Clase base estendida por todas as apps
* Config: Clase para cargar configuracións dende arquivos php
* Errors: Clase para xestionar erros (silencialos, debuguealos, etc)
* Fol\Http: Conxunto de clases para manexar requests e responses (con headers, variables, cookies, etc).
* Fol\Http\Sessions: Conxunto de clases para manexar sesións.
* Fol\Http\Router: Conxunto de clases para definir rutas e cargar controladores
* Templates: Clase para cargar e renderizar plantillas. Son plantillas puras de php.
* FileSystem: Clase para xestionar arquivos e directorios. Tamén ten funcións para xestionar a subida de arquivos tanto por POST (variable $_FILES) como a carga dende unha url ou pasando directamente contido en base64.
* Terminal: Clase para executar comandos e procesos no servidor.


Algunhas das clases máis importantes:

Fol\Http\Request
----------------

Con esta clase podemos recoller os datos dunha petición http e acceder a eles. O método estático createFromGlobals() crea unha instancia a partir dos datos globais de php:

```php
$request = Fol\Http\Request::createFromGlobals();

//Agora xa podemos acceder a todos os datos desta petición:

$request->query; //Obxecto que contén todos os parámetros enviados na url ($_GET)
$request->query->get('nome'); //Devolve o parámetro 'nome'
$request->query->set('nome', 'novo-valor'); //Modifica o valor do parámetro 'nome'

//Outros obxectos dentro de Request son:

$request->data; //Datos enviados no body da petición ($_POST)
$request->headers; //Para as cabeceiras http
$request->cookies; //Cookies enviadas
$request->files; //Arquivos enviados
$request->route; //Para acceder á ruta dende o controlador
```

Tamén podemos crear requests sen usar as variables globais, util para facer subrequests ou testear a aplicación:

```php
//Creamos unha petición post pasandolle os datos para xerar un novo post
$request = Fol\Http\Request::create('/posts/create', 'POST', ['titulo' => 'Novo post']);
```


Fol\Http\Response
-----------------

Esta clase xenera as respostas que se enviarán ao navegador do usuario.

```php
//Creamos un response
$response = new Fol\Http\Response();

//A clase Response contén dentro outros obxectos para xestionar partes específicas:
$response->headers; //Para enviar cabeceiras
$response->cookies; //Para enviar cookies

//Tamén podemos engadirlle o contido ou body da resposta:
$response->setBody('texto de resposta');

//Se temos o request, podemos pasarllo para que o prepare antes (axusta o mime-type así como outras cabeceiras)
$response->prepare($request);

//E finalmente para enviar a resposta ao servidor, podemos usar a función "send":
$response->send();
```

Fol\Http\Router\Router
----------------------

Xenera as distintas rutas do noso sitio web. Podemos definir esas rutas no contructor da nosa app:

```php
namespace App;

use Fol\Http\Request;
use Fol\Router\Router;
use Fol\Router\RouteFactory;

class App extends \Fol\App {

	public function __construct () {
		//Instanciamos o RouteFactory pasándolle o namespace onde estan os nosos controladores
		$routeFactory = new RouteFactory($this->getNamespace('Controllers'));

		//Instanciamos o Router, pasándolle o routeFactory
		$this->router = new Router($routeFactory);

		//Definimos as distintas rutas (nome da ruta, url, controlador e outras opcions)
		$this->router->map('index', '/', 'Index::index', ['methods' => 'GET']);
		$this->router->map('contacto', '/about', 'Index::about');
	}
}
```

EXECUCIÓN POR LIÑA DE COMANDOS
==============================

Fol trae un arquivo executable na raíz para lanzar a nosa aplicación dende liña de comandos. Eses comandos están definidos en app/Cli.php e por defecto está o comando "run", que permite simular peticións http dende cli:

```
$ php fol run GET /posts/list
```

Facer unha petición GET por liña de comandos pasándolle parámetros:

```
$ php fol run GET "/posts/lists?order=id&page=2"
```
ou tamén:
```
$ php fol run GET /posts/lists --order=id --page=2
```

Facer unha petición POST pasándolle tamén parámetros:

```
$ php fol run POST /posts/create --title="Título do posts"
```


CONFIGURACIÓN DO SERVIDOR
=========================

Server de php
-------------
Para usar o servidor que trae o propio php, lanza o seguinte comando no directorio public:

```
$ php -S localhost:8000 index.php
```

Agora se no navegador vas a http://localhost:8000 deberías ver algo.


En Apache
---------
Unha vez instalado o FOL, xa debería funcionar, non hai que facer nada especial.
Se prefires configurar o sitio web mediante httpd.conf, este sería o exemplo:

```
<Directory "/var/www/public">
	# No indexes
	<IfModule mod_autoindex.c>
		Options -Indexes
	</IfModule>

	# Hidden files
	<Files ~ "^\.">
		Order allow,deny
		Deny from all
	</Files>

	<IfModule mod_rewrite.c>
		Options +FollowSymlinks
		RewriteEngine On

		# Redirect Trailing Slashes...
		RewriteRule ^(.*)/$ /$1 [L,R=301]

		# If the requested filename exists, simply serve it.
		RewriteCond %{REQUEST_FILENAME} -f
		RewriteRule .? - [L]

		# Rewrites the requested to index.php
		RewriteRule ^.*$ index.php [L,QSA]
	</IfModule>
</Directory>
```

En Nginx
--------
Hai que configurar o rewrite, polo que tes que editar o arquivo de configuración (nginx/sites-enabled/default):

```
server {
	root /var/www/public;

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
