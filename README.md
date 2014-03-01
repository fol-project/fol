Aqui tes o FOL
==============
(o resto da gaita xa é cousa túa)

[![Build Status](https://travis-ci.org/oscarotero/Fol.png?branch=master)](https://travis-ci.org/oscarotero/Fol)

FOL é un (micro)framework escrito en PHP por Oscar Otero (http://oscarotero.com) como exercicio de deseño e como ferramenta para experimental.
Como é algo persoal que non pretende ter moita repercusión (hai miles de frameworks en PHP), escribo a documentación en galego por comodidade e por se alguen máis daquí lle interesa o proxecto. De todos xeitos, os comentarios dentro do código están en inglés.

Características:

* Rápido e lixeiro.
* Escrito en PHP 5.4.
* Pensado para funcionar con composer
* Tamén está preparado para usar bower para instalar os seus componentes


Instalación
===========

Para instalalo precisas ter composer: https://getcomposer.org/doc/00-intro.md#installation-nix (recomendo instalalo de xeito global para que estea sempre dispoñible). Despois simplemente executa create-project do seguinte xeito:

```
$ composer create-project fol/fol o-meu-proxecto
```

Á hora de instalalo pediráseche configurar estas constantes básicas:

* ENVIRONMENT: O nome do entorno de desenvolvemento. Pode se calquera nome. Por defecto é "development".
* BASE_URL: A url onde está aloxado o sitio web (ruta http do navegador). Serve para xerar as urls. Por defecto é "http://localhost" pero se a instalación se fixo nun subdirectorio, debes modificalo para, por exemplo: http://localhost/o-meu-proxecto

En calquera momento podes cambiar manualmente esa configuración no arquivo environment.php

Unha vez feito isto, deberías poder ver algo no navegador (http://localhost/o-meu-proxecto).


Opcións de instalación
======================

Cando se executa o comando create-project de composer, fol lanza un script, que ademáis de pedir as constantes básicas (ENVIRONMENT e BASE_URL) tamén executar máis operacións segundo a túa configuración na propiedade "extra" do arquivo composer.json:

* config: Array cunha listaxe de arquivos de configuración que se poden modificar na instalación (por exemplo bases de datos, etc). O script xenerará novos arquivos que se gardarán nun subdirectorio de config co mesmo nome que a constante ENVIRONMENT.
* writable: Array cunha listaxe de directorios que precisan ter permisos de escritura (daránselle permisos 0777). Se o directorio non existe, crearase un novo. Útil para xerar directorios para gardar os logs, caches, etc.


Documentación rápida
====================

A parte do directorio "vendor" (usado por composer para gardar aí todos os paquetes e dependencias) hai dúas carpetas:

* app: onde se garda a aplicación por defecto (plantillas, controladores, modelos, tests, etc).
* public: todos os arquivos accesibles publicamente (css, imaxes, js, componentes de bower, etc) ademáis do "front controller" (index.php).

O arquivo bootstrap.php define as seguintes constantes:

* ACCESS_INTERFACE: Se estamos executando fol por comandos, sería "cli" senón "http"
* BASE_PATH: A ruta base onde está aloxado o teu sitio web (ruta interna do servidor).
* BASE_URL: O valor que puxeches na instalación
* ENVIRONMENT: O valor que puxeches na instalación.


Errors
------
A clase Errors rexistra os erros que se poidan producir na execución dos scripts e lanza callbacks. Ademáis convirte todos os erros de php en exceptions, deste modo centralízanse todos os erros para poder manexalos mellor.

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
```

Tamén se pode rexistrar unha clase logger para gardar os erros en logs (Recomendo esta: https://github.com/Seldaek/monolog). Para iso debes definir a clase instanciada que se ocupe de xestionar os logs. O único requerimento é que a clase debe implementar a interface Psr\Log\LoggerInterface (https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md)

Exemplo usando monolog:

```php
use Fol\Errors;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

//Iniciamos o rexistro de erros
Errors::register();

//Instanciamos o Logger de monolog
$log = new Logger('name');

//Asignamos o handler
$log->pushHandler(new StreamHandler(BASE_PATH.'/logs/debug.log', Logger::DEBUG));

//Asignamos agora o logger, para que lle pase os erros que vaia vendo
Errors::setLogger($log);
```

App
---

Fol usa os estándares psr-0 e psr-4, implementados no loader de Composer, para cargar todas as clases necesarias. O arquivo public/index.php fai de controlador inicial, ou sexa, todas as peticións que non sexan de assets (css, js, imaxes, etc) se redirixen a este arquivo e é o que se encarga de iniciar todo (carga o bootstrap.php, configura os erros, inicializa a nosa aplicación e execútaa).

A aplicación está definida na clase App\App e contén todo o código da túa páxina web. Está definida do arquivo app/App.php e podes modificar esa clase para que funcione como queiras. Ademáis tes dispoñibles os seguintes métodos:

* $app->getNamespace(): Devolve o namespace da aplicación (ou sexa "App"). Ademáis podes usalo para xerar subnamespaces ou subclases no mesmo namespace, por exemplo ```$app->getNamespace('Controllers\\Index')``` devolve "App\Controllers\Index".
* $app->getPath(): Devolve o path onde está aloxada a aplicación. Podes engadir paths relativos e incluso dividilos varios argumentos, por exemplo: ```$app->getPath('assets/css', 'subdirectorio')``` devolve algo parecido a "/var/www/sitioweb/app/assets/css/subdirectorio"
* $app->getPublicUrl(): O mesmo que getPath pero para devolver rutas http do directorio público. Ten en conta que son rutas reais, para acceder, por exemplo aos assets. Para usar MVC usa a clase Router. ```$app->getUrl('assets/css', 'subdirectorio')``` devolvería algo parecido a "http://localhost/o-meu-proxecto/public/assets/css/subdirectorio"

A clase app tamén serve para xestionar "servizos", ou sexa, clases que podes instanciar en calquera momento e que dependen da túa app. Por exemplo a conexión á base de datos, configuración, xestión de plantillas, etc. Para dar de alta un servizo, tes que usar o método register, co nome do servizo e un callback que devolva o resultado. Exemplo:

```php
// app/App.php __construct()

//Clase para cargar a configuración:
$this->register('config', function () {
	return new \Fol\Config($this->getPath('config'));
});

//Clase para a conexión á base de datos
$this->register('db', function () {
	$config = $this->config->get('db');

	return new \PDO($config['dns'], $config['username'], $config['password']);
});
```

O método "get" executa o callback de cada servizo rexistrado e devolvenos o resultado. Se só queres ter unha instancia de cada servizo (por exemplo, unha conexión á base de datos), en vez de chamar por get, chama pola propiedade do mesmo nome. Deste xeito, a primeira vez que a chames, executará o magic method __get() que gardará o resultado e que cando a volvas chamar nun futuro, xa non se executa máis:

```php
//Usa "get" para xerar unha nova instancia de cada vez:
$newConnection = $this->get('db');

//Get permite tamén pasarlle argumentos ao noso callback
$this->get('db', $arg1, $arg2);

//Chama directamente pola propiedade
$db = $this->db;

//Asi, se a volves a chamar, devolveche o mesmo obxecto:
$this->db->exec("DELETE FROM fruit WHERE colour = 'red'");
```

Outra función de "get" é a de instanciar clases relativas á nosa app aínda que non estean rexistradas como servizos. Por exemplo, imaxinemonos que temos a clase App\Controllers\Index. Podemos instanciala directamente:

```php
$indexController = $this->get('Controllers\\Index');
```

Por último, a app debe ter definido o magic method __invoke, que é o que se utiliza para executalo (colle un request e devolve un obxecto response).

Resumindo, a estrutura dunha app sería algo asi:

```php
namespace App;

class App extends \Fol\App {

	public function __construct () {
		// Contructor da applicación
		// carga a configuración, instancia clases básicas, rexistra servizos, etc...
	}

	public function __invoke ($request = null) {
		// Devolve un response a partir dun request
	}
}
```

E executado sería algo asi:

```php
use Fol\Http\Request;

//Instanciamos a nosa aplicación:
$app = new App\App;

//Agora executamola
$response = $app();

//Podemos executala de novo con outro request diferente:
$request = Request::create('http://sitioweb.com/posts/23');
$response = $app($request);

//Enviamos a resposta ao navegador:
$response->send();
```

Fol non é un framework con moitas funcionalidades senón que proporciona o mínimo para comezar a traballar. O resto de cousas que precises terás que buscalas e instalalas vía Composer. As utilidades básicas son clases que permiten crear un sistema MVC, xestionar "requests" e "responses", manexo de sesións, plantillas de php e carga de arquivos de configuración.

* Http: Conxunto de clases para manexar requests e responses (con headers, variables, cookies, etc).
* Router: Conxunto de clases para definir rutas asociadas a controladores
* App: Clase base estendida por todas as apps
* Config: Clase para cargar configuracións dende arquivos php
* Errors: Clase para xestionar erros (silencialos, debuguealos, etc)
* Session: Clase para manexar a sesión (inicializar, gardar datos, destruír, etc)
* Templates: Clase para cargar e renderizar plantillas. Son plantillas puras de php.
* FileSystem: Clase para xestionar arquivos e directorios. Tamén ten funcións para xestionar a subida de arquivos tanto por POST (variable $_FILES) como a carga dende unha url ou pasando directamente contido en base64.

Algunhas das clases máis importantes:

Fol\Http\Request
----------------

Con esta clase podemos recoller os datos dunha petición http e acceder a eles. Para crear o obxecto Request, podemos usar a función estática createFromGlobals():

```php
$request = Fol\Http\Request::createFromGlobals();

//Agora xa podemos acceder a todos os datos desta petición:

$request->get; //Obxecto que contén todos os parámetros enviados por GET
$request->get->get('nome'); //Devolve o parámetro enviado por GET 'nome'
$request->get->set('nome', 'novo-valor'); //Modifica o valor do parámetro 'nome'

//Outros obxectos dentro de Request son:

$request->post; //Para os parámetros POST
$request->server; //Para as variables do servidor (o equivalente a $_SERVER)
$request->headers; //Para as cabeceiras http
$request->cookies; //Cookies enviadas
$request->files; //Arquivos enviados
$request->parameters; //Para gardar parámetros manualmente
```

Tamén podemos crear requests sen usar as variables globais, util para facer subrequests ou testear a aplicación:

```php
//Creamos unha petición post pasandolle os datos para xerar un novo post
$request = Fol\Http\Request::create('/posts/create', 'POST', ['titulo' => 'Novo post']);

//Executamos esta petición na nosa app e obtemos a resposta:
$response = $app($request);
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
$response->setContent('texto de resposta');

//Se temos o request, podemos pasarllo para que o prepare antes (axusta o mime-type así como outras cabeceiras)
$response->prepare($request);

//E finalmente para enviar a resposta ao servidor, podemos usar a función "send":
$response->send();
```

Fol\Router\Router
-----------------

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

Fol trae un arquivo executable na raíz para lanzar a nosa aplicación dende liña de comandos. Exemplos:

```
$ fol GET /posts/list
```

Facer unha petición GET por liña de comandos pasándolle parámetros:

```
$ fol GET "/posts/lists?order=id&page=2"
```
ou tamén:
```
$ fol GET /posts/lists --order id --page 2
```

Facer unha petición POST pasándolle tamén parámetros:

```
$ fol POST /posts/create --title "Título do posts"
```


CONFIGURACIÓN DO SERVIDOR
=========================

En Apache
---------
Unha vez instalado o FOL, xa debería funcionar, non hai que facer nada especial.

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

	location ~ /\. {
		deny all;
	}

	# Manage all request
	location / {
		try_files $uri @public;
	}

	# Headers for specific assets
	location ~* .*\.(jpg|jpeg|gif|png|ico|cur|gz|svg|svgz|mp4|ogg|ogv|webm|htc)$ {
		expires 1M;
		access_log off;
		add_header Cache-Control "public";
		try_files $uri @public;
	}

	# Headers for CSS and Javascript
	location ~* .*\.(css|js)$ {
		expires 1y;
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
