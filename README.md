Aqui tes o FOL
==============
(o resto da gaita xa é cousa túa)

[![Build Status](https://travis-ci.org/oscarotero/Fol.png?branch=master)](https://travis-ci.org/oscarotero/Fol)

FOL é un (micro)framework escrito en PHP por Oscar Otero (http://oscarotero.com) como exercicio de deseño e como ferramenta para desenvolver experimentos e proxectos persoais.
Como é algo persoal que non pretende ter moita repercusión (hai miles de frameworks en PHP), escribo a documentación en galego por comodidade e por se alguen máis daquí lle interesa o proxecto. Aínda así, a documentación básica que hai en forma de comentarios no código está en inglés (ou algo parecido).

Características:

* Rápido e lixeiro.
* Escrito en PHP 5.4.
* Pensado para funcionar con composer
* Tamén está preparado para usar bower para instalar assets


Instalación
===========

Para instalalo precisas ter composer: https://getcomposer.org/doc/00-intro.md#installation-nix (recomendo instalalo de xeito global para que estea sempre dispoñible). Despois simplemente executa create-project do seguinte xeito:

```
$ composer create-project fol/fol directorio-destino
```

Á hora de instalalo pediráseche configurar certas constantes básicas:

* ENVIRONMENT: O nome do entorno de desenvolvemento. Pode se calquera nome. Por defecto é "development".
* BASE_URL: A url base sobre a que vai funcionar a web. Serve para xerar urls absolutas. Por defecto é "http://localhost"

En calquera momento podes cambiar esa configuración no arquivo environment.php

Unha vez feito isto, deberías poder ver algo no navegador (http://localhost/o-meu-proxecto).


Documentación rápida
====================

A parte dos directorios "vendor" (usado por composer para gardar aí todos os paquetes e dependencias) e "components" xerada por bower para instalar os seus componentes, hai outras dúas carpetas:

* app: onde se garda a aplicación por defecto (plantillas, assets, controladores, modelos, etc).
* tests: tests unitarios do Fol asi como unha plantilla para testear a tua propia aplicación

O arquivo bootstrap.php na raíz é o que inicia o framework e define as seguintes constantes:

* ACCESS_INTERFACE: Se estamos executando fol por cli, sería "cli" senón "http"
* ENVIRONMENT: O nome do entorno de desenvolvemento actual. Xenérase ao instalar o paquete.
* BASE_PATH: A ruta base onde está aloxado o teu sitio web (ruta interna do servidor).
* BASE_URL: A ruta base onde está aloxado o sitio web (ruta http do navegador). Xenerase ao instalar o paquete


Errors
------
A clase Errors rexistra os erros que se poidan producir na execución dos scripts e lanza callbacks. Deste modo centralízanse todos os erros para poder manexalos mellor.

#### Exemplo

```php
Errors::register(); //Inicia o rexistro de erros

//Rexistra unha función que se execute cando hai un erro. (podes rexistrar cantas funcións queiras)
Errors::pushHandler(function ($exception) {
	var_dump($exception);
	die();
});

//Mostra os erros en pantalla con toda a información útil:
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

Fol usa os estándares psr-0 e psr-4, implementados no loader de Composer, para cargar todas as clases necesarias. A clase App\App é a que se executa por defecto e é a que contén todo o código da túa páxina web. O arquivo index.php é o que se encarga de inicializar todo (carga o bootstrap.php, configura os erros, inicializa a app e execútaa). A app está definida do arquivo app/App.php e podes modificar esa clase para que funcione como queiras. Ademáis tes dispoñibles os seguintes métodos:

* $app->getNamespace(): Devolve o namespace da aplicación (App). Ademáis podes usalo para xerar nomes de clases co mesmo namespace, por exemplo ```$app->getNamespace('Controllers\\Index')``` devolve "App\Controllers\Index".
* $app->getPath(): Devolve o path onde está aloxada a aplicación. Podes engadir paths relativos e incluso divididos varios argumentos, por exemplo: ```$app->getPath('assets/css', 'subdirectorio')``` devolve algo parecido a "/var/www/sitioweb/app/assets/css/subdirectorio"
* $app->getUrl(): O mesmo que getPath pero para devolver rutas http. Ten en conta que son rutas reais, para acceder, por exemplo aos assets. Para usar MVC usa a clase Router. ```$app->getUrl('assets/css', 'subdirectorio')``` devolvería algo parecido a "http://sitioweb.com/app/assets/css/subdirectorio"

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

Coa función "get" podemos obter os servizos rexistrados. Tamén podemos usar o magic method __get() para instancialos e gardalos nunha propiedade para usar nun futuro:

```php
//Accedemos ao servizo chamando directamente polo seu nome (db):
$this->db->exec("DELETE FROM fruit WHERE colour = 'red'");

//Usa "get" para xerar unha nova instancia de cada vez sen gardala:
$db = $this->get('db');

//Get permite tamén pasarlle argumentos ao noso callback
$this->get('db', $arg1, $arg2);
```

Outra función de "get" é a de instanciar clases relativas á nosa app aínda que non estean rexistradas como servizos. Por exemplo, imaxinemonos que temos a clase App\Controllers\Index. Podemos instanciala diretamente:

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

Polo que o sistema sería algo asi:

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

Fol proporciona unha serie de utilidades mínimas para comezar a traballar. Se queres algo máis completo, podes instalalo vía composer. As utilidades básicas son clases que permiten crear un sistema MVC, xestionar "requests" e "responses", manexo de sesións, plantillas de php e carga de arquivos de configuración.

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
//Xeramos un response dende o request. Isto é útil xa que xa lle mete o content-type adecuado, aínda que poderíamos crear un dende cero se o preferimos asi.
$response = $request->generateResponse();

//A clase Response contén dentro outros obxectos para xestionar partes específicas:
$response->headers; //Para enviar cabeceiras
$response->cookies; //Para enviar cookies

//Tamén podemos engadirlle o contido ou body da resposta:
$response->setContent('texto de resposta');

//E finalmente para enviar a resposta ao servidor, podemos usar a función "send":
$response->send();
```

Fol\Router\Router
-----------------

Xenera as distintas rutas do noso sitio web. Podemos definir esas rutas no contructor da nosa app:

```php
namespace Apps\Web;

use Fol\Http\Request;
use Fol\Router\Router;
use Fol\Router\RouteFactory;

class App extends \Fol\App {

	public function __construct () {
		//Instanciamos o RouteFactory pasándolle o namespace onde estan os nosos controladores
		$routeFactory = new RouteFactory($this->getNamespace('Controllers'));

		//Creamos o noso router, pasándolle o routeFactory
		$this->router = new Router($routeFactory);

		//Definimos as distintas rutas (nome da ruta, url, controlador e outras opcions)
		$this->router->map('index', '/', 'Index::index', ['methods' => 'GET']);
		$this->router->map('contacto', '/about', 'Index::about');
	}

	public function __invoke () {
		//Creamos o request collendo os datos globais
		$request = Request::createFromGlobals();

		//Executamos a ruta e devolvemos a resposta
		return $this->router->handle($request, $this);
	}
}
```

Cando se fai unha petición http, o servidor (apache, ngnix, etc) redirixe todo a index.php e dende alí instanciase a nosa app e executase esa petición. A función Request::createFromGlobals() detecta se estamos en "cli" ou en "http" e xenera a petición collendo as variables dende $_GET, $_POST, $_FILES, etc (no caso de http) ou dende a variable $argv (no caso de cli). Iso permitenos executar a nosa web dende liña de comandos para facer tests, executar crons, etc. Para iso debemos executar directamente o arquivo index.php pasándolle o método (GET, POST, PUT, DELETE, etc), a url e outras variables. Se non se especifica método, colle GET por defecto.

Facer unha petición GET por liña de comandos:

```
$ php index.php GET /posts/list
```

Facer unha petición GET por liña de comandos pasándolle parámetros:

```
$ php index.php GET "/posts/lists?order=id&page=2"
```
ou tamén:
```
$ php index.php GET /posts/lists --order id --page 2
```

Facer unha petición POST pasándolle tamén parámetros:

```
$ php index.php POST /posts/create --title "Título do posts"
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
	root /var/www/;

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

	#Redirect all to index.php
	location / {
		rewrite ^(.*)$ /index.php last;
	}

	#This is the mycache location, called when assets are not found
	location @mycache {
		expires 1y;
		access_log on;
		add_header Cache-Control "public";
		rewrite ^/app/assets/(.*)$ /app/assets/cache/index.php last;
	}

	#Assets files
	location ~* /app/assets/.*$ {
		expires 1y;
		access_log off;
		add_header Cache-Control "public";
		try_files $uri $uri/ @mycache;
	}

	#bower components should be served directly
	location ~* /components/.*$ {
		expires 1y;
		access_log off;
		add_header Cache-Control "public";
	}
}
```

TESTS
=====

No directorio test atópanse unha serie de tests unitarios para comprobar que todo funciona correctamente. Para poder executalos, debes ter instalado phpunit (https://github.com/sebastianbergmann/phpunit/) que o podes facer directamente co composer:
```
$ composer global require phpunit/phpunit
```

En phpunit.xml gárdase a configuración básica de phpunit, ou sexa a direccion do arquivo bootstrap.php necesario para iniciar todo e a listaxe de tests para executar. Hai dous testsuites, un propio do Fol para comprobar que todas as súas clases funcionan ben e outra para testear apps.
Existe xa un arquivo inicial para escribir rapidamente os teus tests unitarios en app/BasicTest.php. Simplemente tes que editar a liña onde se rexistra a app no Loader e a función setUpBeforeClass onde se instancia esa app, que se usará en todos os tests. O metodo testApp () sería un test de exemplo, onde creamos un request, pasámosllo á nosa app para que nos devolva un response e comprobamos que o que nos devolve é correcto (unha páxina html co status code 200).
